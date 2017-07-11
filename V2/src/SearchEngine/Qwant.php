<?php

use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;


/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 *
 * @THIS FUNCTION DOES NOT WORK ANYMORE
 *
 * This class is to do a search on bing
 *
 * Class Bing
 */
abstract class Qwant extends SearchEngine
{

    const url = 'https://api.qwant.com/api/search/web';


    /**
     *
     * Patterns for results to remove
     *
     * @var array
     */
    public $PATTERNS = array(
        'COMPANIES' => '/VIP PRESTA/',
        'SEARCH' => '^t$'
    );



    /**
     * the HTML returned from Bing
     *
     * @var simple_html_dom
     */
    protected $html;


    /**
     * @var bool
     */
    public $results = false;


    /**
     * @var
     */
    public  $query;



    /**
     *
     * Do a search

     * @param string $search
     * @param string $regex     a search regex to avoid
     * @return array|bool|string|JsonResponse|void
     */
    public function search($search,$regex = '^t$')
    {

        $this->query = $search;

        $this->setResults(false);


        try {
            $client = new Client();

            $response = $client->get('https://api.qwant.com/api/search/web', array(
                'query' => array(
                    'count' => 10,
                    'locale' => 'fr_FR',
                    'q' => $search,
                    't' => 'all'
                ),
                'headers' => array(
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                )
            ));

            $data = json_decode($response->getBody()->getContents(), true);


            if(isset($data['data']['result']['items'][0])) {
                return $this->cleanLink($data['data']['result']['items']);
            }else{
                return new JsonResponse(array(
                    'error_key' => 'result',
                    'error' => "Extraction failed for {$this->query}, no bing results"), 404);
            }
        }catch (\GuzzleHttp\Exception\ClientException $e){
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => $e->getMessage()), 404);
        }
    }




    /**
     *
     * Extract the link from the results
     *
     * @param $links
     * @return array|JsonResponse
     */
    private function cleanLink($links)
    {


        /** @var \PHPHtmlParser\Dom\AbstractNode $link */
        foreach($links as $link) {

            $title = $link['title'];

            if (!preg_match('/(domains5|reseau-canope|pagesjaunes|infogreffe|laposte|mappy|microsoft|spainisculture|nozio|images\/search|boursorama|verif\.com|spain\.info|archive\.org|blogspot|' . $this->PATTERNS['SEARCH'] . '|youtube|^s$|wordpress|wikipedia|twitter|google|hubspot|znwhois|facebook|linkedin|amazon|minify|privea|BEST-PRICE|Fotosearch|bat\.bing)/', $link['url'])) {
                $url = $link['url'];
                $this->setResults(true);
                break;
            }
        }


        if (!isset($url)) {
            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$this->query}, no bing results"), 404);
        }

        return array(
            'url' => $url,
            'title' => $title,
        );

    }



    /**
     * @return boolean
     */
    public function hasResults()
    {
        return $this->results;
    }

    /**
     * @param boolean $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }



}