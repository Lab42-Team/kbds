<?php

use yii\db\Migration;

class m160909_091439_fact_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%fact}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'initial' => $this->boolean()->notNull()->defaultValue(true),
            'certainty_factor' => $this->double(),
            'description' => $this->string(),
            'production_model' => $this->integer()->notNull(),
            'fact_template' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_fact_name', '{{%fact}}', 'name');

        $this->addForeignKey("fact_knowledge_base_fk", "{{%fact}}",
            "production_model", "{{%knowledge_base}}", "id", 'CASCADE');
        $this->addForeignKey("fact_fact_template_fk", "{{%fact}}",
            "fact_template", "{{%fact_template}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%fact}}');
    }
}