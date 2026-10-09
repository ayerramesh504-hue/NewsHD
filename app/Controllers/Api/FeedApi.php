<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ArticleModel;

class FeedApi extends BaseController
{
    public function index()
    {
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $articleModel = new ArticleModel();
        $articles = $articleModel->publishedWithDetails($limit, $offset);
        $total = $articleModel->countPublished();

        $html = '';
        foreach ($articles as $article) {
            $html .= view('site/partials/article_card', ['article' => $article]);
        }

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
            'page' => $page,
            'hasMore' => ($page * $limit) < $total,
            'nextPage' => $page + 1,
        ]);
    }
}
