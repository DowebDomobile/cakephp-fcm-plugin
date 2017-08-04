<?php
/**
 * @copyright     Copyright (c) DowebDomobile (http://dowebdomobile.ru)
 */

namespace Dwdm\Fcm\Push;

use paragraph1\phpFCM\Message;

interface SystemMessageInterface
{
    /**
     * @param array $data
     * @return Message
     */
    public function create(array $data = []);
}