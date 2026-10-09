<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;

class Contact extends BaseController
{
    public function index(): \CodeIgniter\HTTP\RedirectResponse|string
    {
require_login();

        $user   = current_user();
        $departments = array_values(array_filter(array_map('trim', explode('|', (string) get_setting('contact_departments', 'General|Editorial|Technical|Advertising|Feedback')))));

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $subject  = sanitize($this->request->getPost('subject') ?? '');
            $category = sanitize($this->request->getPost('category') ?? '');
            $message  = sanitize($this->request->getPost('message') ?? '');

            $error = '';
            if (strlen($subject) < 3) {
                $error = 'Subject must be at least 3 characters.';
            } elseif ($message === '') {
                $error = 'Please enter a message.';
            } elseif (! in_array($category, $departments, true)) {
                $error = 'Please select a valid department.';
            }

            if ($error !== '') {
                return redirect()->back()->with('contact_error', $error)->withInput();
            }

            $messageId = (new ContactMessageModel())->insert([
                'user_id'  => $user['id'],
                'subject'  => $subject,
                'category' => $category,
                'message'  => $message,
            ]);
            log_activity((int) $user['id'], 'contact_submit', 'contact_message', (int) $messageId);

            return redirect()->to('contact')->with('contact_success', 'Your message has been sent. We will respond soon.');
        }

        $data = [
            'meta'        => page_meta('Contact'),
            'activeNav'   => 'contact',
            'user'        => $user,
            'departments' => $departments,
            'error'       => session('contact_error'),
            'success'     => session('contact_success'),
        ];

        return view('site/contact', $data);
    }
}
