<?php


namespace Extractor\Service;

use Extractor\Scrapper\DOMAnalyser;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;


use Extractor\Traits\NameParser;
use ForceUTF8\Encoding;


/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 *
 * This class is to do a search on societe.com
 *
 * Class Societe
 */
class Societe extends DOMAnalyser
{


    use NameParser;


    const societes = '/(Societe|(SARL | SARL)|( SAS|SAS )|Ventures|Groupe|HOLDING|GROUP|Invest|Investissements|MARKETING)/i';


    /**
     * @var string
     */
    protected $search_url = 'http://www.societe.com/cgi-bin/liste';



    /**
     * the HTML returned from Bing
     *
     * @var \simple_html_dom
     */
    protected $html;


    /**
     *
     * societe.com prefix
     *
     * @var string
     */
    protected $prefix = 'http://www.societe.com';





    /**
     *
     * Do a bing search
     *
     *
     * @param string $url
     * @return mixed
     */
    public function parse($url = '')
    {

        $company = array(
            'description' => '',
            'turnover' => '',
        );

        $response = $this->getHtml($this->getProxy() . $url);

        if($response instanceof JsonResponse){
            return $response;
        }

        $this->html = $response;

        // There is an issue with the search
        if (!$this->html || null == $this->html->find('#rensjur')) {
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$url}, no page found on societe.com"),404);
        }

        $name = preg_replace('/(SAS|SARL|EURL|SOC|COOP| AGRIC )/i', '', $this->html->find('h1')[0]->innertext);
        $name = $this->sanitize($name);
        $president = self::get_societe_president($this->html);

        if ($president && is_array($president)) {
            $company = array_merge($company,$president);

        } else {
            if($president instanceof JsonResponse){
                return $president;
            }


            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$name}, no president found"),404);
        }



        /** @var \simple_html_dom_node $row */
        foreach($this->html->find('#rensjur')[0]->find('tr') as $row)
        {

            $key = trim($row->find('td')[0]->innertext);


            if(preg_match('/Statut/i',$key)){
                $status = Encoding::fixUTF8(trim($row->find('td')[1]->innertext));

                if(preg_match('/radiée/i',$status)){
                    return new JsonResponse(array(
                        'error_key' => 'result',
                        'error' => "Extraction failed for {$name}, company seems not to exist anymore"),404);
                }

            }

            if(preg_match('/Jugement/i',$key)){
                $status = Encoding::fixUTF8(trim($row->find('td')[1]->innertext));

                if(preg_match('/liquidation/i',$status)){
                    return new JsonResponse(array(
                        'error_key' => 'result',
                        'error' => "Extraction failed for {$name}, company en liquidation"),404);
                }

            }

            if(preg_match('/Activit/i',$key)){
                $company['description'] =  $this->sanitize($row->find('td')[1]->innertext);
            }

            if(preg_match('/SIREN/i',$key)){
                $company['siren'] =  $this->sanitize($row->find('td')[1]->innertext);
            }

            if(preg_match("/Chiffre d'affai/i",$key)){
                $company['turnover'] =  (int)$this->sanitize($row->find('td')[1]->innertext);
            }

        }


        $company['position'] = 'CEO';

        $company['name'] = $name;


        return $this->getCreances($company);
    }








    /**
     *
     * Get some informations about the creances (turnover, date of exploitation & creances)
     *
     * @param array $company    The company data
     * @return array
     */
    protected function getCreances($company)
    {


        $links = $this->html->find('#menuentreprise a');




        /** @var \simple_html_dom_node $link */
        foreach($links as $link){
            if(preg_match('/\/bilan\//',$link->getAttribute('href'))){
                $url = $this->removeProxy($link->getAttribute('href'));
                break;
            }
        }



        if(!isset($url)){
            return $company;
        }


        $response = $this->getHtml($this->getProxy() . $url);


        if($response instanceof JsonResponse){
            return $company;
        }



        $this->html = $response;





        $creances = '';
        $date = '';
        $turnover = '';


        /** @var \simple_html_dom_node $table*/
        $table = $this->html->find('#actif')[0];


        /** @var \simple_html_dom_node $row */
        foreach($table->find('tr') as $key => $row)
        {

            $cols = $row->find('th,td');


            if(!isset($cols[0])){
                continue;
            }


            if(preg_match('/Date de cl(ô|&ocirc;)ture/i',Encoding::fixUTF8($cols[0]->innertext))){
                $date = $cols[1]->innertext;
            }


            if(preg_match('/- - cr(é|&eacute;)ances/i',Encoding::fixUTF8($cols[0]->innertext))){
                $creances = $cols[1]->innertext;
            }


        }



        /** @var \simple_html_dom_node $table */
        $table = $this->html->find('#compteresultat')[0];


        /** @var \simple_html_dom_node $row */
        foreach($table->find('tr') as $key => $row)
        {

            $cols = $row->find('th,td');


            if(Encoding::fixUTF8($cols[0]->innertext) == ('Chiffre d\'affaires')){
                $turnover = $cols[1]->innertext;
            }


        }

        return array_merge($company,array(
            'date_du_bilan' => $this->sanitize($date),
            'turnover' => $this->sanitize($turnover),
            'creances' => $this->sanitize($creances)
        ));
    }



    /**
     *
     * Return the name of the president
     *
     * @param \simple_html_dom $html
     * @param int $loop
     * @return array|bool|\simple_html_dom|JsonResponse
     */
    protected function get_societe_president($html,$loop = 0)
    {

        $loop++;



        $table = $html->find('#tabledir table');


        if(!isset($table[0])){
            return false;
        }

        /** @var\ simple_html_dom_node $table */
        $table = $table[0];

        /** @var \simple_html_dom_node $row */
        $row = $table->find('tr')[0];
        if($row == null){
            return false;
        }

        if(!isset($row->find('td')[1]->find('a')[0])){
            return false;
        }

        $president_link = $row->find('td')[1]->find('a')[0];


        $president = trim(Encoding::fixUTF8($president_link->innertext));

        if((strpos($president,' ') !== false && 0 === preg_match(self::societes,$president)) || $loop == 5){

            // Get name
            $president =  str_replace('ENTREPRISES', '', $president);


            return $this->parseName($president);

        }

        $link = $this->removeProxy($president_link->href);



        $response = $this->getHtml($this->getProxy() . $link);

        if($response instanceof JsonResponse){
            return $response;
        }

        $html = $response;

        if(null == $html->find('#liste a.linkresult')){
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$link}, company seems not to exist anymore"),404);

        }

        $link = $this->removeProxy($html->find('#liste a.linkresult')[0]->href);


        $response = $this->getHtml($this->getProxy() . $link);

        if($response instanceof JsonResponse){
            return $response;
        }


        return $this->get_societe_president($response,$loop);
    }





    public function search($company, $address = '')
    {

        $dep = '';

        if($address){
            preg_match('/[0-9]{5}/',$address,$matches);


            $zip_code = isset($matches[0]) ? $matches[0] : '';
            $dep = substr($zip_code,0,2);
        }


        $query = array(
            'dep' => $dep,
            'nom' => $company,

        );

        $query = http_build_query($query);

        $response = $this->getHtml($this->getProxy() . $this->search_url . '?' . $query);


        if($response instanceof JsonResponse){
            return $response;
        }



        /** @var \simple_html_dom_node[] $link */
        $link = $response->find('#liste .linkresult');

        if(null == $link){
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => 'Company not found on Societe.com',
            ),404);
        }


        return $this->removeProxy($link[0]->getAttribute('href'));

    }


}