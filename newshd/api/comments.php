<?php
require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $articleId = (int) ($_GET['article_id'] ?? 0);
    if (!$articleId) {
        json_response(['success' => false, 'message' => 'Article ID required'], 400);
    }

    $stmt = db()->prepare(
        "SELECT c.*, u.name AS user_name, u.avatar AS user_avatar
         FROM comments c INNER JOIN users u ON u.id = c.user_id
         WHERE c.article_id = ? AND c.status = 'approved' AND c.parent_id IS NULL
         ORDER BY c.created_at DESC"
    );
    $stmt->execute([$articleId]);
    $comments = $stmt->fetchAll();

    foreach ($comments as &$comment) {
        $comment['can_edit'] = can_edit_comment($comment);
        $replyStmt = db()->prepare(
            "SELECT c.*, u.name AS user_name FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.parent_id = ? AND c.status = 'approved' ORDER BY c.created_at ASC"
        );
        $replyStmt->execute([$comment['id']]);
        $comment['replies'] = $replyStmt->fetchAll();
        foreach ($comment['replies'] as &$reply) {
            $reply['can_edit'] = can_edit_comment($reply);
        }
    }

    json_response(['success' => true, 'comments' => $comments]);
}

if ($method === 'POST') {
    require_csrf();
    if (!is_logged_in()) {
        json_response(['success' => false, 'message' => 'Login required'], 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? 'create';

    if ($action === 'create') {
        $articleId = (int) ($input['article_id'] ?? 0);
        $body = sanitize($input['body'] ?? '');
        $parentId = !empty($input['parent_id']) ? (int) $input['parent_id'] : null;

        if (!$articleId || strlen($body) < 2) {
            json_response(['success' => false, 'message' => 'Invalid comment'], 400);
        }

        check_comment_rate_limit((int) current_user()['id']);

        $stmt = db()->prepare(
            'INSERT INTO comments (article_id, user_id, parent_id, body, status) VALUES (?, ?, ?, ?, ?)'
        );
        $status = is_staff() ? 'approved' : 'pending';
        $stmt->execute([$articleId, current_user()['id'], $parentId, $body, $status]);
        log_activity((int) current_user()['id'], 'comment_create', 'comment', (int) db()->lastInsertId());

        json_response([
            'success' => true,
            'message' => $status === 'pending' ? 'Comment submitted for moderation.' : 'Comment posted.',
            'comment_id' => (int) db()->lastInsertId(),
        ]);
    }

    if ($action === 'edit') {
        $commentId = (int) ($input['comment_id'] ?? 0);
        $body = sanitize($input['body'] ?? '');
        $comment = get_user_comment($commentId);
        if (!$comment || !can_edit_comment($comment)) {
            json_response(['success' => false, 'message' => 'Cannot edit this comment'], 403);
        }
        db()->prepare('UPDATE comments SET body = ?, status = ? WHERE id = ?')
           ->execute([$body, is_staff() ? 'approved' : 'pending', $commentId]);
        json_response(['success' => true, 'message' => 'Comment updated.']);
    }

    if ($action === 'delete') {
        $commentId = (int) ($input['comment_id'] ?? 0);
        $comment = get_user_comment($commentId);
        if (!$comment || (int) $comment['user_id'] !== (int) current_user()['id']) {
            json_response(['success' => false, 'message' => 'Cannot delete this comment'], 403);
        }
        db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$commentId]);
        json_response(['success' => true, 'message' => 'Comment deleted.']);
    }

    if ($action === 'vote') {
        $commentId = (int) ($input['comment_id'] ?? 0);
        $vote = (int) ($input['vote'] ?? 0);
        if (!in_array($vote, [-1, 1], true)) {
            json_response(['success' => false, 'message' => 'Invalid vote'], 400);
        }
        $userId = (int) current_user()['id'];
        $existing = db()->prepare('SELECT id, vote FROM comment_votes WHERE comment_id = ? AND user_id = ?');
        $existing->execute([$commentId, $userId]);
        $row = $existing->fetch();
        if ($row) {
            if ((int) $row['vote'] === $vote) {
                db()->prepare('DELETE FROM comment_votes WHERE id = ?')->execute([$row['id']]);
                adjust_comment_votes($commentId, $vote, -1);
            } else {
                db()->prepare('UPDATE comment_votes SET vote = ? WHERE id = ?')->execute([$vote, $row['id']]);
                adjust_comment_votes($commentId, (int) $row['vote'], -1);
                adjust_comment_votes($commentId, $vote, 1);
            }
        } else {
            db()->prepare('INSERT INTO comment_votes (comment_id, user_id, vote) VALUES (?, ?, ?)')
               ->execute([$commentId, $userId, $vote]);
            adjust_comment_votes($commentId, $vote, 1);
        }
        json_response(['success' => true]);
    }

    json_response(['success' => false, 'message' => 'Unknown action'], 400);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

function get_user_comment(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM comments WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function can_edit_comment(array $comment): bool
{
    $user = current_user();
    if (!$user || (int) $comment['user_id'] !== (int) $user['id']) {
        return false;
    }
    $created = strtotime($comment['created_at']);
    return (time() - $created) <= COMMENT_EDIT_WINDOW;
}

function check_comment_rate_limit(int $userId): void
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM comments WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)'
    );
    $stmt->execute([$userId]);
    if ((int) $stmt->fetchColumn() >= 5) {
        json_response(['success' => false, 'message' => 'Too many comments. Please wait.'], 429);
    }
}

function adjust_comment_votes(int $commentId, int $vote, int $delta): void
{
    $col = $vote === 1 ? 'likes' : 'dislikes';
    $op = $delta > 0 ? '+' : '-';
    db()->exec("UPDATE comments SET $col = GREATEST(0, $col $op 1) WHERE id = $commentId");
}
