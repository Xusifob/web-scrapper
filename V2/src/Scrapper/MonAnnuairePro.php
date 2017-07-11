<?php

namespace Extractor\Scrapper;


include __DIR__ . '/../header.php';


use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Scrapper
 *
 * Class MonAnnuairePro
 *
 */
class MonAnnuairePro extends Scrapper implements ScrapperInterface
{


    /**this-
     * @var array
     */
    protected $company = array();





    /** @inheritdoc */
    public function scrapFiche($url)
    {


        $response = $this->getHtml($url);



        if($response instanceof JsonResponse){
            $response->send();
            die();
        }




        $this->company  = array();


        if(!isset($response->find('.company__info strong')[0])){
            return;
        }

        $this->company['company']  = $this->getContent($response->find('.company__info strong'));
        $this->company['address']  = $this->getContent($response->find('.company__info .icon-marker'));
        $this->company['phone']  = $this->getContent($response->find('.company__info .icon-phone'));



        $url = $this->prefix . $this->getContent($response->find('.button_voir_site a')[0],'href');

        try{

            $this->client->get($url,array(
                'connect_timeout' => 10,
                'read_timeout' => 10,
                'timeout' => 15,
                'headers' => array(
                    'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                ),
                'on_stats' => function (\GuzzleHttp\TransferStats $stats) {
                    if(!empty($stats->getHandlerStats()['redirect_url'])){
                        $this->company['website'] = $stats->getHandlerStats()['redirect_url'];
                    }

                }
            ));


            vardump($this->company);

            $this->save($this->company);


        }
        catch(ConnectException $e){
            return;
        } catch(InvalidArgumentException $e){
            return;
        }
        catch(RequestException $e){
            return;
        }




    }


}



$scrapper = new MonAnnuairePro();

$scrapper->setHasPages(true);
$scrapper->setStart(60);
$scrapper->setStop(70);

$scrapper->setUrl('http://www.mon-annuaire-pro.com/result/page-%page%?q=&s=&sn=&a=&o=&pt=salon&p=heavent');

$scrapper->setSelectors(array(
    'list' => '.single-result',
));



//$scrapper->parse();
$scrapper->export();