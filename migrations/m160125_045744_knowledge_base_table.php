<?php

use yii\db\Migration;

class m160125_045744_knowledge_base_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%knowledge_base}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'author' => $this->integer()->notNull(),
            'subject_domain' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_knowledge_base_name', '{{%knowledge_base}}', 'name');
        $this->createIndex('idx_knowledge_base_type', '{{%knowledge_base}}', 'type');
        $this->createIndex('idx_knowledge_base_status', '{{%knowledge_base}}', 'status');

        $this->addForeignKey("knowledge_base_user_fk", "{{%knowledge_base}}", "author", "{{%user}}", "id", 'RESTRICT');
        $this->addForeignKey("knowledge_base_subject_domain_fk", "{{%knowledge_base}}",
            "subject_domain", "{{%subject_domain}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%knowledge_base}}');
    }
}