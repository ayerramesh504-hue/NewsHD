<?php
require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(['success' => false, 'message' => 'Login required'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

require_csrf();
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$articleId = (int) ($input['article_id'] ?? 0);
$userId = (int) current_user()['id'];

$check = db()->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?');
$check->execute([$userId, $articleId]);
$existing = $check->fetch();

if ($existing) {
    db()->prepare('DELETE FROM bookmarks WHERE id = ?')->execute([$existing['id']]);
    json_response(['success' => true, 'bookmarked' => false]);
}

db()->prepare('INSERT INTO bookmarks (user_id, article_id) VALUES (?, ?)')->execute([$userId, $articleId]);
json_response(['success' => true, 'bookmarked' => true]);
