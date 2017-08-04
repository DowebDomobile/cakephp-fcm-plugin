<?php
use Migrations\AbstractMigration;

class CreateUserDevices extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('user_devices')
            ->addColumn('user_id', 'integer')
            ->addColumn('token', 'string', ['length' => 4096])
            ->addColumn('system', 'string', ['length' => 50])
            ->addColumn('version', 'string', ['length' => 10])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
