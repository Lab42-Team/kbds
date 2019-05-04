<?php

use yii\db\Migration;

class m161026_072956_rule_condition_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%rule_condition}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'operator' => $this->string(),
            'rule' => $this->integer()->notNull(),
            'fact' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("rule_condition_rule_fk", "{{%rule_condition}}", "rule", "{{%rule}}", "id", 'CASCADE');
        $this->addForeignKey("rule_condition_fact_fk", "{{%rule_condition}}", "fact", "{{%fact}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%rule_condition}}');
    }
}