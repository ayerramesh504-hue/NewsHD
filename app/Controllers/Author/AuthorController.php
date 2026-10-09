<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;

class AuthorController extends BaseController
{
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! is_author()) {
            return redirect()->to(env('security.authorLoginPath', 'secure-author-login'));
        }

        return redirect()->to('author/dashboard');
    }

    public function login(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        if (is_author()) {
            return redirect()->to('author');
        }

        $error = '';
        if (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $email    = filter_var(trim($this->request->getPost('email') ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $this->request->getPost('password') ?? '';
            $result   = authenticate($email, $password);

            if ($result['success'] && $result['user']['role_slug'] === 'author') {
                login_user($result['user'], ! empty($this->request->getPost('remember')));
                return redirect()->to('author');
            }

            $error = $result['success'] ? 'You do not have author access.' : ($result['message'] ?? 'Login failed.');
        }

        $data = [
            'error'    => $error,
            'email'    => $this->request->getPost('email') ?? '',
            'siteUrl'  => url('/'),
            'siteName' => get_setting('site_name', APP_NAME),
        ];

        return view('author/login', $data);
    }
}
