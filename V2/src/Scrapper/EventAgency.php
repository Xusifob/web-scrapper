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
 * Class EventAgency
 *
 */
class EventAgency extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'http://www.eventagencies.fr/fr/page/%page%/';






    /** @inheritdoc */
    public function scrapFiche($url)
    {


        $response = $this->getHtml($url);


        if($response instanceof JsonResponse){
            return;
        }



        $company  = array();


        $company['company']  = $this->getContent($response->find('h1.entry-title')[0]);

        parse_str(parse_url($this->getContent($response->find('iframe')[0],'src'))['query'],$output);

        if(isset($output['q'])){
            $company['address']  = urldecode($output['q']);
        }



        foreach($response->find('.entry-content strong') as $row)
        {


            $content = $this->getContent($row);


            if(preg_match('/Tél:? ?[0-9 . + ()]{10,30}/',$content)){
                $company['phone'] = trim(preg_replace('/^Tél:?/','',$content));
            }

            if(preg_match('/^((http:\/\/)?(www\.))?[a-z \- \/ ^.]+/',$content)){
                $company['website'] = $content;
            }


        }


vardump($company);

        $this->save($company);

    }


}



$scrapper = new EventAgency();

$scrapper->setHasPages(true);
$scrapper->setStop(20);

$scrapper->setSelectors(array(
    'list' => '.hentry',
));

$scrapper->parse();
//$scrapper->export();