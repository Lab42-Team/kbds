<?php

use yii\db\Migration;

class m160909_093614_rule_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%rule}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'certainty_factor' => $this->double(),
            'salience' => $this->integer(),
            'description' => $this->string(),
            'production_model' => $this->integer()->notNull(),
            'rule_template' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_rule_name', '{{%rule}}', 'name');

        $this->addForeignKey("rule_knowledge_base_fk", "{{%rule}}",
            "production_model", "{{%knowledge_base}}", "id", 'CASCADE');
        $this->addForeignKey("rule_rule_template_fk", "{{%rule}}",
            "rule_template", "{{%rule_template}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%rule}}');
    }
}