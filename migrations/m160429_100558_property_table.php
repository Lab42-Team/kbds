<?php

use yii\db\Migration;

class m160429_100558_property_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%property}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'ontology_class' => $this->integer()->notNull(),
            'data_type' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_property_name', '{{%property}}', 'name');

        $this->addForeignKey("property_ontology_class_fk", "{{%property}}",
            "ontology_class", "{{%ontology_class}}", "id", 'CASCADE');
        $this->addForeignKey("property_data_type_fk", "{{%property}}",
            "data_type", "{{%data_type}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%property}}');
    }
}