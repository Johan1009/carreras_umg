<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCarreras extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 190],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre');
        $this->forge->createTable('carreras');

        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'carrera_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'tipo'            => ['type' => 'ENUM', 'constraint' => ['principal', 'complementario_1', 'complementario_2']],
            'nombre_original' => ['type' => 'VARCHAR', 'constraint' => 255],
            'ruta'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'tamano'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['carrera_id', 'tipo']);
        $this->forge->addForeignKey('carrera_id', 'carreras', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('carrera_archivos');
    }

    public function down(): void
    {
        $this->forge->dropTable('carrera_archivos', true);
        $this->forge->dropTable('carreras', true);
    }
}
