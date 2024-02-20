<?php

namespace magicalella\connectife;

use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Connectife
 * Connectife component
 * @package magicalella\connectife
 *
 * @author Mariusz Stróż <info@inwave.pl>
 */
class Connectife extends Component
{

    /**
     * @var string Can be found under „Integration” menu of „Settings” section
     */
    public $clientId;

    /**
     * @var string Random pice of string
     */
    public $apiKey;

    /**
     * @var string Can be found under „Integration” section in application menu
     */
    public $apiSecret;

    /**
     * @var string
     */
    public $endpoint;
    
    /**
     * @var string
     */
    public $owner;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->clientId) {
            throw new InvalidConfigException('$clientId not set');
        }

        if (!$this->apiKey) {
            throw new InvalidConfigException('$apiKey not set');
        }

        if (!$this->apiSecret) {
            throw new InvalidConfigException('$apiSecret not set');
        }

        if (!$this->endpoint) {
            throw new InvalidConfigException('$endpoint not set');
        }
        
        if (!$this->owner) {
            throw new InvalidConfigException('$owner not set');
        }

        parent::init();
    }

    /**
     * Call SALESmanago function
     * @param string $call Name of API function to call
     * @param array $data
     * @return \stdClass Connectife response
     */
    public function call($call, $data)
    {
        $data = array_merge(
            array(
                'clientId' => $this->clientId,
                'apiKey' => $this->apiKey,
                'owner' => $this->owner,
                'requestTime' => time(),
                'sha' => sha1($this->apiKey . $this->clientId . $this->apiSecret),
            ),
            $data
        );
       
        $json = json_encode($data);
        //echo $json;
        
        $result = $this->curl($this->endpoint . '/api/' . $call, $json);
        return json_decode($result);
    }

    /**
     * Do request by CURL
     * @param $url
     * @param $data
     * @return mixed
     */
    private function curl($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json, application/json',
                'Content-Type: application/json;charset=UTF-8',
                //'Content-Length: ' . strlen($data)
            )
        );

        return curl_exec($ch);
    }


}
