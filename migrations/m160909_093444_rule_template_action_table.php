<?php

use yii\db\Migration;

class m160909_093444_rule_template_action_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%rule_template_action}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'function' => $this->string(),
            'rule_template' => $this->integer()->notNull(),
            'fact_template' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("rule_template_action_rule_template_fk", "{{%rule_template_action}}",
            "rule_template", "{{%rule_template}}", "id", 'CASCADE');
        $this->addForeignKey("rule_template_action_fact_template_fk", "{{%rule_template_action}}",
            "fact_template", "{{%fact_template}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%rule_template_action}}');
    }
}