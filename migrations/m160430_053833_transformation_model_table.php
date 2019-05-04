<?php

use yii\db\Migration;

class m160430_053833_transformation_model_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%transformation_model}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'software_component' => $this->integer()->notNull(),
            'source_metamodel' => $this->integer()->notNull(),
            'target_metamodel' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_transformation_model_name', '{{%transformation_model}}', 'name');

        $this->addForeignKey("transformation_model_software_component_fk", "{{%transformation_model}}",
            "software_component", "{{%software_component}}", "id", 'CASCADE');
        $this->addForeignKey("transformation_model_source_metamodel_fk", "{{%transformation_model}}",
            "source_metamodel", "{{%metamodel}}", "id", 'CASCADE');
        $this->addForeignKey("transformation_model_target_metamodel_fk", "{{%transformation_model}}",
            "target_metamodel", "{{%metamodel}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%transformation_model}}');
    }
}