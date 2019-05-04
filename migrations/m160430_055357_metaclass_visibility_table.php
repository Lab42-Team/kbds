<?php

use yii\db\Migration;

class m160430_055357_metaclass_visibility_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%metaclass_visibility}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'transformation_model' => $this->integer()->notNull(),
            'metaclass' => $this->integer()->notNull(),
            'visibility' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);

        $this->addForeignKey("metaclass_visibility_transformation_model_fk", "{{%metaclass_visibility}}",
            "transformation_model", "{{%transformation_model}}", "id", 'CASCADE');
        $this->addForeignKey("metaclass_visibility_metaclass_fk", "{{%metaclass_visibility}}",
            "metaclass", "{{%metaclass}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%metaclass_visibility}}');
    }
}