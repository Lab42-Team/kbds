<?php

use yii\db\Migration;

class m160408_101933_metaattribute_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%metaattribute}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->string(),
            'value' => $this->string(),
            'metaclass' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_metaattribute_name', '{{%metaattribute}}', 'name');

        $this->addForeignKey("metaattribute_metaclass_fk", "{{%metaattribute}}",
            "metaclass", "{{%metaclass}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%metaattribute}}');
    }
}