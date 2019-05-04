<?php

use yii\db\Migration;

class m160429_095111_ontology_class_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%ontology_class}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'ontology' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_ontology_class_name', '{{%ontology_class}}', 'name');

        $this->addForeignKey("ontology_class_knowledge_base_fk", "{{%ontology_class}}",
            "ontology", "{{%knowledge_base}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%ontology_class}}');
    }
}