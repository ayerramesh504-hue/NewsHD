<?php

use CodeIgniter\Exceptions\PageNotFoundException;

if (! function_exists('csrf_token_name')) {
    function csrf_token_name(): string
    {
        return config('Security')->tokenName;
    }
}

if (! function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return csrf_hash();
    }
}

if (! function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="' . e(csrf_token_name()) . '" value="' . e(csrf_hash()) . '">';
    }
}

if (! function_exists('require_csrf')) {
    function require_csrf(): void
    {
        $request = service('request');
        $token   = $request->getPost(csrf_token_name())
            ?? $request->getHeaderLine('X-CSRF-TOKEN')
            ?? $request->getHeaderLine('X-CSRF-Token')
            ?? null;

        if (! $token || ! hash_equals(csrf_hash(), $token)) {
            if (service('request')->isAJAX()) {
                service('response')->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Invalid CSRF token'])
                    ->send();
            } else {
                service('response')->setStatusCode(403)->setBody('Invalid CSRF token.')->send();
            }
            exit;
        }
    }
}
