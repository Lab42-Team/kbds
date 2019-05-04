<?php

use yii\db\Migration;

class m160401_035002_software_component_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%software_component}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'author' => $this->integer()->notNull(),
            'file_name' => $this->string(),
        ], $tableOptions);

        $this->createIndex('idx_software_component_name', '{{%software_component}}', 'name');
        $this->createIndex('idx_software_component_type', '{{%software_component}}', 'type');
        $this->createIndex('idx_software_component_status', '{{%software_component}}', 'status');

        $this->addForeignKey("software_component_user_fk", "{{%software_component}}",
            "author", "{{%user}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%software_component}}');
    }
}