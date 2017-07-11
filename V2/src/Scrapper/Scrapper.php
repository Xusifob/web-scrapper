<?php

namespace Extractor\Scrapper;

use Extractor\Service\Utils;
use LayerShifter\TLDExtract\Extract as WebsiteExtracter;


use Extractor\Scrapper\ScrapperInterface;
use \Extractor\Service\RequestDoer;
use ForceUTF8\Encoding;


use \Symfony\Component\HttpFoundation\JsonResponse;

use Extractor\HttpFoundation\CSVResponse;


/**
 *
 * This class is used to scrap information from a website
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Scrapper
 *
 * Class Scrapper
 *
 */
class Scrapper extends DOMAnalyser implements ScrapperInterface
{


    const REGEXS = array(
        'phone' => "/(Phone|Téléphone)(( +)?(:|-)?( +)?)?/i",
        'website' => "/(web|Site internet)(( +)?(:|-)?( +)?)?/i",
        'adresse' => "/(address|Adresse)(( +)?(:|-)?( +)?)?/i",
    );


    /**
     *
     * List of all selectors
     *
     * @var array
     */
    protected $selectors = array(
        'list_link' => 'a',
    );




    /**
     *
     * First Page
     *
     * @var int
     */
    protected $start = 1;


    /**
     * Last Page
     *
     * @var int
     */
    protected $stop = 10;


    /**
     * @var bool
     */
    protected $has_pages = false;


    /**
     *
     * Prefix of the website
     *
     * @var string
     */
    protected $url;


    /**
     * The export directory
     *
     * @var string
     */
    protected $dir;



    /**
     * @param string[] $company
     */
    protected function save($company)
    {
        if(!is_dir(self::DIR . $this->dir)){
            mkdir(self::DIR . $this->dir);
        }

        $filename = $company['company'];

        if(isset($company['first_name'])){
            $filename .= '-' . $company['first_name'];
        }


        if(isset($company['last_name'])){
            $filename .= '-' . $company['last_name'];
        }

        if(isset($company['full_name'])){
            $filename .= '-' . $company['full_name'];
        }

        if(isset($company['position'])){
            $filename .= '-' . $company['position'];
        }


        $filename = str_replace('/','-',$filename);
        $filename = trim(preg_replace('/ +/',' ',$filename));


        $filename .= '.json';


        file_put_contents( self::DIR . $this->dir .'/' . $filename,json_encode($company,JSON_PRETTY_PRINT));
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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {

        $extractor = new WebsiteExtracter();

        $result = $extractor->parse($url);

        // Set the prefix
        $this->prefix = strstr($url,'http://') ? 'http://' : 'https://';
        $this->prefix .= $result->getHostname() . '.' . $result->getSuffix();

        $this->setDir($result->getHostname());

        $this->url = $url;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * @param int $stop
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
    }

    /**
     * @return boolean
     */
    public function hasPages()
    {
        return $this->has_pages;
    }

    /**
     * @param boolean $has_pages
     */
    public function setHasPages($has_pages)
    {
        $this->has_pages = $has_pages;
    }







    /** @inheritdoc */
    public function parse()
    {

        if($this->hasPages()){
            for($i = $this->getStart();$i<$this->getStop();$i++){
                $this->scrapFichesUrls(str_replace('%page%',$i,$this->url));
            }
        }else{
            $this->scrapFiche($this->url);
        }
    }


    /**
     *
     * Scrap all the urls from a page
     *
     * @param $url
     */
    protected function scrapFichesUrls($url)
    {

        $response = $this->getHtml($url);


        if($response  instanceof JsonResponse){
            $response->send();
            die();
        }


        $list = $response->find($this->getSelector('list'));


        /** @var \simple_html_dom_node $elem */
        foreach($list as $key => $elem)
        {

            $this->scrapFiche($elem->find($this->getSelector('list_link'))[0]->getAttribute('href'));

        }

    }


    /**
     *
     * Export to excel
     *
     * @param null $list
     * @param string $file_name
     */
    public function export($list = null,$file_name = '')
    {

        $dir =  self::DIR . $this->dir .'/';

        if(!is_dir($dir)){
            $response = new JsonResponse("Le dossier {$dir} n'existe pas",400);
            $response->send();
            return;
        }



        $files = array_diff(scandir($dir),array('..','.','.json'));


        if(count($files) > 15000)
        {
            $_files = array_chunk($files,15000);

            $tmp_file = $file_name ?  'data/' . $file_name :  'data/' . uniqid() . '.csv';

            if($list){

                if(!isset($_files[$list])){
                    return;
                }

                $file = $_files[$list];

                $data = $this->getDirectoryData($dir,$file);

                Utils::array_to_csv($data,$tmp_file);

                unset($data);

            }else{
                foreach($_files as $file)
                {
                    $data = $this->getDirectoryData($dir,$file);

                    Utils::array_to_csv($data,$tmp_file);

                    unset($data);

                }
            }



           // $data = file_get_contents($tmp_file);

           // unlink($tmp_file);
            echo '<a href="V2/'. $tmp_file .'" >Télécharger</a>';
            die();

        }else{
            $data = $this->getDirectoryData($dir);
        }


        $response = new CSVResponse($data);
        $response->setFilename("export-{$this->dir}.csv");


        $response->send();
        die();

    }


    /**
     * @param $dir
     * @param array $files
     * @return array
     */
    public function getDirectoryData($dir,$files = array())
    {

        $data = array();

        if(!$files){
            $files = array_diff(scandir($dir),array('..','.','.json'));

        }


        foreach($files as $entry){
            $d = json_decode(file_get_contents($dir . '/' . $entry),true);
            $data[] = $d;
        }

        return $data;
    }


    /** @inheritdoc */
    public function scrapFiche($url)
    {


        $response = $this->getHtml($url);


        if($response instanceof JsonResponse){
            return;
        }



        $company  = array();

        $company['company']  = $this->getSelectorText($response,'company');
        $company['address']  = $this->getSelectorText($response,'address');
        $company['phone']  = $this->getSelectorText($response,'phone');
        $company['website']  = $this->getSelectorText($response,'website');


        /** @var \simple_html_dom_node $row */
        foreach($response->find($this->getSelector('data_list')) as $row)
        {

            $content = $this->getContent($row);


            $regexs = self::REGEXS;

            foreach(array_keys($regexs) as $key){

                if(preg_match($regexs[$key],$content)){
                    if(!isset($company[$key]) || empty($company[$key])){
                        $company[$key] = trim(preg_replace($regexs[$key],'',$content));
                    }
                }


            }

        }


        $this->save($company);

    }




    /**
     *
     * Display the HTML with correct styling
     *
     * @param $html
     * @return mixed
     */
    protected function parseStyle($html)
    {
        return Encoding::fixUTF8(str_replace(array('/css','/js','/styles'),array($this->prefix . '/css',$this->prefix . '/js',$this->prefix . '/styles'),$html));
    }


}
