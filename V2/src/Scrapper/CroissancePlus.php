<?php

include __DIR__ . '/src/header.php';


/**
 *
 * @deprecated
 *
 * Class CroissancePlus
 */
class CroissancePlus extends Scrapper implements ScrapperInterface
{


    /**
     *
     * Prefix of the website
     *
     * @var string
     */
    protected $prefix = 'http://www.croissanceplus.com';


    /**
     * The export directory
     *
     * @var string
     */
    private $dir = 'croissanceplus';



      /**
     *
     */
    public function parse()
    {
        $this->getCompanyData();


    }




    public function getCompanyData()
    {

        $response = $this->getHtml('http://www.croissanceplus.com/membres/');

        if($response  instanceof \Symfony\Component\HttpFoundation\JsonResponse){
            $response->send();
            die();
        }


        $list = $response->find('.partners a');



        /** @var simple_html_dom_node $elem */
        foreach($list as $elem)
        {

            $this->scrapFiche($elem->getAttribute('href'));

            sleep(1);

        }


    }


    public function scrapFiche($url)
    {



        $response = $this->getHtml($url);


        if($response instanceof \Symfony\Component\HttpFoundation\JsonResponse){
            $response->send();
            return;
        }


        $company  = array();

        $company['company']  = preg_replace('/<[^>]+>.+<[^>]+>/i','',$response->find('.content h1')[0]->innertext);

        $company['website']  = isset($response->find('.employer-boxes a')[0]) ? $response->find('.employer-boxes a')[0]->getAttribute('href') : '';
        $company['full_name']  = isset($response->find('.employer-boxes h3')[0]) ? $response->find('.employer-boxes h3')[0]->innertext : '';
        $company['position']  = isset($response->find('.employer-boxes .entry p strong')[0]) ? $response->find('.employer-boxes .entry p strong')[0]->innertext : '';

        vardump($company);

        if(!is_dir(__DIR__ . '/data/'  . $this->dir . '/')){
            mkdir(__DIR__ . '/data/'  . $this->dir . '/',0755,true);
        }

        file_put_contents(__DIR__ . '/data/'  . $this->dir . '/' . str_replace('/','-',$company['company']) . '.json',json_encode($company,JSON_PRETTY_PRINT));






    }


    /**
     * @param simple_html_dom $response
     *
     * @return array
     *
     */
    public function getLinks($response)
    {

        /** @var simple_html_dom $table */
        $table = $response->find('#divCompanies')[0];

        $links = $table->find('.fileview a');

        $t = array();

        /** @var simple_html_dom_node $link */
        foreach($links as $link){
            $t[] = $link->getAttribute('href');
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

        $dir = __DIR__ . "/data/" . $this->dir;

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


$scrapper = new CroissancePlus();

$scrapper->export();