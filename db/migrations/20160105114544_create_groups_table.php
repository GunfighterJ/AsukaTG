<?php

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\AbstractMigration;

class CreateGroupsTable extends AbstractMigration
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
        $table = $this->table('groups', ['id' => false, 'primary_key' => ['group_id']]);
        $table
            ->addColumn('group_id', AdapterInterface::PHINX_TYPE_INTEGER, [
                'null' => false,
                'limit' => 64,
                'signed' => false
            ])
            ->addColumn('title', AdapterInterface::PHINX_TYPE_STRING, [
                'null' => false
            ])
            ->addIndex(['group_id'], ['unique' => true])
            ->create();
    }
}
