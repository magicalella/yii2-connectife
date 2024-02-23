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
 * @author Raffaella Lollini
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
     * @return response []
     *      status 0/1 success o error
     *      data dati della risposta formato object
     *      error eventuali errori di chiamata 
     *      code codice della risposta es 200 o 404
     *      header in header 
     *              X-RateLimit-Limit: 100
     *              X-RateLimit-Remaining: 98
     *              X-RateLimit-Reset: 1580919168
     *              da considerare per gestire il limite delle chiamate
     */
    public function call($call, $method, $data = [])
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
        
        $response = $this->curl($this->endpoint.$call, $json, $method);
        $response['data'] = json_decode($response['data']);
        $response['header'] = $this->HeaderToArray($response['header']);
        return $response;
    }

    /**
     * Do request by CURL
     * @param $url ex: https://api.connectif.cloud/purchases/
     * @param $data
     * @param $method
     * @return response []
     *      status 0/1 success o error
     *      data dati della risposta formato json
     *      error eventuali errori di chiamata 
     *      code codice della risposta es 200 o 404
     *      header in header 
     * X-RateLimit-Limit: 100
     * X-RateLimit-Remaining: 98
     * X-RateLimit-Reset: 1580919168
     * da considerare per gestire il limite delle chiamate
     */
    private function curl($url, $data, $method = 'POST')
    {
        $response = [];
        $status = self::STATUS_SUCCESS;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json, application/json',
                'Content-Type: application/json;charset=UTF-8',
                'Authorization: apiKey '.$this->apiKey,
                'data-raw '.$data
                //'Content-Length: ' . strlen($data)
            )
        );
        $data = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        $error = curl_error($ch);
        if($error){
            $status = self::STATUS_ERROR;
        }

        $header_size = $curl_info['header_size'];
        $header = substr($data, 0, $header_size);
        $body = substr($data, $header_size);
        
        $response['status'] = $status;
        $response['data'] = $body;//dati
        $response['error'] = $error;//eventuali errori
        $response['code'] = $curl_info['http_code'];//codice restituito
        $response['header'] = $header;//header 
        
        curl_close($ch);
        
        return $response;
    }
    
    /**
    * Inutile tanto nel data non esiste code
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
        
        if(key_exists('code', $response)){
            if($response['code'] == '404'){
                $title = $array_response['title'];
                $detail = $array_response['detail'];
            }
        }
        $array_response = json_decode($response);
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

    /**
    *trasforma stringa HEADER del CURL in [] mappo solo i campi che mi servono :
    *              X-RateLimit-Limit: 100
    *              X-RateLimit-Remaining: 98
    *              X-RateLimit-Reset: 1580919168
    * 
    * @parmas stringa header 
    * return []
     */
    protected function HeaderToArray($header){
        $return = [];
        $array_header = explode("\n",$header);
        if(!empty($array_header)){
            foreach($array_header as $val){
                $array_val = explode(':',$val);

                if(!empty($array_val)){
                    switch($array_val[0]){
                        case 'X-RateLimit-Limit':
                        case 'X-RateLimit-Remaining':
                        case 'X-RateLimit-Reset':
                            $chiave = str_replace(['X','-'], '', $array_val[0]);
                            $return[$chiave] = $array_val[1];
                        break;
}
                }
            }
        }
        return $return;
    }


}

/**
 * @package BridgePS
 */
class ConnectifeException extends Exception
{
}
