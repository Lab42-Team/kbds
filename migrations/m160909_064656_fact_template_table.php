<?php

use yii\db\Migration;

class m160909_064656_fact_template_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%fact_template}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'production_model' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_fact_template_name', '{{%fact_template}}', 'name');

        $this->addForeignKey("fact_template_knowledge_base_fk", "{{%fact_template}}",
            "production_model", "{{%knowledge_base}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%fact_template}}');
    }
}