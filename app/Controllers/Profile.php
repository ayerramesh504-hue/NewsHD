<?php

namespace App\Controllers;

use App\Models\BookmarkModel;
use App\Models\UserModel;

class Profile extends BaseController
{
    public function index(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        require_login();

        $user   = current_user();
        $tab    = sanitize($this->request->getGet('tab') ?? 'profile');
        $error  = '';
        $success = '';

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            require_csrf();
            $action = $this->request->getPost('action') ?? '';

            if ($action === 'profile') {
                $name = sanitize($this->request->getPost('name') ?? '');
                $bio  = sanitize($this->request->getPost('bio') ?? '');
                if (strlen($name) < 2) {
                    $error = 'Name must be at least 2 characters.';
                } else {
                    $avatar = $user['avatar'];
                    $file   = $this->request->getFile('avatar');
                    if ($file && $file->isValid()) {
                        $tmp = $file->getTempName();
                        $fake = [
                            'name'     => $file->getName(),
                            'tmp_name' => $tmp,
                            'size'     => $file->getSize(),
                            'error'    => UPLOAD_ERR_OK,
                        ];
                        $uploaded = upload_image($fake, 'avatars');
                        if ($uploaded) {
                            $avatar = $uploaded;
                        }
                    }
                    (new UserModel())->update($user['id'], ['name' => $name, 'bio' => $bio, 'avatar' => $avatar]);
                    $success = 'Profile updated successfully.';
                    $user = current_user();
                }
            } elseif ($action === 'password') {
                $current = $this->request->getPost('current_password') ?? '';
                $new     = $this->request->getPost('new_password') ?? '';
                $confirm = $this->request->getPost('password_confirm') ?? '';
                if (! password_verify($current, $user['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } elseif ($msg = validate_password_strength($new)) {
                    $error = $msg;
                } elseif ($new !== $confirm) {
                    $error = 'New passwords do not match.';
                } else {
                    (new UserModel())->update($user['id'], ['password_hash' => password_hash($new, PASSWORD_BCRYPT)]);
                    $success = 'Password changed successfully.';
                }
            }
        }

        $bookmarks    = (new BookmarkModel())->byUser((int) $user['id']);

        $data = [
            'meta'         => page_meta('My Profile'),
            'activeNav'    => '',
            'user'         => $user,
            'tab'          => $tab,
            'error'        => $error,
            'success'      => $success,
            'bookmarks'    => $bookmarks,
        ];

        return view('site/profile', $data);
    }
}
