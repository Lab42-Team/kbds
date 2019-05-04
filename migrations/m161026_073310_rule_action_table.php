<?php

use yii\db\Migration;

class m161026_073310_rule_action_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%rule_action}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'function' => $this->string(),
            'rule' => $this->integer()->notNull(),
            'fact' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("rule_action_rule_fk", "{{%rule_action}}", "rule", "{{%rule}}", "id", 'CASCADE');
        $this->addForeignKey("rule_action_fact_fk", "{{%rule_action}}", "fact", "{{%fact}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%rule_action}}');
    }
}