<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddAiLogs extends Migration {
    public function up() {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'prompt' => ['type' => 'TEXT'],
            'response' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('ai_logs');
    }
    public function down() { $this->forge->dropTable('ai_logs'); }
}
