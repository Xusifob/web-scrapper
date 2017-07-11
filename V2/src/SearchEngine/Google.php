<?php

namespace Extractor\SearchEngine;


use Serps\Core\Browser\Browser;
use Serps\Core\Http\Proxy;
use Serps\HttpClient\CurlClient;
use Serps\SearchEngine\Google\GoogleClient;
use Serps\SearchEngine\Google\GoogleUrl;
use Symfony\Component\HttpFoundation\JsonResponse;



/**
 *
 * This class is to do a search on bing
 *
 * Class Google
 */
class Google extends SearchEngine
{


    const url = 'google.fr';





    /**
     *
     * Do a Raw Search | return Google Response
     *
     * @param $query
     * @return \Serps\Core\Serp\IndexedResultSet|JsonResponse
     */
    public function rawSearch($query)
    {


        try {

            $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2214.93 Safari/537.36";
            $browserLanguage = "fr-FR";


            //$p = $this->proxies[rand(0,count($this->proxies)-1)];
            //$proxy = new Proxy('http://projets.bastienmalahieude.fr','80/miniProxy.php?');

            $browser = new Browser(new CurlClient(), $userAgent, $browserLanguage,null);

            $googleClient = new GoogleClient($browser);

            $googleUrl = new GoogleUrl(self::url);
            $googleUrl->setSearchTerm($query);


            $googleSerp = $googleClient->query($googleUrl);

            return $googleSerp->getNaturalResults();


        } catch (\Serps\Exception\RequestError\CaptchaException $e) {
            return new JsonResponse(array(
                'error_key' => 'captcha',
                'error' => "Extraction failed for {$this->query}, captcha issue",
                'captcha' => $e->getCaptcha()->getErrorPage()->getUrl()->__toString(),

            ),404);

        } catch (\Serps\Exception\RequestError\RequestErrorException $e) {
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$this->query}, no google results"
            ),404);
        }

        catch (\Symfony\Component\Process\Exception\ProcessFailedException $e){
            return new JsonResponse(array(
                'error_key' => 'error',
                'error' => $e->getMessage(),
                'process' => $e->getProcess()
            ),500);
        }
        catch (\Exception $e){
            return new JsonResponse(array(
                'error_key' => 'error',
                'error' => $e->getMessage(),
            ),500);
        }


    }




}