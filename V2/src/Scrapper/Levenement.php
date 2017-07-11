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
 * Class Levenement
 *
 */
class Levenement extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'http://www.levenement.org/les-agences/';






    /** @inheritdoc */
    public function parse()
    {

        for($i = 40;$i<46;$i++){

            $response = $this->getHtml($this->url . $i);


            if($response  instanceof JsonResponse){
                $response->send();
                die();
            }


            $list = $response->find('.fusion-portfolio-post');


            /** @var \simple_html_dom_node $elem */
            foreach($list as $key => $elem)
            {

                $this->scrapTheFiche($elem->find('a')[0]->getAttribute('href'),$this->getContent($elem->find('.entry-title')[0]));

            }



        }
    }




    /** @inheritdoc */
    public function scrapTheFiche($url,$company_name)
    {


        $response = $this->getHtml($url);


        if($response instanceof JsonResponse){
            return;
        }



        $company  = array();


        $company['company']  = $company_name;


        foreach($response->find('.fusion-one-third p') as $row)
        {

            $content = $this->getContent($row);

            if(preg_match('/^((http:\/\/)?(www\.))?[a-z \- \/ ^.]+/',$content)){
                $company['website'] = $content;
            }

            if(preg_match('/[0-9 ]{0,15}/',$content)){
                $company['phone'] = $content;
            }


        }


        foreach($response->find('.content-box-percentage') as $row)
        {

            $content = $this->getContent($row);

            if(preg_match('/[0-9]+M€/',$content)){
                $company['turnover'] = $content;
            }


        }



        $this->save($company);

    }


}



$scrapper = new BonjourIdee();

//$scrapper->parse();
$scrapper->export();