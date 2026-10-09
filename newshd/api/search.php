<?php
require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: application/json');

$q = sanitize($_GET['q'] ?? '');
if (strlen($q) < 2) {
    json_response(['success' => true, 'results' => []]);
}

$like = '%' . $q . '%';
$stmt = db()->prepare(
    "SELECT DISTINCT a.id, a.title, a.slug, a.excerpt, c.name AS category_name
     FROM articles a
     INNER JOIN categories c ON c.id = a.category_id
     LEFT JOIN users u ON u.id = a.author_id
     LEFT JOIN article_tags at ON at.article_id = a.id
     LEFT JOIN tags t ON t.id = at.tag_id
     WHERE a.status = 'published' AND (
       a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?
       OR c.name LIKE ? OR t.name LIKE ?
     )
     ORDER BY a.published_at DESC LIMIT 8"
);
$stmt->execute([$like, $like, $like, $like, $like]);
$results = $stmt->fetchAll();

foreach ($results as &$r) {
    $r['url'] = url('article.php?slug=' . $r['slug']);
    $r['excerpt'] = truncate(strip_tags($r['excerpt'] ?? ''), 80);
}

json_response(['success' => true, 'results' => $results]);
