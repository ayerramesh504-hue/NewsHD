<?php

namespace App\Controllers;

use App\Models\ArticleModel;

class Sitemap extends BaseController
{
    public function index()
    {
        $articles = (new ArticleModel())
            ->select('slug, updated_at')
            ->where('status', 'published')
            ->orderBy('updated_at', 'DESC')
            ->limit(5000)
            ->findAll();

        $categories = get_categories();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= '  <url><loc>' . e(url('')) . '</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>' . "\n";
        $xml .= '  <url><loc>' . e(url('search')) . '</loc><changefreq>daily</changefreq><priority>0.5</priority></url>' . "\n";

        foreach ($categories as $cat) {
            $xml .= '  <url><loc>' . e(url('category/' . $cat['slug'])) . '</loc><changefreq>daily</changefreq><priority>0.7</priority></url>' . "\n";
        }

        foreach ($articles as $a) {
            $xml .= '  <url><loc>' . e(url('article/' . $a['slug'])) . '</loc>'
                . '<lastmod>' . e(date('c', strtotime($a['updated_at']))) . '</lastmod>'
                . '<changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
        }

        $xml .= '</urlset>';

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=utf-8')
            ->setBody($xml);
    }
}
