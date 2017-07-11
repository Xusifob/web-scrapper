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
class Capital extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'http://www.capital.fr/votre-carriere/les-pme-prometteuses-dans-le-nord-et-l-est-409899';


    /**
     * The export directory
     *
     * @var string
     */
    protected $dir = 'Capital-nord-est';




    /** @inheritdoc */
    public function parse()
    {

        $response = $this->getHtml($this->url);

        if($response  instanceof JsonResponse){
            $response->send();
            die();
        }


        $list = $response->find('.article-body p a');


        /** @var \simple_html_dom_node $elem */
        foreach($list as $key => $elem)
        {

            $this->scrapFiche($elem->getAttribute('href'));

        }



    }




   /** @inheritdoc */
    public function scrapFiche($url)
    {

        $response = $this->getHtml($url);


        if($response instanceof JsonResponse){
           return;
        }



        $subline = explode('-',$response->find('.article-body p')[0]);


        $company  = array();

        $company['company']  = $response->find('h1.article-title')[0]->innertext;
        $company['description']  = trim(strip_tags($subline[0]));
        $company['address']  = trim(strip_tags(str_replace('Ville :','',$subline[1])));

        $company['website']  =$response->find('.article-body a[href^="http"]')[0]->innertext;


        $this->save($company);

    }


}



$scrapper = new Capital();

$scrapper->parse();
$scrapper->export();