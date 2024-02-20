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
     * @var string Random pice of string
     */
    public $apiKey;
    
    /**
     * @var string
     * https://api.connectif.cloud/
     */
    public $endpoint;
    
    /**
     * @var string metodo della chiamata POST - GET - DELETE - PATCH
     */
    public $method;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        

        if (!$this->apiKey) {
            throw new InvalidConfigException('$apiKey not set');
        }

        if (!$this->endpoint) {
            throw new InvalidConfigException('$endpoint not set');
        }
        
        if (!$this->method) {
            throw new InvalidConfigException('$owner not set');
        }

        parent::init();
    }

    /**
     * Call Connectife function
     * @param string $call Name of API function to call
     * @param array $data
     * @return \stdClass Connectife response
     */
    public function call($call, $data)
    {
        // $data = array_merge(
        //     array(
        //         'apiKey' => $this->apiKey,
        //         'owner' => $this->owner,
        //         'requestTime' => time(),
        //         'sha' => sha1($this->apiKey . $this->clientId . $this->apiSecret),
        //     ),
        //     $data
        // );
       
        $json = json_encode($data);
        //echo $json;
        
        $result = $this->curl($this->endpoint.$call, $json, $method);
        return json_decode($result);
    }

    /**
     * Do request by CURL
     * @param $url ex: https://api.connectif.cloud/purchases/
     * @param $data
     * @param $method
     * @return mixed
     */
    private function curl($url, $data, $method = 'POST')
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json, application/json',
                'Content-Type: application/json;charset=UTF-8',
                'Authorization: apiKey '.$this->apiKey,
                'data-raw '.$data
                //'Content-Length: ' . strlen($data)
            )
        );

        return curl_exec($ch);
    }


}
