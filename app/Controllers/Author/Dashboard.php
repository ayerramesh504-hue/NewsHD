<?php

namespace App\Controllers\Author;

use App\Models\ArticleModel;

class Dashboard extends BaseAuthorController
{
    public function index(): string
    {
        $user = current_user();
        $articleModel = new ArticleModel();

        $stats = [
            'articles'  => (int) $articleModel->where('author_id', (int) $user['id'])->countAllResults(),
            'published' => (int) $articleModel->where('author_id', (int) $user['id'])->where('status', 'published')->countAllResults(),
            'drafts'    => (int) $articleModel->where('author_id', (int) $user['id'])->where('status', 'draft')->countAllResults(),
        ];

        $recent = $articleModel
            ->where('author_id', (int) $user['id'])
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->findAll();

        return $this->render('Author Dashboard', 'dashboard', 'dashboard', [
            'stats'  => $stats,
            'recent' => $recent,
        ]);
    }
}
