<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;

abstract class BaseAuthorController extends BaseController
{
    protected function render(string $title, string $active, string $view, array $data = [], array $pageData = []): string
    {
        $layout = view('author/layout', [
            'title'        => $title,
            'active'       => $active,
            'content'      => view('author/' . $view, $data),
            'extraCss'     => $pageData['extraCss'] ?? [],
            'extraScripts' => $pageData['extraScripts'] ?? [],
        ]);

        return $layout;
    }
}
