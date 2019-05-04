<?php

use yii\db\Migration;

class m160429_100001_relationship_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%relationship}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'is_association' => $this->boolean()->notNull()->defaultValue(false),
            'is_inheritance' => $this->boolean()->notNull()->defaultValue(false),
            'is_equivalence' => $this->boolean()->notNull()->defaultValue(false),
            'description' => $this->string(),
            'ontology' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_relationship_name', '{{%relationship}}', 'name');

        $this->addForeignKey("relationship_knowledge_base_fk", "{{%relationship}}",
            "ontology", "{{%knowledge_base}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%relationship}}');
    }
}