<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Allows access only for super-admin role.
 */
class AdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = current_user();
        if (! $user || $user['role_slug'] !== 'admin') {
            if ($request->isAJAX() || str_starts_with((string) $request->getUri()->getPath(), '/api/')) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }
            return redirect()->to('admin')->with('error', 'Only the site administrator can perform this action.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
