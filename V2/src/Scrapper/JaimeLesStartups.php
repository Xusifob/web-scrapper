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
 * Class JaimeLesStartups
 *
 */
class JaimeLesStartups extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'http://www.jaimelesstartups.fr/tag/b2b/page/';





    /** @inheritdoc */
    public function parse()
    {

        for($i = 1;$i<4;$i++){


            $response  = $this->getHtml($this->url . $i);


            if($response  instanceof JsonResponse){
                $response->send();
                die();
            }



            $list = $response->find('#primary .entry');


            /** @var \simple_html_dom_node $elem */
            foreach($list as $key => $elem)
            {

                $this->scrapFiche($elem->find('.entry-title a')[0]->getAttribute('href'));

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



        $company  = array();

        $company['company']  = $response->find('h1.entry-title')[0]->innertext;
        $company['description']  = trim(strip_tags($response->find('.entry-content p')[0]->innertext));

        $company['website']  =$response->find('.urlwebsite')[0]->innertext;


        $this->save($company);

    }


}



$scrapper = new JaimeLesStartups();

$scrapper->parse();
$scrapper->export();