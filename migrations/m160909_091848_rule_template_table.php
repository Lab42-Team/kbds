<?php

use yii\db\Migration;

class m160909_091848_rule_template_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%rule_template}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'salience' => $this->integer(),
            'description' => $this->string(),
            'production_model' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_rule_template_name', '{{%rule_template}}', 'name');

        $this->addForeignKey("rule_template_knowledge_base_fk", "{{%rule_template}}",
            "production_model", "{{%knowledge_base}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%rule_template}}');
    }
}