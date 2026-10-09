<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'slug', 'description', 'sort_order'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    public function ordered()
    {
        return $this->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
    }
}
