<?php

use yii\db\Migration;

class m160429_100435_object_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%object}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'ontology' => $this->integer()->notNull(),
            'ontology_class' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_object_name', '{{%object}}', 'name');

        $this->addForeignKey("object_knowledge_base_fk", "{{%object}}",
            "ontology", "{{%knowledge_base}}", "id", 'CASCADE');
        $this->addForeignKey("object_ontology_class_fk", "{{%object}}",
            "ontology_class", "{{%ontology_class}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%object}}');
    }
}