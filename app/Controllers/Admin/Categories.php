<?php

namespace App\Controllers\Admin;

use App\Models\CategoryModel;

class Categories extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('Categories', 'categories', 'categories/index', [
            'categories' => get_categories(),
            'error'      => '',
            'old'        => [],
        ]);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        require_csrf();

        $id          = (int) $this->request->getPost('id');
        $name        = sanitize($this->request->getPost('name') ?? '');
        $slugInput   = trim((string) $this->request->getPost('slug'));
        $slug        = slugify($slugInput !== '' ? $slugInput : $name);
        $description = sanitize($this->request->getPost('description') ?? '');
        $sortOrder   = (int) $this->request->getPost('sort_order');

        $model = new CategoryModel();

        if (! $name) {
            return $this->render('Categories', 'categories', 'categories/index', [
                'categories' => get_categories(),
                'error'      => 'Category name is required.',
                'old'        => ['name' => $name, 'slug' => $slug, 'description' => $description, 'sort_order' => $sortOrder],
            ]);
        }

        $data = ['name' => $name, 'slug' => $slug, 'description' => $description, 'sort_order' => $sortOrder];

        if ($id) {
            $model->update($id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to('admin/categories')->with('success', 'Category saved successfully.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        require_csrf();
        (new CategoryModel())->delete($id);
        return redirect()->to('admin/categories')->with('success', 'Category deleted successfully.');
    }
}
