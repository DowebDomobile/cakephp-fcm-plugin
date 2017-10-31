<?php
/**
 * @copyright     Copyright (c) DowebDomobile (http://dowebdomobile.ru)
 */

namespace Dwdm\Fcm\Controller;

use Cake\Controller\Component\AuthComponent;
use Cake\Network\Request;
use Dwdm\Fcm\Model\Entity\UserDevice;
use Dwdm\Fcm\Model\Table\UserDevicesTable;

/**
 * Trait UserDeviceAddActionTrait
 * @package Dwdm\Fcm\Controller
 *
 * @property Request $request
 * @property UserDevicesTable $UserDevices
 * @property AuthComponent $Auth
 *
 * @method self set($name, $value = null)
 */
trait UserDeviceAddActionTrait
{
    /**
     * Add device token if it is new
     */
    public function add()
    {
        $this->request->allowMethod('post');

        $userDevice = $this->UserDevices->findOrCreate(
            $this->request->getData(),
            function (UserDevice $entity) {
                $entity->user_id = $this->Auth->user('id');
                return $entity;
            }
        );
        $userDevice->user_id = $this->Auth->user('id');
        $this->UserDevices->save($userDevice);

        $errors = $userDevice->getErrors();
        $message = empty($errors)
            ? __d('fcm', 'Token for push messages was added.') : __d('fcm', 'Token adding fail.');

        $this->set(compact('errors', 'message'));
    }
}