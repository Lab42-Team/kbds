<?php

use yii\db\Migration;

class m160408_095516_metaclass_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%metaclass}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'metamodel' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_metaclass_name', '{{%metaclass}}', 'name');

        $this->addForeignKey("metaclass_metamodel_fk", "{{%metaclass}}",
            "metamodel", "{{%metamodel}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%metaclass}}');
    }
}