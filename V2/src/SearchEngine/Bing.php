<?php

namespace Extractor\SearchEngine;


use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;


/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\SearchEngine
 *
 * This class is to do a search on bing
 *
 * Class Bing
 */
class Bing extends SearchEngine
{




    const url = 'http://www.bing.com/search?cc=fr&q=';




    /**
     * @param $search
     * @return \Serps\Core\Serp\IndexedResultSet|\simple_html_dom|JsonResponse|void
     */
    public function rawSearch($search)
    {
        $this->query = $search;

        $response = $this->getHtml($this->getProxy(). self::url . urlencode($search));

        if($response instanceof JsonResponse){
            return $response;
        }

        $this->html = $response;

        $this->cleanResults();

        if (isset($this->html->find('#b_results')[0])) {

            /** @var \simple_html_dom_node $res */
            $res = $this->html->find('#b_results')[0];

            $r = $res->find('.b_algo');

        } else {

            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$search}, no bing results"), 404);
        }



        $results = new \Serps\Core\Serp\IndexedResultSet();


        /** @var \PHPHtmlParser\Dom\AbstractNode $re */
        foreach($r as $re) {

            $d = array(
                'url' => $this->removeProxy($re->find('h2 a')[0]->getAttribute('href')),
                'title' => strip_tags($re->find('h2 a')[0]->innertext),
                'subtitle' => isset($re->find('.b_caption .b_factrow')[0]) ? strip_tags($re->find('.b_caption .b_factrow')[0]) : '',
                'description' => null == $re->find('.b_caption p') ? '' :  strip_tags($re->find('.b_caption p')[0]->innertext),
            );

            $data = new \Serps\Core\Serp\BaseResult(array(),$d);
            $results->addItem($data);

        }

        return $results;

    }




    /**
     * Clean the bing results
     */
    private function cleanResults()
    {
        // Remove bing ads
        foreach ($this->html->find('.b_ad') as $item) {
            $item->outertext = '';
        }
        // Remove bing orthographic thing
        foreach ($this->html->find('.b_ans') as $item) {
            $item->outertext = '';
        }
        $this->html->save();
    }



}