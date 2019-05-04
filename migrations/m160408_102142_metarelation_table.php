<?php

use yii\db\Migration;

class m160408_102142_metarelation_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%metarelation}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'metamodel' => $this->integer()->notNull(),
            'left_metaclass' => $this->integer()->notNull(),
            'right_metaclass' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_metarelation_name', '{{%metarelation}}', 'name');

        $this->addForeignKey("metarelation_metamodel_fk", "{{%metarelation}}",
            "metamodel", "{{%metamodel}}", "id", 'CASCADE');
        $this->addForeignKey("metarelation_left_metaclass_fk", "{{%metarelation}}",
            "left_metaclass", "{{%metaclass}}", "id", 'CASCADE');
        $this->addForeignKey("metarelation_right_metaclass_fk", "{{%metarelation}}",
            "right_metaclass", "{{%metaclass}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%metarelation}}');
    }
}