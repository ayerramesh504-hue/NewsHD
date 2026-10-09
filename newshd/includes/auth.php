<?php
/**
 * Authentication & session management
 */

declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return restore_remember_session();
    }
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.is_banned = 0'
        );
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user) {
            logout_user();
        }
    }
    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user && in_array($user['role_slug'], ['admin', 'editor', 'author'], true);
}

function is_staff(): bool
{
    $user = current_user();
    return $user && in_array($user['role_slug'], ['admin', 'editor'], true);
}

function login_user(array $user, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

    $stmt = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $stmt = db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
        $stmt->execute([hash('sha256', $token), $user['id']]);
        setcookie('remember_token', $token, [
            'expires'  => time() + REMEMBER_ME_DAYS * 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    log_activity((int) $user['id'], 'login');
}

function logout_user(): void
{
    if (!empty($_SESSION['user_id'])) {
        log_activity((int) $_SESSION['user_id'], 'logout');
        db()->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')
           ->execute([$_SESSION['user_id']]);
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    setcookie('remember_token', '', time() - 3600, '/');
    session_destroy();
}

function restore_remember_session(): ?array
{
    $token = $_COOKIE['remember_token'] ?? null;
    if (!$token) {
        return null;
    }
    $hash = hash('sha256', $token);
    $stmt = db()->prepare(
        'SELECT u.*, r.slug AS role_slug, r.name AS role_name
         FROM users u INNER JOIN roles r ON r.id = u.role_id
         WHERE u.remember_token = ? AND u.is_banned = 0'
    );
    $stmt->execute([$hash]);
    $user = $stmt->fetch();
    if ($user) {
        login_user($user, false);
        return $user;
    }
    return null;
}

function check_login_rate_limit(string $email): bool
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE email = ? AND ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
    );
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '', LOGIN_LOCKOUT_MINUTES]);
    return (int) $stmt->fetchColumn() < LOGIN_MAX_ATTEMPTS;
}

function record_login_attempt(string $email): void
{
    $stmt = db()->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)');
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);
}

function clear_login_attempts(string $email): void
{
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);
}

function register_user(string $name, string $email, string $password): array
{
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    $token = bin2hex(random_bytes(32));
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = db()->prepare(
        'INSERT INTO users (role_id, name, email, password_hash, verification_token)
         VALUES (4, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $hash, $token]);

    $userId = (int) db()->lastInsertId();
    log_activity($userId, 'register');

    return [
        'success' => true,
        'user_id' => $userId,
        'verification_token' => $token,
        'message' => 'Registration successful. Please verify your email.',
    ];
}

function verify_email_token(string $token): bool
{
    $stmt = db()->prepare(
        'UPDATE users SET email_verified_at = NOW(), verification_token = NULL
         WHERE verification_token = ? AND email_verified_at IS NULL'
    );
    $stmt->execute([$token]);
    return $stmt->rowCount() > 0;
}

function create_password_reset_token(string $email): ?string
{
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND is_banned = 0');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        return null;
    }
    $token = bin2hex(random_bytes(32));
    $stmt = db()->prepare(
        'UPDATE users SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?'
    );
    $stmt->execute([$token, $user['id']]);
    return $token;
}

function reset_password_with_token(string $token, string $password): bool
{
    $stmt = db()->prepare(
        'SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user) {
        return false;
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare(
        'UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?'
    );
    $stmt->execute([$hash, $user['id']]);
    return true;
}

function authenticate(string $email, string $password): array
{
    if (!check_login_rate_limit($email)) {
        return ['success' => false, 'message' => 'Too many login attempts. Try again later.'];
    }

    $stmt = db()->prepare(
        'SELECT u.*, r.slug AS role_slug, r.name AS role_name
         FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.email = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_login_attempt($email);
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    if ($user['is_banned']) {
        return ['success' => false, 'message' => 'Your account has been suspended.'];
    }

    clear_login_attempts($email);
    return ['success' => true, 'user' => $user];
}
