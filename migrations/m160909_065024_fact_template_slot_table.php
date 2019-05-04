<?php

use yii\db\Migration;

class m160909_065024_fact_template_slot_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%fact_template_slot}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'default_value' => $this->string(),
            'description' => $this->string(),
            'fact_template' => $this->integer()->notNull(),
            'data_type' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_fact_template_slot_name', '{{%fact_template_slot}}', 'name');

        $this->addForeignKey("fact_template_slot_fact_template_fk", "{{%fact_template_slot}}",
            "fact_template", "{{%fact_template}}", "id", 'CASCADE');
        $this->addForeignKey("fact_template_slot_data_type_fk", "{{%fact_template_slot}}",
            "data_type", "{{%data_type}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%fact_template_slot}}');
    }
}