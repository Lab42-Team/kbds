<?php

use yii\db\Migration;

class m160408_103001_metareference_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%metareference}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'metarelation' => $this->integer()->notNull(),
            'left_metaattribute' => $this->integer()->notNull(),
            'right_metaattribute' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("metareference_metarelation_fk", "{{%metareference}}",
            "metarelation", "{{%metarelation}}", "id", 'CASCADE');
        $this->addForeignKey("metareference_left_metaattribute_fk", "{{%metareference}}",
            "left_metaattribute", "{{%metaattribute}}", "id", 'CASCADE');
        $this->addForeignKey("metareference_right_metaattribute_fk", "{{%metareference}}",
            "right_metaattribute", "{{%metaattribute}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%metareference}}');
    }
}