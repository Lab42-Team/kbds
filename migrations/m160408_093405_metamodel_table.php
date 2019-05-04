<?php

use yii\db\Migration;

class m160408_093405_metamodel_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%metamodel}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'author' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_metamodel_name', '{{%metamodel}}', 'name');
        $this->createIndex('idx_metamodel_type', '{{%metamodel}}', 'type');

        $this->addForeignKey("metamodel_user_fk", "{{%metamodel}}", "author", "{{%user}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%metamodel}}');
    }
}