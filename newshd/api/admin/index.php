<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once APP_ROOT . '/includes/pagination.php';

header('Content-Type: application/json');

if (!is_admin()) {
    json_response(['success' => false, 'message' => 'Unauthorized'], 403);
}

$resource = sanitize($_GET['resource'] ?? 'dashboard');
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

switch ($resource) {
    case 'dashboard':
        $stats = [
            'articles' => (int) db()->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
            'users' => (int) db()->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'comments' => (int) db()->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
            'pending_comments' => (int) db()->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn(),
            'page_views' => (int) db()->query("SELECT COALESCE(SUM(view_count),0) FROM articles")->fetchColumn(),
            'messages' => (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE status='new'")->fetchColumn(),
        ];
        $chartStmt = db()->query(
            "SELECT DATE(created_at) AS day, COUNT(*) AS count FROM activity_logs
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY day"
        );
        $stats['chart'] = $chartStmt->fetchAll();
        json_response(['success' => true, 'stats' => $stats]);

    case 'articles':
        if ($method === 'GET') {
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $search = sanitize($_GET['q'] ?? '');
            $where = '1=1';
            $params = [];
            if ($search) {
                $where .= ' AND a.title LIKE ?';
                $params[] = '%' . $search . '%';
            }
            $count = db()->prepare("SELECT COUNT(*) FROM articles a WHERE $where");
            $count->execute($params);
            $total = (int) $count->fetchColumn();
            $p = paginate($total, $page, ADMIN_ITEMS_PER_PAGE, '');
            $sql = "SELECT a.*, c.name AS category_name, u.name AS author_name,
                    (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM article_tags at
                     INNER JOIN tags t ON t.id = at.tag_id WHERE at.article_id = a.id) AS tag_names
                    FROM articles a
                    INNER JOIN categories c ON c.id = a.category_id
                    INNER JOIN users u ON u.id = a.author_id
                    WHERE $where ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
            $stmt = db()->prepare($sql);
            $i = 1;
            foreach ($params as $param) {
                $stmt->bindValue($i++, $param);
            }
            $stmt->bindValue($i++, ADMIN_ITEMS_PER_PAGE, PDO::PARAM_INT);
            $stmt->bindValue($i, $p['offset'], PDO::PARAM_INT);
            $stmt->execute();
            json_response(['success' => true, 'data' => $stmt->fetchAll(), 'pagination' => pagination_json($p)]);
        }
        require_csrf();
        if ($method === 'POST') {
            $id = (int) ($input['id'] ?? 0);
            $title = sanitize($input['title'] ?? '');
            $slug = slugify($input['slug'] ?? $title);
            $content = $input['content'] ?? '';
            $excerpt = sanitize($input['excerpt'] ?? '');
            $categoryId = (int) ($input['category_id'] ?? 0);
            $status = in_array($input['status'] ?? '', ['draft','published','scheduled'], true) ? $input['status'] : 'draft';
            $isFeatured = !empty($input['is_featured']) ? 1 : 0;
            $isBreaking = !empty($input['is_breaking']) ? 1 : 0;
            $tags = array_filter(array_map('trim', explode(',', $input['tags'] ?? '')));
            $coverImage = sanitize($input['cover_image'] ?? '');
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

            if (!$title || !$categoryId) {
                json_response(['success' => false, 'message' => 'Title and category required'], 400);
            }

            $user = current_user();
            if ($id) {
                $stmt = db()->prepare(
                    'UPDATE articles SET title=?, slug=?, excerpt=?, content=?, category_id=?, status=?,
                     is_featured=?, is_breaking=?, cover_image=?, published_at=COALESCE(published_at, ?),
                     meta_title=?, meta_description=? WHERE id=?'
                );
                $stmt->execute([$title, $slug, $excerpt, $content, $categoryId, $status, $isFeatured, $isBreaking,
                    $coverImage ?: null, $publishedAt, $title, $excerpt, $id]);
                sync_article_tags($id, $tags);
                log_activity((int)$user['id'], 'article_update', 'article', $id);
                json_response(['success' => true, 'id' => $id]);
            }

            $stmt = db()->prepare(
                'INSERT INTO articles (author_id, category_id, title, slug, excerpt, content, cover_image, status,
                 is_featured, is_breaking, published_at, meta_title, meta_description)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([(int)$user['id'], $categoryId, $title, $slug, $excerpt, $content,
                $coverImage ?: null, $status, $isFeatured, $isBreaking, $publishedAt, $title, $excerpt]);
            $newId = (int) db()->lastInsertId();
            sync_article_tags($newId, $tags);
            log_activity((int)$user['id'], 'article_create', 'article', $newId);
            json_response(['success' => true, 'id' => $newId]);
        }
        if ($method === 'DELETE') {
            require_csrf();
            $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);
            db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
            log_activity((int)current_user()['id'], 'article_delete', 'article', $id);
            json_response(['success' => true]);
        }
        break;

    case 'categories':
        if ($method === 'GET') {
            json_response(['success' => true, 'data' => get_categories()]);
        }
        require_csrf();
        if ($method === 'POST') {
            $id = (int) ($input['id'] ?? 0);
            $name = sanitize($input['name'] ?? '');
            $slug = slugify($input['slug'] ?? $name);
            $description = sanitize($input['description'] ?? '');
            if (!$name) json_response(['success' => false, 'message' => 'Name required'], 400);
            if ($id) {
                db()->prepare('UPDATE categories SET name=?, slug=?, description=? WHERE id=?')
                   ->execute([$name, $slug, $description, $id]);
            } else {
                db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)')
                   ->execute([$name, $slug, $description]);
            }
            json_response(['success' => true]);
        }
        if ($method === 'DELETE') {
            require_csrf();
            db()->prepare('DELETE FROM categories WHERE id = ?')->execute([(int)($input['id'] ?? 0)]);
            json_response(['success' => true]);
        }
        break;

    case 'users':
        require_role(['admin', 'editor']);
        if ($method === 'GET') {
            $stmt = db()->query(
                'SELECT u.id, u.name, u.email, u.is_banned, u.created_at, r.name AS role_name, r.slug AS role_slug
                 FROM users u INNER JOIN roles r ON r.id = u.role_id ORDER BY u.created_at DESC'
            );
            json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        require_csrf();
        if ($method === 'POST') {
            require_role(['admin']);
            $id = (int) ($input['id'] ?? 0);
            $roleId = (int) ($input['role_id'] ?? 4);
            $banned = !empty($input['is_banned']) ? 1 : 0;
            db()->prepare('UPDATE users SET role_id=?, is_banned=? WHERE id=?')->execute([$roleId, $banned, $id]);
            json_response(['success' => true]);
        }
        break;

    case 'comments':
        if ($method === 'GET') {
            $status = sanitize($_GET['status'] ?? 'pending');
            $stmt = db()->prepare(
                "SELECT c.*, u.name AS user_name, a.title AS article_title FROM comments c
                 INNER JOIN users u ON u.id = c.user_id
                 INNER JOIN articles a ON a.id = c.article_id
                 WHERE c.status = ? ORDER BY c.created_at DESC LIMIT 50"
            );
            $stmt->execute([$status]);
            json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        require_csrf();
        $id = (int) ($input['id'] ?? 0);
        $action = $input['action'] ?? 'approve';
        $newStatus = match ($action) {
            'approve' => 'approved',
            'reject' => 'rejected',
            'spam' => 'spam',
            default => 'approved',
        };
        if ($method === 'DELETE') {
            db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$id]);
        } else {
            db()->prepare('UPDATE comments SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
        }
        json_response(['success' => true]);

    case 'messages':
        if ($method === 'GET') {
            $stmt = db()->query(
                'SELECT m.*, u.name AS user_name, u.email AS user_email FROM contact_messages m
                 INNER JOIN users u ON u.id = m.user_id ORDER BY m.created_at DESC'
            );
            json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        require_csrf();
        $id = (int) ($input['id'] ?? 0);
        $status = sanitize($input['status'] ?? 'read');
        $reply = sanitize($input['admin_reply'] ?? '');
        db()->prepare('UPDATE contact_messages SET status=?, admin_reply=? WHERE id=?')
           ->execute([$status, $reply ?: null, $id]);
        json_response(['success' => true]);

    case 'settings':
        require_role(['admin']);
        if ($method === 'GET') {
            $stmt = db()->query('SELECT setting_key, setting_value FROM site_settings');
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            json_response(['success' => true, 'data' => $settings]);
        }
        require_csrf();
        foreach ($input as $key => $value) {
            if (!is_string($key)) continue;
            db()->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            )->execute([sanitize($key), sanitize((string)$value)]);
        }
        json_response(['success' => true]);

    case 'roles':
        json_response(['success' => true, 'data' => db()->query('SELECT id, name, slug FROM roles')->fetchAll()]);
}

json_response(['success' => false, 'message' => 'Unknown resource'], 404);
