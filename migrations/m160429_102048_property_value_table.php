<?php

use yii\db\Migration;

class m160429_102048_property_value_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%property_value}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string(),
            'property' => $this->integer()->notNull(),
            'object' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_property_value_name', '{{%property_value}}', 'name');

        $this->addForeignKey("property_value_property_fk", "{{%property_value}}",
            "property", "{{%property}}", "id", 'CASCADE');
        $this->addForeignKey("property_value_object_fk", "{{%property_value}}",
            "object", "{{%object}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%property_value}}');
    }
}