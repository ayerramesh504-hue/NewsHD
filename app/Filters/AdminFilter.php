<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Allows access to the admin area for admin users only.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! is_logged_in() || ! is_admin()) {
            if ($request->isAJAX() || str_starts_with((string) $request->getUri()->getPath(), '/api/')) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }
            return redirect()->to(env('security.adminLoginPath', 'secure-admin-login'))->with('error', 'Please sign in with an admin account.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
