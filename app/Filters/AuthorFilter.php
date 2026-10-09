<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthorFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! is_logged_in() || ! is_author()) {
            if ($request->isAJAX() || str_starts_with((string) $request->getUri()->getPath(), '/api/')) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Unauthorized']);
            }

            return redirect()->to(env('security.authorLoginPath', 'secure-author-login'))->with('error', 'Please sign in with an author account.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
