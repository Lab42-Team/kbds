<?php

use yii\db\Migration;

class m160430_054302_transformation_rule_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%transformation_rule}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'transformation_model' => $this->integer()->notNull(),
            'source_metaclass' => $this->integer()->notNull(),
            'target_metaclass' => $this->integer()->notNull(),
            'priority' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("transformation_rule_transformation_model_fk", "{{%transformation_rule}}",
            "transformation_model", "{{%transformation_model}}", "id", 'CASCADE');
        $this->addForeignKey("transformation_rule_source_metaclass_fk", "{{%transformation_rule}}",
            "source_metaclass", "{{%metaclass}}", "id", 'CASCADE');
        $this->addForeignKey("transformation_rule_target_metaclass_fk", "{{%transformation_rule}}",
            "target_metaclass", "{{%metaclass}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%transformation_rule}}');
    }
}