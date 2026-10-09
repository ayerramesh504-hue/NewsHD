<?php
require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

require_csrf();
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) {
    json_response(['success' => false, 'message' => 'Invalid email'], 400);
}

try {
    $stmt = db()->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)');
    $stmt->execute([$email]);
    json_response(['success' => true, 'message' => 'Subscribed successfully!']);
} catch (PDOException) {
    json_response(['success' => false, 'message' => 'Subscription failed'], 500);
}
