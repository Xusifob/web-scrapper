<?php

namespace Extractor\Scrapper;



include_once __DIR__ . '/../header.php';

use Extractor\Service\Utils;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Scrapper
 *
 * Class Capital
 *
 */
class StartupPlace extends Scrapper implements ScrapperInterface
{



    public $prefix  = 'http://www.dogfinance.com';


    // 356



    public function __construct()
    {

        parent::__construct();

    }


    /** @inheritdoc */
    public function parse()
    {
        $response = $this->getHtml($this->url,array(
            'headers' => array(
                'Origin' =>'https://www.startupplace.io',
                'X-Requested-With' =>'XMLHttpRequest',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            )
        ));


        if($response instanceof JsonResponse){
            $response->send();
            return;
        }


        $nonce = ($response->find('#startup_search_nonce')[0]->getAttribute('value'));

        $client = new Client();


        for($i =20;$i<30;$i++ ){

            $response = $client->post('https://www.startupplace.io/wp-admin/admin-ajax.php',array(
                'connect_timeout' => 10,
                'read_timeout' => 10,
                'timeout' => 15,
                'form_params' => array(
                    'action' => 'search_startups',
                    'nonce' => $nonce,
                    'search_params' => array(
                        'paged' => $i,
                        'market' => array(
                            80
                        ),
                        'country' => array(
                            'France'
                        ),
                    )
                ),

                'headers' => array(
                    'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                    'Origin' =>'https://www.startupplace.io',
                    'X-Requested-With' =>'XMLHttpRequest',
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                )
            ))->getBody()->getContents();


            $dom = new \simple_html_dom();

            $data = json_decode($response,true)['data'];

            $html = $dom->load($data);



            vardump($response);


            foreach($html->find('.item-startup') as $item)
            {
                $this->scrapFiche($item->find('a')[0]->getAttribute('href'));
            }
        }

        die();

    }




    /** @inheritdoc */
    public function scrapFiche($url)
    {



        $response = $this->getHtml($url);

        $company  = array();

        $company['company'] = $this->getContent($response->find('.startup-name')[0]);


        /** @var \simple_html_dom_node $row */
        foreach($response->find('.infos p') as $row)
        {

            $content = $this->getContent($row);


            if($row->find('.fa-dollar')){
                $company['turnover'] = $content;
            }
            if($row->find('.fa-link')){
                $company['website'] = $content;
            }

            if($row->find('.fa-phone')){
                $company['phone'] = $content;
            }

            if($row->find('.fa-map-marker')){
                $company['adress'] = $content;
            }


        }

        vardump($company);

        $this->save($company);

    }



}

$scrapper = new StartupPlace();

$scrapper->setUrl('https://www.startupplace.io/search-startup/');

//$scrapper->parse();



$scrapper->export();