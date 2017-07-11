<?php


namespace Extractor\Service;

use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;


/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * This class is doing a Request and returning an HTML DOM Element.
 *
 * It also handle proxies
 *
 * Class RequestDoer
 */
abstract class RequestDoer
{


    /**
     * @var string
     */
    protected $proxy;



    /**
     * @var array
     */
    protected $proxies = array();


    /**
     * @var Client
     */
    protected $client;


    /**
     * @var string
     */
    protected $prefix;




    /**
     * RequestDoer constructor.
     */
    public function __construct()
    {

        $this->proxies = @json_decode(file_get_contents(MAIN_DIR . 'config/proxies.json'),true);

    }


    /**
     * @param $url
     * @param array $params
     * @param bool $use_cookies
     * @return string|JsonResponse
     */
    public function do_request($url,$params = array(),$use_cookies = true)
    {

        if(!preg_match('/https?:\/\//',$url)){
            $url = $this->prefix . $url;
        }

        try {
            if(!$this->client){
                $this->client = new Client(['cookies' => $use_cookies]);
            }


            $response = $this->client->get($url,array_merge(array(
                'connect_timeout' => 10,
                'read_timeout' => 10,
                'timeout' => 15,
                'headers' => array(
                    'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                )
            ),$params));



            return $response->getBody()->getContents();


        }catch (\GuzzleHttp\Exception\ClientException $e){
            return new JsonResponse(array(
                    'error_key' => 'result',
                    'error' =>$e->getMessage())
                ,404);
        }catch (\GuzzleHttp\Exception\ConnectException $e){
            return new JsonResponse(array(
                    'error_key' => 'result',
                    'error' =>$e->getMessage())
                ,404);
        }catch (\GuzzleHttp\Exception\RequestException $e){
            return new JsonResponse(array(
                    'error_key' => 'result',
                    'error' =>$e->getMessage())
                ,404);
        }
    }


    /**
     *
     * Return the HTML
     *
     * @param $url
     * @param array $params
     * @param boolean $use_cookies
     * @return \simple_html_dom|JsonResponse
     */
    protected function getHtml($url,$params = array(),$use_cookies = true)
    {
        $response = $this->do_request($url,$params);

        if($response instanceof JsonResponse){
            return $response;
        }

        $dom = new \simple_html_dom();

        return $dom->load($response);
    }


    /**
     * @param $url
     * @return mixed
     */
    public function removeProxy($url)
    {
        return str_replace($this->getProxy(),'',$url);
    }




    /**
     *
     * Return one proxy from the list of proxies
     *
     * @return string
     */
    public function getProxy()
    {

        if(!$this->proxy){

            if(count($this->proxies) == 0 ){
                return '';
            }


            $proxies = $this->proxies;

            $proxy = $proxies[rand(0,count($this->proxies)-1)];
            $this->proxy = $proxy;
        }


        return $this->proxy;

    }


}