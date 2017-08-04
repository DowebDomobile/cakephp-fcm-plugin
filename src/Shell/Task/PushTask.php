<?php
namespace Dwdm\Fcm\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Dwdm\Fcm\Model\Table\UserDevicesTable;
use Dwdm\Fcm\Push\Sender;

/**
 * Push shell task.
 */
class PushTask extends Shell
{
    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        return 0;
    }

    /**
     * Send push.
     *
     * @param Query $userDevices
     * @param array $options
     */
    public function send(Query $userDevices, array $options = [])
    {
        /** @var UserDevicesTable $Devices */
        $Devices = $this->loadModel('Dwdm/Fcm.UserDevices');
        $sender = new Sender($Devices, Configure::read('Push'));
        $sender->send($userDevices, $options);
    }
}
