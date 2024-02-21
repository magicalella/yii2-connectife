<?php

namespace magicalella\connectife;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Exception;

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
    
    const STATUS_SUCCESS = true;
    const STATUS_ERROR = false;


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
     * in header 
     * X-RateLimit-Limit: 100
     * X-RateLimit-Remaining: 98
     * X-RateLimit-Reset: 1580919168
     * da considerare per gestire il limite delle chiamate
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
    
    /**
     * Take the status code and throw an exception if the server didn't return 200 or 201 code
     * <p>Unique parameter must take : <br><br>
     * 'status_code' => Status code of an HTTP return<br>
     * 'response' => CURL response
     {
         status: 400,
         title: 'Bad Request',
         detail: 'Product 5cf8c05f5b4cb901091f7d98, 5cf8c05f5b4cb901091f7d99, 5cf8c05f5b4cb901091f7d9a had URL from non authorized domain.',
         code: 'E0103',
         productsId: [ '5cf8c05f5b4cb901091f7d98', '5cf8c05f5b4cb901091f7d99', '5cf8c05f5b4cb901091f7d9a' ],
         allowedDomains: [ 'http://www.mysite.com' ]
     }
     * </p>
     *
     * @param array $request Response elements of CURL request
     *
     * @throws ConnectifeException if HTTP status code is not 200 or 201
     */
    protected function checkStatusCode($response)
    {
        $error_message = '';
        $title = '';
        $detail = '';
        $array_response = json_decode($response);
        if(key_exists('status', $array_response)){
            if($array_response['status'] == '400'){
                $title = $array_response['title'];
                $detail = $array_response['detail'];
            }
        }
        switch ($array_response['code']) {
            case '200':
            case '201':
                break;
            case 'E0101':
            case 'E0102':
            case 'E0103':
            case 'E0201':
            case 'E0401':
                $error_message = 'Codice: '.$array_response['code'].' '.$title.' '.$detail;
            break;
            default:
                throw new ConnectifeException(
                    'This call to Connectife Web Services returned an unexpected HTTP status of:' . $array_response['status']
                );
        }
    
        if ($error_message != '') {
            $error_label = 'This call to Connectife failed and returned an HTTP status of %d. That means: %s.';
            throw new ConnectifeException(sprintf($error_label, $request['status_code'], $error_message));
        }
    }


}

/**
 * @package BridgePS
 */
class ConnectifeException extends Exception
{
}
