<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $error = '';
        if (is_logged_in()) {
            return redirect()->to('/');
        }

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $email    = filter_var(trim($this->request->getPost('email') ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $this->request->getPost('password') ?? '';
            $remember = ! empty($this->request->getPost('remember'));

            if (! $email || ! $password) {
                $error = 'Please enter email and password.';
            } else {
                $result = authenticate($email, $password);
                if ($result['success']) {
                    login_user($result['user'], $remember);
                    $redirect = session()->get('redirect_after_login') ?? url('/');
                    session()->remove('redirect_after_login');
                    if (is_admin() && ! empty($this->request->getPost('admin_login'))) {
                        $redirect = url('admin');
                    }
                    return redirect()->to($redirect);
                }
                $error = $result['message'];
            }
        }

        $data = [
            'meta'      => page_meta('Login'),
            'activeNav' => '',
            'error'     => $error,
            'email'     => $this->request->getPost('email') ?? '',
        ];

        return view('site/login', $data);
    }

    public function register(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        if (is_logged_in()) {
            return redirect()->to('/');
        }

        $error  = '';
        $success = '';

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $name     = sanitize($this->request->getPost('name') ?? '');
            $email    = filter_var(trim($this->request->getPost('email') ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $this->request->getPost('password') ?? '';
            $confirm  = $this->request->getPost('password_confirm') ?? '';

            if (strlen($name) < 2) {
                $error = 'Name must be at least 2 characters.';
            } elseif (! $email) {
                $error = 'Please enter a valid email.';
            } elseif ($msg = validate_password_strength($password)) {
                $error = $msg;
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $result = register_user($name, $email, $password);
                if ($result['success']) {
                    $verifyUrl = url('verify/' . $result['verification_token']);
                    $success   = 'Registration successful! <a href="' . e($verifyUrl) . '">Click here to verify your email</a> (simulated).';
                } else {
                    $error = $result['message'];
                }
            }
        }

        $data = [
            'meta'      => page_meta('Register'),
            'activeNav' => '',
            'error'     => $error,
            'success'   => $success,
            'name'      => $this->request->getPost('name') ?? '',
            'email'     => $this->request->getPost('email') ?? '',
        ];

        return view('site/register', $data);
    }

    public function logout()
    {
        logout_user();
        return redirect()->to('/login');
    }

    public function verify(string $token): string
    {
        $verified = $token && verify_email_token($token);

        $data = [
            'meta'      => page_meta('Email Verification'),
            'activeNav' => '',
            'verified'  => $verified,
        ];

        return view('site/verify', $data);
    }

    public function forgotPassword(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $error  = '';
        $success = '';
        $token  = sanitize($this->request->getGet('token') ?? '');

        if (strtolower((string) $this->request->getMethod()) === 'post' && $this->request->getPost('reset')) {
            require_csrf();
            $token    = sanitize($this->request->getPost('token') ?? '');
            $password = $this->request->getPost('password') ?? '';
            $confirm  = $this->request->getPost('password_confirm') ?? '';

            if ($msg = validate_password_strength($password)) {
                $error = $msg;
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } elseif (reset_password_with_token($token, $password)) {
                $success = 'Password reset successfully. You can now login.';
                $token   = '';
            } else {
                $error = 'Invalid or expired reset link.';
            }
        } elseif (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $email = filter_var(trim($this->request->getPost('email') ?? ''), FILTER_VALIDATE_EMAIL);
            if (! $email) {
                $error = 'Please enter a valid email.';
            } else {
                $resetToken = create_password_reset_token($email);
                if ($resetToken) {
                    $resetUrl = url('forgot-password?token=' . $resetToken);
                    $success  = 'If that email exists, a reset link was generated. <a href="' . e($resetUrl) . '">Reset password (simulated)</a>';
                } else {
                    $success = 'If that email exists, a reset link will be sent.';
                }
            }
        }

        $data = [
            'meta'      => page_meta('Forgot Password'),
            'activeNav' => '',
            'error'     => $error,
            'success'   => $success,
            'token'     => $token,
        ];

        return view('site/forgot_password', $data);
    }
}
