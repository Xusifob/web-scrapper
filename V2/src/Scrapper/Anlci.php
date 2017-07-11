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
 * Class Anlci
 *
 */
class Anlci extends Scrapper implements ScrapperInterface
{




    public function scrapFiche($url)
    {

        $response = $this->getHtml($url);


        if($response  instanceof JsonResponse){
            $response->send();
            die();
        }



        $content = $response->find('.coordonnees')[0];

        $content = explode('<br />',$content);



        $company = array();

        $company['company'] = $this->getContent($response->find('h1'));


        foreach($content as $c)
        {
            if(preg_match('/ [0-9]{5} /',$c)){
                $company['address'] = $this->sanitize($c);
            }

            if(preg_match('/Téléphone/',$c)){
                $company['phone'] = $this->sanitize(str_replace('Téléphone :','',$c));
            }

            if(preg_match('/(https?:\/\/)?(www\.)?[^.]+\.[a-z]+/',$c)){
                $company['website'] = $this->sanitize($c);
            }

        }



        $content = $response->find('.gauche')[0];


        $content = explode('<br />',$content);


        foreach($content as $c)
        {

            $user = explode(':',$this->sanitize($c));

            if($user[1] != 'NC NC'){

                $company['title'] = $user[0];
                $company['full_name'] = $user[1];

            }


            vardump($company);

            $this->save($company);

        }





    }




}



$scrapper = new Anlci();

$scrapper->setHasPages(true);
$scrapper->setStart(0);
$scrapper->setStop(8);

$scrapper->setSelectors(array(
    'list' => '.listresult .mini_acteur'
));

$scrapper->setUrl('http://www.anlci.gouv.fr/Annuaire-des-organismes-de-formation');


/*foreach(array(8410,628,8409,8407,633,8408,8412,8414,642,645) as $region){



    $scrapper->setUrl('http://www.anlci.gouv.fr/Annuaire-des-organismes-de-formation/(regions)/'. $region .'/(page)/%page%');


    $scrapper->parse();

}*/

$scrapper->export();
