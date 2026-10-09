<?php

namespace App\Controllers\Admin;

use App\Models\ContactMessageModel;

class Messages extends BaseAdminController
{
    public function index(): string
    {
        $messages = (new ContactMessageModel())->withUser();

        return $this->render('Messages', 'messages', 'messages/index', [
            'messages' => $messages,
        ]);
    }

    public function action(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        require_csrf();

        $status = sanitize($this->request->getPost('status') ?? 'read');
        $reply  = sanitize($this->request->getPost('admin_reply') ?? '');

        $model = new ContactMessageModel();
        $message = $model->find($id);
        if (! $message) {
            return redirect()->to('admin/messages')->with('error', 'Message not found.');
        }

        $model->update($id, [
            'status'      => in_array($status, ['new', 'read', 'resolved'], true) ? $status : 'read',
            'admin_reply' => $reply !== '' ? $reply : null,
        ]);

        return redirect()->to('admin/messages')->with('success', 'Message updated.');
    }
}
