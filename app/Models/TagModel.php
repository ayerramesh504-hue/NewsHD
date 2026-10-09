<?php

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table            = 'tags';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'slug'];

    protected $useTimestamps = false;

    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    public function forArticle(int $articleId)
    {
        return $this->db->table('tags t')
            ->select('t.id, t.name, t.slug')
            ->join('article_tags at', 'at.tag_id = t.id')
            ->where('at.article_id', $articleId)
            ->get()
            ->getResultArray();
    }
}
