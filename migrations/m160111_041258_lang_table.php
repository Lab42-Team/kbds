<?php

use yii\db\Migration;

class m160111_041258_lang_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%lang}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'url' => $this->string()->notNull(),
            'local' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'default' => $this->smallInteger(6)->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('idx_lang_url', '{{%lang}}', 'url');
        $this->createIndex('idx_lang_name', '{{%lang}}', 'name');
    }

    public function down()
    {
        $this->dropTable('{{%lang}}');
    }
}