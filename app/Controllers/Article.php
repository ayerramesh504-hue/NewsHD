<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\BookmarkModel;

class Article extends BaseController
{
    public function view(string $slug): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $slug = sanitize($slug);
        if (! $slug) {
            return redirect()->to('/');
        }

        $articleModel = new ArticleModel();
        $article = $articleModel->findBySlugPublished($slug);

        if (! $article) {
            return $this->render404();
        }

        increment_article_views((int) $article['id']);
        $tags   = get_article_tags((int) $article['id']);
        $related = $articleModel->related((int) $article['category_id'], (int) $article['id'], 3);

        $user        = current_user();
        $isBookmarked = false;
        if ($user) {
            $isBookmarked = (bool) (new BookmarkModel())->exists((int) $user['id'], (int) $article['id']);
        }

        $data = [
            'meta'         => page_meta(
                $article['meta_title'] ?: $article['title'],
                $article['meta_description'] ?: truncate(strip_tags($article['excerpt'] ?? ''), 160),
                $article['cover_image'],
                url('article/' . $article['slug'])
            ),
            'activeNav'    => $article['category_slug'],
            'article'      => $article,
            'tags'         => $tags,
            'related'      => $related,
            'trending'     => $articleModel->trending(5),
            'user'         => $user,
            'isBookmarked' => $isBookmarked,
            'extraCss'     => ['https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css'],
        ];

        return view('site/article', $data);
    }

    private function render404(): string
    {
        $data = [
            'meta'      => page_meta('Article Not Found'),
            'activeNav' => '',
        ];
        return view('site/404', $data);
    }
}
