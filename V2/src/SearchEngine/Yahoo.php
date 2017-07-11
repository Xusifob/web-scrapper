<?php

use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;


/**
 *
 * This class is to do a search on bing
 *
 * Class Bing
 */
abstract class Yahoo extends SearchEngine
{

    const url = 'https://fr.search.yahoo.com/search?p=';



    /**
     *
     * Do a Yahoo search

     * @param string $search
     * @param array $domains
     * @param array $domains_added
     * @return array|\Serps\Core\Serp\IndexedResultSet|JsonResponse
     */
    public function search($search,$domains = array(),$domains_added = array())
    {

        $this->domains_excluded  = array_merge($this->domains_excluded,$domains);

        $this->setResults(false);


        $this->query = $search;

        $search = urlencode($search);

        $response = $this->getHtml($this->getProxy() . self::url . $search);

        if($response instanceof JsonResponse){
            return $response;
        }

        $dom = new simple_html_dom();


        $this->html = $response;

        echo $response;
        die();


        // echo str_replace('/styles','http://societe.com/styles',$this->html);

        if (!$this->html || null == $this->html->find('.result a')) {
            $this->results = false;
            return;
        }else{
            $this->results = true;
        }

        return $this->cleanLink();

    }



    /**
     *
     * Extract the link from the results
     *
     */
    private function cleanLink()
    {


        if (isset($this->html->find('#resultsContainer')[0])) {

            $res = $this->html->find('#resultsContainer')[0];

            $links = $res->find('.result a');


        } else {
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$this->query}, no ecosia results"), 404);
        }




        /** @var \PHPHtmlParser\Dom\AbstractNode $link */
        foreach($links as $link) {

            $title = trim(strip_tags($link));

            preg_match('/http(s)?:\/\/[^"]+/', $link, $matches);


            if (is_array($matches)) {
                foreach ($matches as $m) {
                    if ($this->isCorrectDomain($m) && $this->isCorrectExtension($m)) {
                        $url = $m;
                        break;
                    }
                }
            }

             if(isset($url)){
                 break;
             }

        }

        if (!isset($url)) {
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$this->query}, no ecosia results"), 404);
        }

        return array(
            'url' => $url,
            'title' => $title,
        );

    }


}