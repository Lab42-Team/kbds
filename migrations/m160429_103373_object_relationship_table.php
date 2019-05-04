<?php

use yii\db\Migration;

class m160429_103373_object_relationship_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%object_relationship}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string(),
            'description' => $this->string(),
            'relationship' => $this->integer()->notNull(),
            'object' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_object_relationship_name', '{{%object_relationship}}', 'name');

        $this->addForeignKey("object_relationship_relationship_fk", "{{%object_relationship}}",
            "relationship", "{{%relationship}}", "id", 'CASCADE');
        $this->addForeignKey("object_relationship_object_fk", "{{%object_relationship}}",
            "object", "{{%object}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%object_relationship}}');
    }
}