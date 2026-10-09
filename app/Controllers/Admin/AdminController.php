<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdminController extends BaseController
{
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! is_admin()) {
            return redirect()->to(env('security.adminLoginPath', 'secure-admin-login'));
        }
        return redirect()->to('admin/dashboard');
    }

    public function login(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        if (is_admin()) {
            return redirect()->to('admin');
        }

        $error = '';
        if (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $email    = filter_var(trim($this->request->getPost('email') ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $this->request->getPost('password') ?? '';
            $result   = authenticate($email, $password);
            if ($result['success'] && $result['user']['role_slug'] === 'admin') {
                login_user($result['user'], ! empty($this->request->getPost('remember')));
                return redirect()->to('admin');
            }
            $error = $result['success'] ? 'You do not have admin access.' : ($result['message'] ?? 'Login failed.');
        }

        $data = [
            'error'    => $error,
            'email'    => $this->request->getPost('email') ?? '',
            'siteUrl'  => url('/'),
            'siteName' => get_setting('site_name', APP_NAME),
        ];

        return view('admin/login', $data);
    }
}
