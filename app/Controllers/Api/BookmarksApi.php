<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BookmarkModel;

class BookmarksApi extends BaseController
{
    public function index()
    {
        if (! is_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Login required']);
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method not allowed']);
        }

        require_csrf();
        $input     = $this->request->getJSON(true) ?? $this->request->getPost();
        $articleId = (int) ($input['article_id'] ?? 0);
        $userId    = (int) current_user()['id'];

        $bookmarked = (new BookmarkModel())->toggle($userId, $articleId);

        return $this->response->setJSON(['success' => true, 'bookmarked' => $bookmarked]);
    }
}
