<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class BaseAdminController extends BaseController
{
    protected function render(string $title, string $active, string $view, array $data = [], array $pageData = []): string
    {
        $user = current_user();
        $layout = view(is_admin() ? 'admin/layout' : 'author/layout', [
            'title'        => $title,
            'active'       => $active,
            'content'      => view('admin/' . $view, $data),
            'extraCss'     => $pageData['extraCss'] ?? [],
            'extraScripts' => $pageData['extraScripts'] ?? [],
        ]);

        return $layout;
    }
}
