<?php


namespace Extractor\Service;

use Extractor\Exception\JsonEncodedException;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Exception\ServerException;



/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * This class handles all the calls on Hunter.io
 *
 * Class Hunter
 */
class Hunter
{



    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;


    /**
     *
     * The Hunter.io API Key
     *
     * @var string
     */
    protected $api_key;


    /**
     *
     * The File where the Hunter API Keys are stored
     *
     * @var string
     */
    protected $api_key_file  = MAIN_DIR .  'config/hunter_apis.json';



    /**
     * Hunter constructor.
     */
    public function __construct()
    {
        // Get email data
        $this->client = new Client(array(
            'base_uri' => 'https://api.emailhunter.co/v1/'
        ));

    }


    /**
     *
     * return a valid API Key from the listing
     *
     * @return string
     * @throws JsonEncodedException
     */
    public function getApiKey()
    {


        if($this->api_key){
            return $this->api_key;
        }

        $keys = $this->getApiKeys();

        foreach($keys as $key => $value){
            if(!$value['is_finished'] || strtotime($value['starting']) < time()){
                $this->api_key = $key;
                break;
            }
        }


        if($this->api_key && isset($key)){
            return $key;
        }


        throw new JsonEncodedException(array(
            'error_key' => 'quota',
            'error' => 'No usable API Key found for Hunter.io',
            'key' => $this->api_key,
            'code' => 429,
        ));
    }




    /**
     *
     * Add a new API Key into the file
     *
     * @param string $api_key
     * @param string $email
     * @throws JsonEncodedException
     */
    public function setApiKey($api_key,$email = '')
    {


        if(!$api_key){
            return;
        }

        $keys = $this->getApiKeys();


        // Do not add a key that already exists
        if(array_key_exists($api_key,$keys)){
            return;
        }


        $key = array(
            "api_key" => $api_key,
            "is_finished" => false,
            "starting" => date('d-m-Y',strtotime('+ 1 month')),
            "email" => $email,
        );


        $keys[$api_key] = $key;


        $file = file_put_contents($this->api_key_file,json_encode($keys,JSON_PRETTY_PRINT));


        $this->api_key = $api_key;


        if(false === $file){
            throw new JsonEncodedException(array(
                'error_key' => 'server',
                'error' => "File $this->api_key_file impossible to write",
                'code' => 500
            ));
        }
    }





    /**
     *
     * Return the list of all api keys
     *
     * @return array
     * @throws JsonEncodedException
     */
    public function getApiKeys()
    {
        $file = file_get_contents($this->api_key_file);


        if(false === $file){
            throw new JsonEncodedException(array(
                'error_key' => 'server',
                'error' => "File $this->api_key_file impossible to read",
                'code' => 500
            ));

        }

        return json_decode($file,true);

    }


    /**
     *
     * Find one email on Hunter.io
     *
     * @param $first_name
     * @param $last_name
     * @param $website
     *
     * @return array
     *
     * @throws JsonEncodedException
     */

    public function findEmail($first_name,$last_name,$website)
    {


        try {
            $api_key = $this->getApiKey();


            $request = $this->client->request('GET', 'generate', array(
                'query' => array(
                    'api_key' => $api_key,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'domain' => $website,
                ),
            ));


            $response = json_decode($request->getBody(), true);


            // Handle response
            if (!isset($response['email']) || !$response['email']) {
                throw new JsonEncodedException(array(
                    'error_key' => 'email',
                    'error' => "No email found for {$first_name} {$last_name} on {$website} ",
                    'code' => 404
                ));

            }



            if($response['score'] < 70){

                throw new JsonEncodedException(array(
                    'error_key' => 'email',
                    'error' => "Email found {$response['email']} but score only {$response['score']}",
                    'code' => 404,
                ));
            }


            return $response;


        }catch (ServerException $e){

            throw new JsonEncodedException(array(
                'error_key' => 'server',
                'error' => $e->getMessage(),
                'code' => $e->getResponse()->getStatusCode(),
            ));



        }catch (ClientException $e){

            if(in_array($e->getResponse()->getStatusCode(),array(429,401))){
                $this->setApikeyUsed();
                return $this->findEmail($first_name,$last_name,$website);
            }else {
                throw new JsonEncodedException(array(
                    'error_key' => 'server',
                    'error' => $e->getMessage(),
                    'code' => $e->getResponse()->getStatusCode(),
                ));
            }

        }
    }



    /**
     *
     * Add a new api key in the listing
     *
     *
     * @param null|string $api_key
     * @throws JsonEncodedException
     */
    public function setApiKeyUsed($api_key = null)
    {
        if(!$api_key){
            $api_key = $this->api_key;
        }

        if(!$api_key || empty($api_key)){
            return;
        }

        $keys = $this->getApiKeys();

        if(!is_array($keys)){
            throw new JsonEncodedException(array(
                'error_key' => 'server',
                'error' => "impossible to set  $api_key used, no keys found",
                'data' => $keys,
                'code' => 500
            ));
        }

        $date = date('d-',strtotime($keys[$api_key]['starting'])) . date('m-Y');

        $date = date('d-m-Y',strtotime($date . ' + 1 month'));


        $keys[$api_key] = array(
            'api_key' => $api_key,
            'is_finished' => true,
            'starting' => $date,
            'email' => isset($keys[$api_key]['email']) ? $keys[$api_key]['email'] : '',
        );

        $file = file_put_contents($this->api_key_file,json_encode($keys,JSON_PRETTY_PRINT));



        if(false === $file){
            throw new JsonEncodedException(array(
                'error_key' => 'server',
                'error' => "File $this->api_key_file impossible to write",
                'code' => 500
            ));
        }

    }



}