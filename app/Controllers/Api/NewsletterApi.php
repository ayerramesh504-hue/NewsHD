<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\NewsletterSubscriberModel;

class NewsletterApi extends BaseController
{
    public function index()
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method not allowed']);
        }

        require_csrf();
        $email = filter_var(trim($this->request->getPost('email') ?? ''), FILTER_VALIDATE_EMAIL);
        if (! $email) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid email']);
        }

        try {
            (new NewsletterSubscriberModel())->subscribe($email);
            return $this->response->setJSON(['success' => true, 'message' => 'Subscribed successfully!']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Subscription failed']);
        }
    }
}
