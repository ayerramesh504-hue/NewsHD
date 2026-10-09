<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table            = 'articles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'author_id', 'category_id', 'title', 'slug', 'excerpt', 'content',
        'cover_image', 'status', 'is_featured', 'is_breaking', 'view_count',
        'published_at', 'scheduled_at', 'meta_title', 'meta_description',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findBySlugPublished(string $slug)
    {
        return $this->db->table('articles a')
            ->select('a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, u.avatar AS author_avatar')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->where('a.slug', $slug)
            ->where('a.status', 'published')
            ->get()->getRowArray();
    }

    public function findByIdWithDetails(int $id)
    {
        return $this->db->table('articles a')
            ->select('a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, u.avatar AS author_avatar')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->where('a.id', $id)
            ->get()->getRowArray();
    }

    public function publishedWithDetails(?int $limit = null, ?int $offset = null)
    {
        $builder = $this->db->table('articles a')
            ->select('a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->where('a.status', 'published')
            ->orderBy('a.published_at', 'DESC');
        if ($limit !== null) {
            $builder->limit($limit, $offset ?? 0);
        }
        return $builder->get()->getResultArray();
    }

    public function countPublished()
    {
        return (int) $this->db->table('articles')->where('status', 'published')->countAllResults();
    }

    public function breaking()
    {
        return $this->db->table('articles a')
            ->select('a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->where('a.status', 'published')
            ->where('a.is_breaking', 1)
            ->orderBy('a.published_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
    }

    public function featured()
    {
        return $this->db->table('articles a')
            ->select('a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->where('a.status', 'published')
            ->where('a.is_featured', 1)
            ->orderBy('a.published_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
    }

    public function trending(int $limit = 5)
    {
        return $this->db->table('articles a')
            ->select('a.id, a.title, a.slug, a.view_count, c.name AS category_name')
            ->join('categories c', 'c.id = a.category_id')
            ->where('a.status', 'published')
            ->orderBy('a.view_count', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function byCategory(int $categoryId, ?int $limit = null, ?int $offset = null)
    {
        $builder = $this->db->table('articles a')
            ->select('a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name')
            ->join('categories c', 'c.id = a.category_id')
            ->join('users u', 'u.id = a.author_id')
            ->where('a.category_id', $categoryId)
            ->where('a.status', 'published')
            ->orderBy('a.published_at', 'DESC');
        if ($limit !== null) {
            $builder->limit($limit, $offset ?? 0);
        }
        return $builder->get()->getResultArray();
    }

    public function countByCategory(int $categoryId)
    {
        return (int) $this->db->table('articles')
            ->where('category_id', $categoryId)
            ->where('status', 'published')
            ->countAllResults();
    }

    public function related(int $categoryId, int $excludeId, int $limit = 3)
    {
        return $this->db->table('articles a')
            ->select('a.id, a.title, a.slug, a.cover_image, a.published_at')
            ->where('a.category_id', $categoryId)
            ->where('a.id !=', $excludeId)
            ->where('a.status', 'published')
            ->orderBy('a.published_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function incrementViews(int $articleId)
    {
        return $this->db->table($this->table)
            ->where('id', $articleId)
            ->set('view_count', 'view_count + 1', false)
            ->update();
    }

    public function searchCount(string $query): int
    {
        $like = '%' . $query . '%';
        $sql = "SELECT COUNT(DISTINCT a.id) AS count
                FROM articles a
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN categories c ON c.id = a.category_id
                LEFT JOIN article_tags at ON at.article_id = a.id
                LEFT JOIN tags t ON t.id = at.tag_id
                WHERE a.status = 'published' AND (
                    a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?
                    OR c.name LIKE ? OR t.name LIKE ?
                )";
        $row = $this->db->query($sql, [$like, $like, $like, $like, $like])->getRowArray();
        return (int) ($row['count'] ?? 0);
    }

    public function search(string $query, int $offset = 0, int $limit = ITEMS_PER_PAGE)
    {
        $like = '%' . $query . '%';
        $sql = "SELECT DISTINCT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
                FROM articles a
                INNER JOIN categories c ON c.id = a.category_id
                INNER JOIN users u ON u.id = a.author_id
                LEFT JOIN article_tags at ON at.article_id = a.id
                LEFT JOIN tags t ON t.id = at.tag_id
                WHERE a.status = 'published' AND (
                    a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?
                    OR c.name LIKE ? OR t.name LIKE ?
                )
                ORDER BY a.published_at DESC
                LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$like, $like, $like, $like, $like, $limit, $offset])->getResultArray();
    }

    public function apiSearch(string $query, int $limit = 8)
    {
        $like = '%' . $query . '%';
        $sql = "SELECT DISTINCT a.id, a.title, a.slug, a.excerpt, c.name AS category_name
                FROM articles a
                INNER JOIN categories c ON c.id = a.category_id
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN article_tags at ON at.article_id = a.id
                LEFT JOIN tags t ON t.id = at.tag_id
                WHERE a.status = 'published' AND (
                    a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?
                    OR c.name LIKE ? OR t.name LIKE ?
                )
                ORDER BY a.published_at DESC
                LIMIT ?";
        return $this->db->query($sql, [$like, $like, $like, $like, $like, $limit])->getResultArray();
    }

    public function adminListByStatus(string $search = '', string $status = '', int $offset = 0, int $limit = ADMIN_ITEMS_PER_PAGE)
    {
        $sql = "SELECT a.*, c.name AS category_name, u.name AS author_name,
                    (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM article_tags at
                     INNER JOIN tags t ON t.id = at.tag_id WHERE at.article_id = a.id) AS tag_names
                FROM articles a
                INNER JOIN categories c ON c.id = a.category_id
                INNER JOIN users u ON u.id = a.author_id";
        $params = [];
        $where = [];
        if ($search !== '') {
            $where[] = 'a.title LIKE ?';
            $params[] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'a.status = ?';
            $params[] = $status;
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->query($sql, $params)->getResultArray();
    }

    public function adminCountByStatus(string $search = '', string $status = ''): int
    {
        $sql = 'SELECT COUNT(*) AS count FROM articles a';
        $params = [];
        $where = [];
        if ($search !== '') {
            $where[] = 'a.title LIKE ?';
            $params[] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'a.status = ?';
            $params[] = $status;
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['count'] ?? 0);
    }

    public function forAuthor(int $authorId, string $search = '', string $status = '', int $offset = 0, int $limit = ADMIN_ITEMS_PER_PAGE)
    {
        $sql = "SELECT a.*, c.name AS category_name, u.name AS author_name,
                    (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM article_tags at
                     INNER JOIN tags t ON t.id = at.tag_id WHERE at.article_id = a.id) AS tag_names
                FROM articles a
                INNER JOIN categories c ON c.id = a.category_id
                INNER JOIN users u ON u.id = a.author_id
                WHERE a.author_id = ?";
        $params = [$authorId];
        if ($search !== '') {
            $sql .= ' AND a.title LIKE ?';
            $params[] = '%' . $search . '%';
        }
        if ($status !== '') {
            $sql .= ' AND a.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->query($sql, $params)->getResultArray();
    }

    public function authorCount(int $authorId, string $search = '', string $status = ''): int
    {
        $sql = 'SELECT COUNT(*) AS count FROM articles a WHERE a.author_id = ?';
        $params = [$authorId];
        if ($search !== '') {
            $sql .= ' AND a.title LIKE ?';
            $params[] = '%' . $search . '%';
        }
        if ($status !== '') {
            $sql .= ' AND a.status = ?';
            $params[] = $status;
        }
        $row = $this->db->query($sql, $params)->getRowArray();
        return (int) ($row['count'] ?? 0);
    }
}
