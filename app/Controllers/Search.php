<?php

namespace App\Controllers;

use App\Models\ArticleModel;

class Search extends BaseController
{
    public function index(): string
    {
        $query = sanitize($this->request->getGet('q') ?? '');
        $articles = [];
        $total    = 0;

        if ($query !== '') {
            $articleModel = new ArticleModel();
            $total = $articleModel->searchCount($query);
            $articles = $articleModel->search($query, 0, 1000);
        }

        $data = [
            'meta'      => page_meta($query ? 'Search: ' . $query : 'Search', 'Search news articles'),
            'activeNav' => '',
            'query'     => $query,
            'articles'  => $articles,
            'total'     => $total,
        ];

        return view('site/search', $data);
    }
}
