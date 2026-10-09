<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArticleTagsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'article_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tag_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey(['article_id', 'tag_id'], true);
        $this->forge->addKey('tag_id', false, false, 'idx_article_tags_tag');
        $this->forge->addForeignKey('article_id', 'articles', 'id', 'CASCADE', 'CASCADE', 'fk_article_tags_article');
        $this->forge->addForeignKey('tag_id', 'tags', 'id', 'CASCADE', 'CASCADE', 'fk_article_tags_tag');
        $this->forge->createTable('article_tags');
    }

    public function down()
    {
        $this->forge->dropTable('article_tags');
    }
}
