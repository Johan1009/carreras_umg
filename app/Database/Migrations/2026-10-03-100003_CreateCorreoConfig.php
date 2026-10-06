<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCorreoConfig extends Migration
{
    public function up(): void
    {
        // Tabla de una sola fila (id = 1) con los parámetros SMTP.
        $this->forge->addField([
            'id'              => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'auto_increment' => true],
            'host'            => ['type' => 'VARCHAR', 'constraint' => 190, 'default' => 'smtp.gmail.com'],
            'puerto'          => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 587],
            'cifrado'         => ['type' => 'ENUM', 'constraint' => ['tls', 'ssl', 'ninguno'], 'default' => 'tls'],
            'usuario'         => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'clave'           => ['type' => 'TEXT', 'null' => true],
            'remitente_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'remitente_nombre' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('correo_config');
    }

    public function down(): void
    {
        $this->forge->dropTable('correo_config', true);
    }
}
