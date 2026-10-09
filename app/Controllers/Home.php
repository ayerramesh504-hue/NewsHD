<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\CategoryModel;

class Home extends BaseController
{
    public function index(): string
    {
        $articleModel = new ArticleModel();

        $breaking = $articleModel->breaking();
        if (empty($breaking)) {
            $breaking = $articleModel->featured();
        }

        $heroSide = $articleModel->featured();
        if (empty($heroSide)) {
            $heroSide = $breaking;
        }
        $heroSide = array_slice($heroSide, 0, 2);

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $total = $articleModel->countPublished();
        $articles = $articleModel->publishedWithDetails($limit, $offset);

        $trending = $articleModel->trending(5);

        $categoryModel = new CategoryModel();
        $categoriesWithCount = [];
        foreach ($categoryModel->ordered() as $cat) {
            $categoriesWithCount[] = [
                'id'    => (int) $cat['id'],
                'name'  => $cat['name'],
                'slug'  => $cat['slug'],
                'count' => $articleModel->countByCategory((int) $cat['id']),
            ];
        }

        $data = [
            'meta'          => page_meta(get_setting('site_tagline', 'Latest News'), get_setting('site_description')),
            'activeNav'     => 'home',
            'breaking'      => $breaking,
            'heroSide'      => $heroSide,
            'articles'      => $articles,
            'trending'      => $trending,
            'categoriesWidget' => $categoriesWithCount,
            'currentPage'   => $page,
            'nextPage'      => $page + 1,
            'hasMore'       => $page * $limit < $total,
        ];

        return view('site/home', $data);
    }
}
