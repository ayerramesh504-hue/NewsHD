<?php
/**
 * Global helper functions
 */

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function api_url(string $path): string
{
    return url('api/' . ltrim($path, '/'));
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_setting(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        try {
            $stmt = db()->query('SELECT setting_key, setting_value FROM site_settings');
            $cache = [];
            while ($row = $stmt->fetch()) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException) {
            return $default;
        }
    }
    return $cache[$key] ?? $default;
}

function log_activity(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (PDOException) {
        // Non-critical
    }
}

function format_date(?string $datetime, string $format = 'M j, Y'): string
{
    if (!$datetime) {
        return '';
    }
    return date($format, strtotime($datetime));
}

function truncate(string $text, int $length = 150): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function upload_image(array $file, string $subdir): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return null;
    }
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => null,
    };
    if (!$ext) {
        return null;
    }
    $dir = UPLOAD_PATH . '/' . $subdir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $filename = uniqid('', true) . '.' . $ext;
    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    return 'uploads/' . $subdir . '/' . $filename;
}

function get_categories(): array
{
    $stmt = db()->query('SELECT id, name, slug FROM categories ORDER BY sort_order, name');
    return $stmt->fetchAll();
}

function increment_article_views(int $articleId): void
{
    $stmt = db()->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id = ?');
    $stmt->execute([$articleId]);
}

function get_article_tags(int $articleId): array
{
    $stmt = db()->prepare(
        'SELECT t.id, t.name, t.slug FROM tags t
         INNER JOIN article_tags at ON at.tag_id = t.id
         WHERE at.article_id = ?'
    );
    $stmt->execute([$articleId]);
    return $stmt->fetchAll();
}

function sync_article_tags(int $articleId, array $tagNames): void
{
    db()->prepare('DELETE FROM article_tags WHERE article_id = ?')->execute([$articleId]);
    foreach ($tagNames as $name) {
        $name = trim($name);
        if ($name === '') continue;
        $slug = slugify($name);
        $stmt = db()->prepare('SELECT id FROM tags WHERE slug = ?');
        $stmt->execute([$slug]);
        $tag = $stmt->fetch();
        if ($tag) {
            $tagId = (int) $tag['id'];
        } else {
            db()->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)')->execute([$name, $slug]);
            $tagId = (int) db()->lastInsertId();
        }
        db()->prepare('INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)')
           ->execute([$articleId, $tagId]);
    }
}

function validate_password_strength(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain an uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain a lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain a number.';
    }
    return null;
}

function page_meta(string $title, ?string $description = null, ?string $image = null, ?string $url = null): array
{
    return [
        'title'       => $title . ' | ' . (get_setting('site_name') ?: APP_NAME),
        'description' => $description ?: (get_setting('site_description') ?: ''),
        'image'       => $image ?: asset('images/news-logo.jpg'),
        'url'         => $url ?: APP_URL,
    ];
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? url('');
        redirect(url('login.php'));
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect(url('admin/login.php'));
    }
}

function require_role(array $roles): void
{
    $user = current_user();
    if (!$user || !in_array($user['role_slug'], $roles, true)) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
}
