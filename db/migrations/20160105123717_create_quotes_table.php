<?php

use Phinx\Db\Adapter\AdapterInterface;
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
            ->addColumn('content', AdapterInterface::PHINX_TYPE_TEXT, [
                'null' => false
            ])
            ->addColumn('chat_id', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'signed' => false,
                'limit' => 64
            ])
            ->addColumn('message_id', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'limit' => 64
            ])
            ->addColumn('user_id', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'limit' => 64
            ])
            ->addColumn('addedby_id', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'limit' => 64
            ])
            ->addColumn('message_timestamp', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'limit' => 64
            ])
            ->addColumn('added_timestamp', AdapterInterface::PHINX_TYPE_TIMESTAMP, [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP'
            ])
            ->addIndex(['message_id'], ['unique' => true])
            ->addForeignKey('user_id', 'users', 'user_id', ['delete' => 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
