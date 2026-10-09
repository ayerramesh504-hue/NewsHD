<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\CategoryModel;

class Category extends BaseController
{
    public function view(string $slug): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $slug = sanitize($slug);
        if (! $slug) {
            return redirect()->to('/');
        }

        $category = (new CategoryModel())->findBySlug($slug);
        if (! $category) {
            return redirect()->to('/');
        }

        $articles  = (new ArticleModel())->byCategory((int) $category['id']);

        $data = [
            'meta'      => page_meta($category['name'], $category['description'] ?? ''),
            'activeNav' => $category['slug'],
            'category'  => $category,
            'articles'  => $articles,
        ];

        return view('site/category', $data);
    }
}
