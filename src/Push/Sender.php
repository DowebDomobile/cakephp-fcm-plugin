<?php
/**
 * @copyright     Copyright (c) DowebDomobile (http://dowebdomobile.ru)
 */

namespace Dwdm\Fcm\Push;

use Cake\Core\App;
use Cake\Core\InstanceConfigTrait;
use Cake\Log\LogTrait;
use Cake\ORM\Query;
use Cake\Utility\Inflector;
use Dwdm\Fcm\Model\Entity\UserDevice;
use Dwdm\Fcm\Model\Table\UserDevicesTable;
use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Recipient\Device;
use Psr\Log\LogLevel;
use GuzzleHttp\Client as HttpClient;

/**
 * Class Sender
 * @package Dwdm\Fcm\Push
 */
class Sender
{
    use LogTrait;
    use InstanceConfigTrait;

    /** @var array */
    protected $_defaultConfig = [];

    /** @var UserDevicesTable */
    protected $Devices;

    /** @var Client */
    protected $client;

    public function __construct(UserDevicesTable $Devices, array $config)
    {
        $this->setConfig($config);
        $this->Devices = $Devices;

        $this->client = new Client();
        $this->client->injectHttpClient($this->_getHttpClient());
        $this->client->setApiKey($this->getConfig('apiKey'));
    }

    public function send(Query $userDevices, array $options = [])
    {
        $options += ['message' => [], 'strategy' => 'single'];

        $this->{'_strategy' . ucfirst($options['strategy'])}($userDevices, $options);
    }

    /**
     * Prepare and send single message for many recipients
     *
     * @param Query $userDevices
     * @param array $options
     */
    protected function _strategySingle(Query $userDevices, array $options = [])
    {
        $systems = $this->Devices->find('systemList')->all();

        foreach ($systems as $system) {
            if (!($messageBuilder = $this->_getMessageBuilder($system, '', $options))) {
                $this->log(__d('fcm-debug', 'No message builders found for {0}.', [ucfirst($system)]), LogLevel::DEBUG);

                continue;
            }

            $devices = clone $userDevices;
            $devices->where(['UserDevices.system IN' => [strtolower($system), ucfirst($system)]]);

            if ($devices->count() == 0) {
                $this->log(__d('fcm-debug', 'No recipients found for {0}.', [ucfirst($system)]), LogLevel::DEBUG);
                continue;
            }

            /** @var Message $message */
            $message = $messageBuilder->create($options['message']);

            $tokens = [];
            /** @var UserDevice $userDevice */
            foreach ($devices as $userDevice) {
                $tokens[] = $userDevice->token;
                $message->addRecipient(new Device($userDevice->token));
            }

            $this->_send($message, $tokens);
        }
    }

    protected function _strategyIndividual(Query $userDevices, array $options = [])
    {
        /** @var UserDevice $userDevice */
        foreach ($userDevices as $userDevice) {
            if ($messageBuilder = $this->_getMessageBuilder($userDevice->system, $userDevice->version, $options))
            {
                $options['message']['userDevice'] = $userDevice;
                $message = $messageBuilder->create($options['message'])
                    ->addRecipient(new Device($userDevice->token));
                $this->_send($message, [$userDevice->token]);
            }
        }
    }

    /**
     * @param string $system
     * @param string $version
     * @param array $options
     * @return SystemMessageInterface|false
     * @internal param UserDevice $userDevice
     */
    protected function _getMessageBuilder($system, $version, array $options = [])
    {
        $version = empty($version) ? '' : '/V' . Inflector::delimit(ltrim($version, 'vV'));
        $class = App::className(ucfirst($options['message']['type']), 'Push/' . ucfirst($system) . $version, 'Message');

        if (!$class) {
            $class = App::className(ucfirst($options['message']['type']), 'Push/' . ucfirst($system), 'Message');
        }

        return $class ? new $class : false;
    }

    protected function _send(Message $message, array $tokens = [])
    {
        $this->log(__d('fcm-debug', 'Send messages to: {0}', [implode(', ', $tokens)]), LogLevel::DEBUG);
        $this->log(__d('fcm-debug', 'Send message: {0}', [json_encode($message->jsonSerialize())]), LogLevel::DEBUG);

        $response = $this->client->send($message);

        $this->log(__d('fcm-debug', 'Send status: {0}', [$response->getStatusCode()]), LogLevel::DEBUG);
        if ($response->getStatusCode() == 200) {
            $payload = json_decode($content = $response->getBody()->getContents());
            $this->log(__d('fcm-debug', 'FCM response: {0}', [$content]), LogLevel::DEBUG);
            if ($payload->failure > 0) {
                $expiredTokens = [];
                foreach ($payload->results as $key => $result) {
                    if (isset($result->error)) {
                        $expiredTokens[] = $tokens[$key];
                    }
                }

                $this->log(__d('fcm-debug', 'Expired tokens: {0}', [implode(', ', $expiredTokens)]), LogLevel::DEBUG);
                $this->Devices->deleteAll(['token IN' => $expiredTokens]);
            }
        }
    }

    /**
     * @return HttpClient|mixed
     */
    protected function _getHttpClient()
    {
        return $this->getConfig('client') instanceof HttpClient
            ? $this->getConfig('client') : new HttpClient(['http_errors' => false]);
    }
}