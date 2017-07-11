<?php

include __DIR__ . '/src/header.php';


/**
 *
 * @deprecated
 *
 * Class AACC
 */
class AACC extends Scrapper implements ScrapperInterface
{


    /**
     *
     * Prefix of the website
     *
     * @var string
     */
    protected $prefix = 'http://www.aacc.fr';


    /**
     * The export directory
     *
     * @var string
     */
    private $dir = 'AACC';



      /**
     *
     */
    public function parse()
    {
        $this->getCompanyData();


    }




    public function getCompanyData()
    {

        $response = $this->getHtml('http://www.aacc.fr/agencies/page/14');

        if($response  instanceof \Symfony\Component\HttpFoundation\JsonResponse){
            $response->send();
            die();
        }


        $list = $response->find('.listeAgences .carteAgence a');


        /** @var simple_html_dom_node $elem */
        foreach($list as $elem)
        {
            $this->scrapFiche($this->prefix .  $elem->getAttribute('href'));
        }


    }


    public function scrapFiche($url)
    {

        $response = $this->getHtml($url);


        if($response instanceof \Symfony\Component\HttpFoundation\JsonResponse){
            $response->send();
            die();
        }


        $company  = array();

        $company['name']  = $response->find('h1')[0]->innertext;
        $company['phone']  = isset($response->find('.tel')[0]) ? $response->find('.tel')[0]->innertext : '';
        $company['email']  = isset($response->find('.mail a')[0]) ? $response->find('.mail a')[0]->innertext : '';
        $company['website']  = $response->find('.adresse a')[0]->innertext;
        $company['address']  = trim(preg_replace('/<a .+>.+<\/a>/','',str_replace('<br />',' ',$response->find('.adresse')[0]->innertext)));

        file_put_contents(__DIR__ . '/data/aacc/' . $company['name']  .'.json',json_encode($company,JSON_PRETTY_PRINT));

        foreach($response->find('.carteAgence') as $link)
        {
            $company['first_name'] = '';
            $company['last_name'] = '';
            $company['position'] = '';

            $company['first_name'] = preg_replace('/(<strong>.+<\/strong>|<br>)/','',$link->find('.name')[0]->innertext);
            $company['last_name'] = preg_replace('/(<strong>.+<\/strong>|<br>)/','',$link->find('.name strong')[0]->innertext);
            $company['position'] = preg_replace('/(<strong>.+<\/strong>|<br>)/','',$link->find('.domaine')[0]->innertext);

            file_put_contents(__DIR__ . '/data/aacc/' . $company['name'] . $company['first_name']  . '.json',json_encode($company,JSON_PRETTY_PRINT));

            vardump($company);

        }



    }


    /**
     * @param simple_html_dom $response
     *
     * @return array
     *
     */
    public function getLinks($response)
    {

        /** @var simple_html_dom $links */
        $table = $response->find('#divCompanies')[0];

        $links = $table->find('.fileview a');

        $t = array();

        foreach($links as $link){
            $t[] = $link->attr('href');
        }



        return $t;

    }


    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }



    /**
     *
     * Export to excel
     *
     */
    public function export()
    {

        $dir = __DIR__ . "/data/aacc";

        if(!is_dir($dir)){
            $response = new \Symfony\Component\HttpFoundation\JsonResponse("Le dossier {$dir} n'existe pas",400);
            $response->send();
            return;
        }

        $data = array();

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $d = json_decode(file_get_contents($dir . '/' . $entry),true);
                    $data[] = $d;
                }
            }
            closedir($handle);
        }

        $response = new CSVResponse($data);
        $response->setFilename("export-{$this->dir}.csv");


        $response->send();
        die();
        return;

    }



}


$scrapper = new AACC();

$scrapper->export();