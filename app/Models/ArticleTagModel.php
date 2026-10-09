<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleTagModel extends Model
{
    protected $table         = 'article_tags';
    protected $primaryKey    = null;
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['article_id', 'tag_id'];

    protected $useTimestamps = false;

    public function sync(int $articleId, array $tagNames): void
    {
        $db = $this->db;
        $tagModel = new TagModel();

        $db->table($this->table)->where('article_id', $articleId)->delete();

        foreach ($tagNames as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $slug = slugify($name);
            $tag = $tagModel->where('slug', $slug)->first();
            if ($tag) {
                $tagId = (int) $tag['id'];
            } else {
                $tagModel->insert(['name' => $name, 'slug' => $slug]);
                $tagId = (int) $tagModel->getInsertID();
            }
            $db->table($this->table)->ignore(true)
                ->insert(['article_id' => $articleId, 'tag_id' => $tagId]);
        }
    }
}
