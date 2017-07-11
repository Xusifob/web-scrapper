<?php

namespace Extractor\Scrapper;


include __DIR__ . '/../header.php';


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
class BonjourIdee extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'https://bonjouridee.com/startups/outilsbtob-servicesauxentreprises/page/';






    /** @inheritdoc */
    public function parse()
    {

        for($i = 40;$i<46;$i++){

            $response = $this->getHtml($this->url . $i);


            if($response  instanceof JsonResponse){
                $response->send();
                die();
            }


            $list = $response->find('.content article');


            /** @var \simple_html_dom_node $elem */
            foreach($list as $key => $elem)
            {

                $this->scrapFiche($elem->find('h2 a')[0]->getAttribute('href'));

            }



        }
    }




    /** @inheritdoc */
    public function scrapFiche($url)
    {


        $response = $this->getHtml($url);


        if($response instanceof JsonResponse){
            return;
        }



        $subline = preg_split('/(,|:|-)/',$response->find('h1.post-title')[0]->innertext);


        $company  = array();

        $company['company']  = html_entity_decode(trim($subline[0]));
        $company['description']  = isset($subline[1])?  html_entity_decode(trim(strip_tags($subline[1]))) : '';
        $company['website']  = strip_tags($response->find('.entry-inner a[!class]')[0]->innertext);


        $this->save($company);

    }


}



$scrapper = new BonjourIdee();

//$scrapper->parse();
$scrapper->export();