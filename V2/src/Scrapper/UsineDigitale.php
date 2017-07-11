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
 * Class FrenchTech
 *
 */
class UsineDigitale extends Scrapper implements ScrapperInterface
{


    /** @inheritdoc */
    protected $url = 'http://www.usine-digitale.fr/annuaire-start-up/start-up-du-web/';


    protected $prefix =  'http://www.usine-digitale.fr';

    /**
     * The export directory
     *
     * @var string
     */
    protected $dir = 'Usine-Digitale-Startup-web';




    /** @inheritdoc */
    public function parse()
    {


        for($i = 21;$i<26;$i++){

            $response = $this->getHtml($this->url . $i  .'/');

            if($response  instanceof JsonResponse){
                $response->send();
                die();
            }


            $list = $response->find('.contenuPage article section a');


            /** @var \simple_html_dom_node $elem */
            foreach($list as $key => $elem)
            {

                $this->scrapFiche($this->prefix .$elem->getAttribute('href'));

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



        /** @var \simple_html_dom_node $content */
        $content = $response->find('.contenuPage')[0];



        $company  = array();

        $company['company']  = $content->find('h1')[0]->innertext;

        $company['address'] = '';
        $company['website'] = '';
        $company['phone'] = '';


        /** @var \simple_html_dom_node $row */
        foreach($content->find('#infoPratiq ul li') as $row)
        {

            $key = trim($row->find('div')[0]->innertext);

            if(preg_match('/Adresse/i',$key)){
                $company['address'] = html_entity_decode(trim(str_replace("<br>"," ",$row->find('p')[0]->innertext)));
            }
            if(preg_match('/Web/i',$key)){
                $company['website'] = trim(str_replace("<br>"," ",$row->find('p a')[0]->innertext));
            }
            if(preg_match('/phone/i',$key)){
                $company['phone'] = strip_tags(trim(str_replace("<br>"," ",$row->find('p')[0]->innertext)));
            }

        }

        $company['produit'] = '';
        $company['business_model'] = '';

        /** @var \simple_html_dom_node $row */
        foreach($content->find('h2.txtArtTitre') as $row)
        {

            $key = trim($row->innertext);


            if(preg_match('/Produit/i',$key)){
                $company['produit'] = html_entity_decode(trim(str_replace('    	',' ',strip_tags($row->next_sibling()->innertext))));
            }

            if(preg_match('/Business model:/i',$key)){
                $company['business_model'] = html_entity_decode(trim(str_replace('    	',' ',strip_tags($row->next_sibling()->innertext))));
            }

        }


        $this->save($company);

    }


}



$scrapper = new UsineDigitale();

$scrapper->parse();
//echo 'done !';

$scrapper->export();