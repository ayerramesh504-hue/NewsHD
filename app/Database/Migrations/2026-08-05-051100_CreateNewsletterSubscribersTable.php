<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsletterSubscribersTable extends Migration
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
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'subscribed_at' => "subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('email', false, true, 'uk_newsletter_email');
        $this->forge->createTable('newsletter_subscribers');
    }

    public function down()
    {
        $this->forge->dropTable('newsletter_subscribers');
    }
}
