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
class BePub extends Scrapper implements ScrapperInterface
{




    public function scrapFichesUrls($url)
    {

        $response = $this->getHtml($url);

        echo $url;

        if($response  instanceof JsonResponse){
            $response->send();
            die();
        }


        foreach($response->find('#page-content section') as $section)
        {

            $company = array();

            $address = explode(' - ',$this->getContent($section->find('.address')));


            $found = false;

            foreach($section->find('img') as $img){

                if($this->getContent($img,'src') == '/flags/fl1.gif'){
                    $found = true;
                }

            }

            if(!$found){
                continue;
            }

            $company['company'] = $this->getContent($section->find('h2'));
            $company['description'] = $this->getContent($section->find('.skills'));
            $company['full_name'] = isset($address[1]) ? trim($address[0]) : '';
            $company['adresse'] =  isset($address[1]) ? trim($address[1]) : trim($address[0]);
            $company['description'] = $this->getContent($section->find('.skills'));
            $company['website'] = $this->getContent($section->find('.web'),'href');


            vardump($company);

            $this->save($company);


        }


    }




}



$scrapper = new BePub();

$scrapper->setHasPages(true);
$scrapper->setStart(1);
$scrapper->setStop(57);

$scrapper->setUrl('http://www.bepub.com/annuaire-evenementiel/agence-conseil-evenementiel/page-%page%');



//$scrapper->parse();
$scrapper->export();