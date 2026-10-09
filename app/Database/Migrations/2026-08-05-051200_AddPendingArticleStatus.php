<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPendingArticleStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE articles MODIFY status ENUM('draft','pending','published','scheduled') NOT NULL DEFAULT 'draft'");
    }

    public function down()
    {
        $this->db->query("UPDATE articles SET status = 'draft' WHERE status = 'pending'");
        $this->db->query("ALTER TABLE articles MODIFY status ENUM('draft','published','scheduled') NOT NULL DEFAULT 'draft'");
    }
}
