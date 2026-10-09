<?php

namespace App\Controllers\Admin;

use App\Models\RoleModel;
use App\Models\UserModel;

class Users extends BaseAdminController
{
    public function index(): string
    {
        $currentUser = current_user();
        $users = (new UserModel())
            ->select('users.id, users.name, users.email, users.role_id, users.is_banned, users.last_login_at, users.created_at, r.name AS role_name, r.slug AS role_slug')
            ->join('roles r', 'r.id = users.role_id')
            ->where('users.id !=', (int) $currentUser['id'])
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        $roles = array_values(array_filter((new RoleModel())->findAll(), static fn (array $role): bool => $role['slug'] !== 'editor'));

        return $this->render('Users', 'users', 'users/index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        require_csrf();

        $id     = (int) $this->request->getPost('id');
        $roleId = (int) $this->request->getPost('role_id');
        $selectedRole = (new RoleModel())->find($roleId);
        if (! $selectedRole || $selectedRole['slug'] === 'editor') {
            return redirect()->to('admin/users')->with('error', 'That role is not available.');
        }
        $banned = ! empty($this->request->getPost('is_banned')) ? 1 : 0;

        $user = (new UserModel())->find($id);
        if (! $user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        (new UserModel())->update($id, ['role_id' => $roleId, 'is_banned' => $banned]);

        return redirect()->to('admin/users')->with('success', 'User updated successfully.');
    }
}
