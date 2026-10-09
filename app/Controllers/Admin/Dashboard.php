<?php

namespace App\Controllers\Admin;

use App\Models\ActivityLogModel;
use App\Models\ArticleModel;
use App\Models\ContactMessageModel;
use App\Models\UserModel;

class Dashboard extends BaseAdminController
{
    public function index(): string
    {
        $articleModel = new ArticleModel();
        $stats = [
            'articles'         => (int) $articleModel->countAll(),
            'published'        => (int) $articleModel->where('status', 'published')->countAllResults(),
            'drafts'           => (int) $articleModel->where('status', 'draft')->countAllResults(),
            'users'            => (int) (new UserModel())->countAll(),
            'new_messages'     => (int) (new ContactMessageModel())->where('status', 'new')->countAllResults(),
        ];

        $recent = (new ArticleModel())
            ->select('a.*, c.name AS category_name, u.name AS author_name')
            ->from('articles a')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->orderBy('a.created_at', 'DESC')
            ->limit(8)
            ->findAll();

        $chart = (new ActivityLogModel())->last7DaysChart();

        return $this->render('Dashboard', 'dashboard', 'dashboard', [
            'stats'  => $stats,
            'recent' => $recent,
            'chart'  => $chart,
        ], [
            'extraScripts' => ['dashboard.js'],
        ]);
    }
}
