<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBookmarksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'article_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'created_at' => "created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'article_id'], false, true, 'uk_bookmark');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_bookmarks_user');
        $this->forge->addForeignKey('article_id', 'articles', 'id', 'CASCADE', 'CASCADE', 'fk_bookmarks_article');
        $this->forge->createTable('bookmarks');
    }

    public function down()
    {
        $this->forge->dropTable('bookmarks');
    }
}
