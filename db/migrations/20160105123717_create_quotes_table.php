<?php

use Phinx\Migration\AbstractMigration;

class CreateQuotesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('quotes');
        $table
            ->addColumn('content', 'text', ['null' => false])
            ->addColumn('citation', 'text', ['null' => false])
            ->addColumn('source', 'text', ['null' => false, 'default' => 'Telegram'])
            ->addColumn('message_id', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('message_user_id', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('addedby_user_id', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('message_timestamp', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('added_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['content', 'telegram_message_id'], ['unique' => true])
            ->create();
    }
}
