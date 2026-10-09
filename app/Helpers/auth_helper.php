<?php

use App\Models\LoginAttemptModel;
use App\Models\UserModel;

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        $session = session();

        if (empty($session->get('user_id'))) {
            return restore_remember_session();
        }
        $user = model(UserModel::class)->withRole((int) $session->get('user_id'));
        if (! $user) {
            logout_user();
        }
        return $user ?: null;
    }
}

if (! function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return current_user() !== null;
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = current_user();
        return $user && $user['role_slug'] === 'admin';
    }
}

if (! function_exists('is_author')) {
    function is_author(): bool
    {
        $user = current_user();
        return $user && $user['role_slug'] === 'author';
    }
}

if (! function_exists('is_staff')) {
    function is_staff(): bool
    {
        return is_admin();
    }
}

// Role-based capabilities (RBAC)
if (! function_exists('can_publish_articles')) {
    function can_publish_articles(): bool
    {
        return is_admin();
    }
}

if (! function_exists('can_manage_categories')) {
    function can_manage_categories(): bool
    {
        return is_admin();
    }
}

if (! function_exists('can_manage_messages')) {
    function can_manage_messages(): bool
    {
        return is_admin();
    }
}

if (! function_exists('can_manage_users')) {
    function can_manage_users(): bool
    {
        return is_admin();
    }
}

if (! function_exists('can_manage_settings')) {
    function can_manage_settings(): bool
    {
        return is_admin();
    }
}

if (! function_exists('login_user')) {
    function login_user(array $user, bool $remember = false): void
    {
        $session = session();
        $session->regenerate(true);
        $session->set('user_id', (int) $user['id']);

        model(UserModel::class)->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            model(UserModel::class)->update($user['id'], ['remember_token' => hash('sha256', $token)]);
            setcookie('remember_token', $token, [
                'expires'  => time() + REMEMBER_ME_DAYS * 86400,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        log_activity((int) $user['id'], 'login');
    }
}

if (! function_exists('logout_user')) {
    function logout_user(): void
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        if ($userId) {
            log_activity($userId, 'logout');
            model(UserModel::class)->update($userId, ['remember_token' => null]);
        }
        $session->destroy();
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

if (! function_exists('restore_remember_session')) {
    function restore_remember_session(): ?array
    {
        $token = $_COOKIE['remember_token'] ?? null;
        if (! $token) {
            return null;
        }
        $hash = hash('sha256', $token);
        $user = model(UserModel::class)->findByRememberToken($hash);
        if ($user) {
            login_user($user, false);
            return $user;
        }
        return null;
    }
}

if (! function_exists('check_login_rate_limit')) {
    function check_login_rate_limit(string $email): bool
    {
        $ip = service('request')->getIPAddress() ?? '';
        return model(LoginAttemptModel::class)->countRecent($email, $ip, LOGIN_LOCKOUT_MINUTES) < LOGIN_MAX_ATTEMPTS;
    }
}

if (! function_exists('record_login_attempt')) {
    function record_login_attempt(string $email): void
    {
        $ip = service('request')->getIPAddress() ?? '';
        model(LoginAttemptModel::class)->insert(['email' => $email, 'ip_address' => $ip]);
    }
}

if (! function_exists('clear_login_attempts')) {
    function clear_login_attempts(string $email): void
    {
        $ip = service('request')->getIPAddress() ?? '';
        model(LoginAttemptModel::class)->clearFor($email, $ip);
    }
}

if (! function_exists('register_user')) {
    function register_user(string $name, string $email, string $password): array
    {
        $userModel = model(UserModel::class);
        if ($userModel->findByEmail($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $token = bin2hex(random_bytes(32));
        $hash  = password_hash($password, PASSWORD_BCRYPT);

        $userId = $userModel->insert([
            'role_id'           => 3,
            'name'              => $name,
            'email'             => $email,
            'password_hash'     => $hash,
            'verification_token' => $token,
        ]);
        log_activity((int) $userId, 'register');

        return [
            'success'           => true,
            'user_id'           => (int) $userId,
            'verification_token' => $token,
            'message'           => 'Registration successful. Please verify your email.',
        ];
    }
}

if (! function_exists('verify_email_token')) {
    function verify_email_token(string $token): bool
    {
        $user = model(UserModel::class)->findByVerificationToken($token);
        if (! $user) {
            return false;
        }
        model(UserModel::class)->update($user['id'], [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'verification_token' => null,
        ]);
        return true;
    }
}

if (! function_exists('create_password_reset_token')) {
    function create_password_reset_token(string $email): ?string
    {
        $userModel = model(UserModel::class);
        $user = $userModel->where('email', $email)->where('is_banned', 0)->first();
        if (! $user) {
            return null;
        }
        $token = bin2hex(random_bytes(32));
        $userModel->update($user['id'], [
            'reset_token'        => $token,
            'reset_token_expires' => date('Y-m-d H:i:s', time() + 3600),
        ]);
        return $token;
    }
}

if (! function_exists('reset_password_with_token')) {
    function reset_password_with_token(string $token, string $password): bool
    {
        $user = model(UserModel::class)->findByResetToken($token);
        if (! $user) {
            return false;
        }
        model(UserModel::class)->update($user['id'], [
            'password_hash'       => password_hash($password, PASSWORD_BCRYPT),
            'reset_token'         => null,
            'reset_token_expires' => null,
        ]);
        return true;
    }
}

if (! function_exists('authenticate')) {
    function authenticate(string $email, string $password): array
    {
        if (! check_login_rate_limit($email)) {
            return ['success' => false, 'message' => 'Too many login attempts. Try again later.'];
        }

        $user = model(UserModel::class)->withRoleByEmail($email);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            record_login_attempt($email);
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if ($user['is_banned']) {
            return ['success' => false, 'message' => 'Your account has been suspended.'];
        }

        clear_login_attempts($email);
        return ['success' => true, 'user' => $user];
    }
}

if (! function_exists('require_login')) {
    function require_login(): void
    {
        if (! is_logged_in()) {
            session()->set('redirect_after_login', (string) service('uri'));
            redirect()->to('login')->with('error', 'Please log in to continue.')->send();
            exit;
        }
    }
}


