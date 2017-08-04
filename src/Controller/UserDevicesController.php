<?php
/**
 * @copyright     Copyright (c) DowebDomobile (http://dowebdomobile.ru)
 */

namespace Dwdm\Fcm\Controller;

use App\Controller\AppController as BaseController;
use Cake\Controller\Exception\MissingComponentException;
use Dwdm\Fcm\Model\Table\UserDevicesTable;

/**
 * Class UserDevicesController
 * @package Dwdm\Fcm\Controller
 *
 * @property UserDevicesTable $UserDevices
 */
class UserDevicesController extends BaseController
{
    use UserDeviceAddActionTrait;

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        parent::initialize();

        if (!$this->components()->has('Auth')) {
            throw new MissingComponentException(['Auth']);
        }
    }
}