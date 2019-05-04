<?php

use yii\db\Migration;

class m160430_054602_transformation_body_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%transformation_body}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'transformation_rule' => $this->integer()->notNull(),
            'source_metaattribute' => $this->integer(),
            'target_metaattribute' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey("transformation_body_transformation_rule_fk", "{{%transformation_body}}",
            "transformation_rule", "{{%transformation_rule}}", "id", 'CASCADE');
        $this->addForeignKey("transformation_body_source_metaattribute_fk", "{{%transformation_body}}",
            "source_metaattribute", "{{%metaattribute}}", "id", 'CASCADE');
        $this->addForeignKey("transformation_body_target_metaattribute_fk", "{{%transformation_body}}",
            "target_metaattribute", "{{%metaattribute}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%transformation_body}}');
    }
}