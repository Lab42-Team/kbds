<?php

use yii\db\Migration;

class m160909_091537_fact_slot_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%fact_slot}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'value' => $this->string(),
            'description' => $this->string(),
            'fact' => $this->integer()->notNull(),
            'data_type' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_fact_slot_name', '{{%fact_slot}}', 'name');

        $this->addForeignKey("fact_slot_fact_fk", "{{%fact_slot}}", "fact", "{{%fact}}", "id", 'CASCADE');
        $this->addForeignKey("fact_slot_data_type_fk", "{{%fact_slot}}",
            "data_type", "{{%data_type}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%fact_slot}}');
    }
}