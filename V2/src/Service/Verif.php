<?php


namespace Extractor\Service;

use Extractor\Exception\JsonEncodedException;
use Extractor\Scrapper\DOMAnalyser;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;
use Extractor\Traits\NameParser;

use ForceUTF8\Encoding;

/**
 *
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * This class is to do a search on verif.com
 *
 * Class Verif
 */
class Verif extends DOMAnalyser
{



    use NameParser;


    /**
     * the HTML returned from Bing
     *
     * @var \simple_html_dom
     */
    protected $html;


    /**
     * @var string
     */
    protected $search_url = 'http://www.verif.com/recherche/{{SEARCH}}/1/ca/d/?ville=null';


    /**
     * @var string
     */
    protected $societe_url = 'http://www.verif.com/societe/';




    /**
     * @param $search
     * @param null $siren
     * @return mixed|string
     * @throws JsonEncodedException
     */
    public function search($search,$siren = null)
    {

        if(isset($siren) && !empty($siren)){

            $siren = str_replace(' ','',$siren);

            return $this->societe_url . $siren;

        }


        $url = str_replace('{{SEARCH}}',urlencode($search),$this->search_url);


        $response = $this->getHtml($this->getProxy() . $url);

        if($response instanceof JsonResponse){
            throw new JsonEncodedException(json_decode($response->getContent()));
        }

        $this->html = $response;

        /** @var \simple_html_dom_node[]|null $link */
        $link  = $this->html->find('#verif_tableResult tr td a');

        if($link == null){
            throw new JsonEncodedException(array(
                'error_key' => 'result',
                'error' => 'company not found on verif.com',
                'code' => 404
            ));
        }


        return $this->removeProxy($link[0]->getAttribute('href'));

    }



    /**
     *
     * Do a bing search
     *
     *
     * @param string $url
     * @return array
     * @throws JsonEncodedException
     */
    public function parse($url = '')
    {



        $company = array();

        $response = $this->getHtml($this->getProxy() . $url);

        if($response instanceof JsonResponse){
            throw new JsonEncodedException(json_decode($response->getContent()));
        }

        $this->html = $response;



        // There is an issue with the search
        if (!$this->html || null == $this->html->find('table.dirigeants')) {
            throw new JsonEncodedException(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$url}, no dirigeant found on verif.com",
                'code' => 404
            ));
        }


        /** @var \simple_html_dom_node $row */
        foreach($this->html->find('table.dirigeants')[0]->find('tr') as $row)
        {

            $key = $this->getContent($row->find('td')[0]);

            if(preg_match('/financier|comptable/i',utf8_encode($key))){
                $daf = $this->getContent($row->find('td')[1]);
                $contact['position'] =  $key;

                $name = $this->parseName($daf);

            }


            if(Utils::extractFromRequest('scrap_ceo')){
                if(preg_match('/Directeur général/i',utf8_encode($key))){
                    $daf = strip_tags(utf8_encode(trim($row->find('td')[1]->innertext)));
                    $contact['position'] = 'Directeur général';

                    $name = $this->parseName($daf);
                }

                if(preg_match('/^Gérant$/i',utf8_encode($key))){
                    $daf = strip_tags(utf8_encode(trim($row->find('td')[1]->innertext)));
                    $contact['position'] = 'Gérant';

                    $name = $this->parseName($daf);
                }
            }


            if(!isset($name) || !$name){
                continue;
            }



            // Change here because they are inverted
            $contact['first_name'] = Encoding::fixUTF8($name['last_name']);
            $contact['last_name'] = Encoding::fixUTF8($name['first_name']);


            $company[] = $contact;

            unset($name);



        }


        return $company;
    }



}