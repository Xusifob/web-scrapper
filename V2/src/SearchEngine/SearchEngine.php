<?php

namespace Extractor\SearchEngine;



namespace Extractor\SearchEngine;

use Symfony\Component\HttpFoundation\JsonResponse;

use LayerShifter\TLDExtract\Extract as WebsiteExtracter;

use Extractor\Service\Utils;


use Extractor\Service\RequestDoer;

/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\SearchEngine
 *
 * Class SearchEngine
 * @package Extractor\SearchEngine
 */
abstract class SearchEngine extends RequestDoer
{


    /**
     * The Google Maps API Keys
     */
    protected $maps_keys = array();


    /**
     * The Google Maps Search URL
     */
    const  maps_search_url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';




    /**
     *
     * The Google Place URL
     *
     */
    const place_url = 'https://maps.googleapis.com/maps/api/place/details/json';



    /**
     * @var bool
     */
    public $results = false;


    /**
     * @var string
     */
    protected $query;



    /**
     * the HTML returned from Bing
     *
     * @var \simple_html_dom
     */
    protected $html;



    /** @const string EXT  the possible extensions for domain search */
    const EXT = '/^(net|is|bzh|world|info|org|asso\.fr|agency|pro|digital|eu|me|com|co|fr|io|ai|tv)$/i';


    /**
     *
     * List of excluded domains in search engine
     *
     * @var array
     */
    protected $_domains_excluded  = array(
        'twitter',
        'hubspot',
        'znwhois',
        'facebook',
        'linkedin',
        'amazon',
        'minify',
        'privea',
        'spainisculture',
        'nozio',
        'boursorama',
        'verif',
        'spain',
        'youtube',
        '^s$',
        'wordpress',
        'wikipedia',
        'archive',
        'blogspot',
        'bloomberg',
        'monster',
        'linternaute',
        'bfmtv',
        'findthecompany',
        'reseau-canope',
        'google',
        'pagesjaunes',
        'infogreffe',
        'laposte',
        'mappy',
        'microsoft',
        'bing',
        'lequipe',
        'eurosport',
        'strategies',
        'fotosearch',
        'domains5',
        'tripadvisor',
        'enterprise',
        'instagram',
        'softonic',
        'deviantart',
        'booking',
        'london',
        'bit',
        'foursquare',
        'corporama',
        'cancer',
        'procedurecollective',
        'facebook',
        'manageo',
        'unesco',
        'kompass',
        'larousse',
        'linguee',
        'francemediasmonde',
        'reverso',
        'dailymotion',
        'futura-sciences',
        'over-blog',
        'annuaire-horaire',
        'viadeo',
        'ouest-france',
        'allocine',
        'michelin',
        'tumblr',
        'bizapedia',
        'synonymes',
        'slideshare',
        'ratp',
        'frenchweb',
        'paranormal-encyclopedie',
        'french-corporate',
        'glassdoor',
        'docker',
        'wanadoo',
        'github',
        'isarta',
        'lemonde',
        'apple',
        'usine-digitale',
        'voici',
        'francetvpub',
        'cnn',
        'orange-business',
        'makingitlovely',
        'shpi',
        'hotel',
        'ihg',
        'homiletique',
        'videotron',
        'adobe',
        'blogbang',
        'nouvelobs',
        'reuters',
        'aurubis',
        'e-leclerc',
        'clubic',
        'lamontagne',
        'ledauphine',
        'techno-science',
        'univ-lyon3',
        'manpower',
        'unjobdanslapub',
        'iquesta',
        'prezi',
        'lexpress',
        'assemblee-nationale',
        'remixjobs',
        'admission-postbac',
        'letudiant',
        'yahoo',
        'commentcamarche',
        'emule',
        'boursier',
        'airfrance',
        'service-public',
        'lefigaro',
        'gmail',
        'zonebourse',
        'frenchweb',
        'youtube',
        'france24',
        'ebay',
        'lefigaro',
        'leparisien',
        'oracle',
        'regionsjob',
        'castorama',
        'iledefrance',
        'societegenerale',
        'bepub',
        'cnrs',
        'opentext',
        'usinenouvelle',
        'ca-paris',
        'afp',
        'voyages-sncf',
        'francecars',
        'koreus',
        'unifrance',
        'agefi',
        'businessimmo',
        '123pages'
    );


    protected $domains_excluded;


    /**
     * SearchEngine constructor.
     */
    public function __construct()
    {

        $this->maps_keys = json_decode(file_get_contents(MAIN_DIR . 'config/maps_keys.json'),true);

        parent::__construct();

    }


    /**
     *
     * Do a saarch

     * @param string $search
     * @param array $domains
     * @param array $domains_added
     * @return array|JsonResponse
     */
    public function search($search,$domains = array(),$domains_added = array())
    {

        $this->domains_excluded  = array_merge($this->_domains_excluded,$domains);

        $this->domains_excluded = array_diff($this->domains_excluded,$domains_added);

        $this->setResults(false);

        $results = $this->rawSearch($search);

        if($results instanceof JsonResponse){
            return $results;
        }

        return $this->getCorrectUrl($results);
    }


    /**
     * @param $query
     * @return mixed
     */
    abstract public function rawSearch($query);


    /**
     *
     * Return if an extension is correct
     *
     * @param $url
     * @return int
     */
    public function isCorrectExtension($url)
    {
        $extractor = new WebsiteExtracter();

        $result = $extractor->parse($url);

        return preg_match(self::EXT,$result->getSuffix());
    }




    /**
     *
     * Return if the url is not in an excluded domain
     *
     * @param $url
     * @param array $domains
     * @return bool
     */
    public function isCorrectDomain($url,$domains = array())
    {
        $extractor = new WebsiteExtracter();

        $result = $extractor->parse($url);

        return !in_array($result->getHostname(),array_merge($this->domains_excluded,$domains));
    }



    /**
     *
     * Search a company on google maps
     *
     * @param string $query
     * @param string|null $address
     *
     * @return string|JsonResponse
     */
    public function search_map($query,$address =  null)
    {

        $key = $this->getMapsKey();

        $q =  array(
            'key' => $key,
            'query' => $query . $address . ' France',
            'language' => 'fr',

        );

        if($address){
            $q['location'] = $this->getLatLng($address);
            $q['radius'] = 50000;

        }



        $response = json_decode($this->do_request(self::maps_search_url, array(
            'query' => $q,
        )),true);


        if($response instanceof JsonResponse){
            return $response;
        }

        if($response['status'] == 'OVER_QUERY_LIMIT'){

            $this->setKeyExpired($key);

            if(!$this->getMapsKey()){

                $r  = new JsonResponse(array(
                    'error' => 'Quota Google Maps Place API Reached',
                    'error_key' => 'maps-quota',

                ),401);
                return $r;
            }else{
                $this->search_map($query,$address);
            }
        }


        if(!isset($response["results"][0])){
            return new JsonResponse(array(
                'error' => "No Google maps result found for {$query}",
                'error_key' => 'result',
            ),404);
        }

        $i = 0;
        $result = $this->search_place($response["results"][$i]['place_id']);

        while($result === false && isset($response["results"][($i+1)])){
            $i++;
            $result  = $this->search_place($response["results"][$i]['place_id']);
        }

        return $result;

    }




    /**
     * Return Google Maps API Key
     *
     * @return string|bool
     */
    public function getMapsKey()
    {


        $keys = $this->maps_keys;

        $key = $keys[rand(0,count($this->maps_keys)-1)];

        $array = $this->getTodayKeyFileData();

        $i = 0;

        while(in_array($key,$array) && $i < 20){
            $key = $keys[rand(0,count($this->maps_keys)-1)];

            $i++;
        }

        if($i == 20){
            return false;
        }else{
            return $key;
        }

    }


    /**
     * Return the expired data file content
     *
     * @return array|mixed
     */
    private function getTodayKeyFileData()
    {
        $date = date('y-m-d');

        $file_name = MAIN_DIR . 'data/keys-' . $date . '.json';

        $array = json_decode(@file_get_contents($file_name),true);

        if( !is_array($array)){
            $array = array();
        }

        return $array;
    }


    /**
     * @param $key
     */
    protected function setKeyExpired($key)
    {
        $date = date('y-m-d');

        $file_name = MAIN_DIR . 'data/keys-' . $date . '.json';

        $data = $this->getTodayKeyFileData();

        $data[] = $key;

        file_put_contents($file_name,json_encode($data));

    }



    /**
     *
     * Return a latlng string if it exist
     *
     * @param $address
     * @return array|mixed|string|JsonResponse
     */
    protected function getLatLng($address)
    {
        $key = $this->getMapsKey();

        $response = $this->do_request('https://maps.googleapis.com/maps/api/geocode/json',array(
            'query' => array(
                'key' => $key,
                'address' => $address,
            )
        ));

        if($response instanceof JsonResponse){
            return $response;
        }

        $response = json_decode($response,true);

        if(!isset($response['results'][0]['geometry'])){
            return new JsonResponse(array(
                'error' => "No Google maps result found for {$address}",
                'error_key' => 'result',
            ),404);
        }

        $geo = $response['results'][0]['geometry']['location'];


        return "{$geo['lat']},{$geo['lng']}";

    }



    /**
     *
     * Get company info from place id
     *
     *
     * @param $id
     * @return string|JsonResponse
     */
    protected function search_place($id)
    {

        $this->domains_excluded = $this->_domains_excluded;


        $response = $this->do_request(self::place_url,array(
            'query' => array(
                'key' => self::getMapsKey(),
                'placeid' => $id,
            )
        ));

        if($response instanceof JsonResponse){
            return $response;
        }

        $response = json_decode($response,true);

        $this->setResults(true);

        if(isset($response['result']['website']) && $response['result']['website'] != ''){
            if(!$this->isCorrectExtension($response['result']['website']) || !$this->isCorrectDomain($response['result']['website'])){
                return false;
            }


        }



        return array(
            'phone' => isset($response['result']['formatted_phone_number']) ? $response['result']['formatted_phone_number'] : '',
            'address' => isset($response['result']['formatted_address']) ? $response['result']['formatted_address'] : '',
            'website' => isset($response['result']['website']) ? $response['result']['website'] : '',
        );

    }



    /**
     *
     * Extract the link from the results
     *
     */
    /**
     * @param \Serps\Core\Serp\ResultSet $results
     * @return array|JsonResponse
     */
    protected function getCorrectUrl($results)
    {


        foreach($results as $key => $result){

            if($result instanceof \Serps\Core\Serp\ResultDataInterface){
                $resultData = $result->getData();
            }else{
                continue;
            }

            if ($this->isCorrectDomain($resultData['url']) && $this->isCorrectExtension($resultData['url'])) {
                $url  = $resultData['url'];
                $title = $resultData['title'];
                break;

            }

        }

        if (!isset($url)) {

            $class = Utils::class_basename($this);

            return new JsonResponse(array(
                'error_key' => 'result',
                'error' => "Extraction failed for {$this->query}, no {$class} results"), 404);
        }


        $this->setResults(true);


        return array(
            'url' => $url,
            'title' => isset($title) ? $title : '',
            'score' => isset($key) ?(100 - $key*10) : 0,
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