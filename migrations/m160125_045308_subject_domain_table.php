<?php

use yii\db\Migration;

class m160125_045308_subject_domain_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%subject_domain}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'author' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_subject_domain_name', '{{%subject_domain}}', 'name');

        $this->addForeignKey("subject_domain_user_fk", "{{%subject_domain}}", "author", "{{%user}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%subject_domain}}');
    }
}