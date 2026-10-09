<?php

namespace App\Models;

use CodeIgniter\Model;

class BookmarkModel extends Model
{
    protected $table            = 'bookmarks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'article_id'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function exists(int $userId, int $articleId)
    {
        return $this->where('user_id', $userId)->where('article_id', $articleId)->first();
    }

    public function toggle(int $userId, int $articleId): bool
    {
        $existing = $this->exists($userId, $articleId);
        if ($existing) {
            $this->delete($existing['id']);
            return false;
        }
        $this->insert(['user_id' => $userId, 'article_id' => $articleId]);
        return true;
    }

    public function byUser(int $userId)
    {
        return $this->select('a.id, a.title, a.slug, a.cover_image, a.published_at, b.created_at AS saved_at')
            ->from('bookmarks b')
            ->join('articles a', 'a.id = b.article_id')
            ->where('b.user_id', $userId)
            ->orderBy('b.created_at', 'DESC')
            ->findAll();
    }
}
