<?php

use yii\db\Migration;

class m160429_101812_data_type_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%data_type}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'knowledge_base' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_data_type_name', '{{%data_type}}', 'name');

        $this->addForeignKey("data_type_knowledge_base_fk", "{{%data_type}}",
            "knowledge_base", "{{%knowledge_base}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%data_type}}');
    }
}