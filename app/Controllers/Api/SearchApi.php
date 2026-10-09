<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ArticleModel;

class SearchApi extends BaseController
{
    public function index()
    {
        $q = sanitize($this->request->getGet('q') ?? '');
        if (strlen($q) < 2) {
            return $this->response->setJSON(['success' => true, 'results' => []]);
        }

        $results = (new ArticleModel())->apiSearch($q);

        foreach ($results as &$r) {
            $r['url']     = url('article/' . $r['slug']);
            $r['excerpt'] = truncate(strip_tags($r['excerpt'] ?? ''), 80);
        }

        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }
}
