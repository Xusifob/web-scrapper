<?php

use LayerShifter\TLDExtract\Extract as WebsiteExtracter;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Response;


use Extractor\Service\Hunter;
use Extractor\Service\Searcher;
use Extractor\Service\Societe;

use Extractor\HttpFoundation\CSVResponse;

use Extractor\SearchEngine\Google;
use Extractor\SearchEngine\SearchEngine;
use Extractor\Service\Utils;

use \Extractor\Exception\JsonEncodedException;

use Extractor\Traits\NameParser;




/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * @since 1.0.0
 *
 * This class is made to extract some data from a company and export it into a folder as a JSON file
 *
 * Class Extractor
 */
class Extractor implements JsonSerializable
{


    use NameParser;


    /**
     * @var string
     */
    public $siren;


    /**
     *
     * The first name of the guy you export
     *
     * @var string
     */
    public $first_name;



    /**
     *
     * The last name of the guy you export
     *
     * @var string
     */
    public $last_name;


    /**
     * @var string
     */
    protected $company;


    /**
     * @var string
     */
    public $position;


    /**
     * @var string
     */
    protected $linkedin;


    /**
     * @var string
     */
    protected $phone;



    /**
     * @var int
     */
    protected $creance;


    /**
     * @var string
     */
    protected $date_bilan;


    /**
     * @var string
     */
    protected $description;


    /**
     * @var string
     */
    protected $dir = 'export';


    /**
     * @var int
     */
    protected $turnover;



    /**
     * @var SearchEngine
     */
    protected $searchEngine;


    /**
     * @var Hunter
     */
    protected $hunter;


    /**
     * @var Searcher
     */
    protected $searcher;


    /**
     * @var string
     */
    protected $from  = 'listing';


    /**
     *
     *
     * @var string
     */
    protected $website = array(
        'domain' => '',
        'extension' => ''
    );


    /**
     * @var array
     */
    public $score = array(
        'email' => 0,
        'search' => 100,
    );


    /**
     * @var string
     */
    public $email;


    /**
     * @var null|JsonResponse
     */
    public $response;


    /**
     * @var string
     */
    protected $address;


    /**
     * Extractor constructor.
     * @param SearchEngine|null $searchEngine
     * @param Searcher|null $searcher
     * @param Hunter|null $hunter
     */
    public function __construct(SearchEngine $searchEngine = null,Searcher $searcher =  null,Hunter $hunter =  null)
    {
        if($searchEngine){
            $this->searchEngine = $searchEngine;
        }else{
            $this->searchEngine = new Google();
        }

        if($searchEngine){
            $this->searcher = $searcher;
        }else{
            $this->searcher = new Searcher();
        }

        if($hunter){
            $this->hunter = $hunter;
        }else{
            $this->hunter = new Hunter();
        }


    }


    /**
     * Extractor constructor.
     */
    public function extract()
    {

        $this->parseRequest();


        if($this->email && $this->first_name && $this->last_name && $this->company){
            $this->save();
            return;
        }

        $this->getWebsiteFromPreviousScrapping();
        $this->getGoogleMapsInfo();
        $this->getWebsiteFromCompanyName();


    }


    /**
     * Extract the name and email
     *
     */
    public function extractPersonNameAndEmail()
    {

        $this->findName();

        $this->findEmail();

        $this->save();
    }


    /**
     *  Parse the request
     */
    protected function parseRequest(){
        $this->setCompany();
        $this->setAddress();
        $this->setWebsite();
        $this->setLastName();
        $this->setFirstName();
        $this->setPhone();
        $this->setPosition();
        $this->setDescription();
        $this->setEmail();
        $this->setApiKey();
        $this->setDir();
        $this->setLinkedin();
    }


    /**
     *
     * Get the company website from previous data
     *
     */
    public function getWebsiteFromPreviousScrapping()
    {


        if($this->getWebsite()){
            return;
        }

        $result = $this->searcher->doSearch(array('company' => $this->company));



        if(empty($result)){
            return;
        }




        if(isset($result[0]['email'][1])){
            $domain = explode('@',$result[0]['email'])[1];
            $this->setWebsite($domain);
            $this->setScore('search',isset($result[0]['score']['score_search'])? $result[0]['score']['score_search'] :95);

        }


    }




    /**
     * get infos of a company from Google Maps
     */
    protected function getGoogleMapsInfo()
    {
        if($this->hasError()){
            return;
        }

        $response = $this->searchEngine->search_map($this->company,$this->address);


        if($response instanceof JsonResponse){
            if(json_decode($response->getContent(),true)['error_key'] == 'maps-quota'){
                $this->response = $response;
            }
            return;

        }


        $this->setPhone($response['phone']);
        if(!$this->getWebsite()){
            $this->setWebsite($response['website']);
            $this->setScore('search',90);

        }
        if($response['address']){
            $this->setAddress($response['address']);
        }

    }



    /**
     *
     * Find an email according to the data collected
     *
     */
    public function findEmail()
    {

        if($this->hasError()){
            return;
        }




        $query = array();

        $query['company'] = $this->getCompany();
        $query['first_name'] = $this->getFirstName();
        $query['last_name'] = $this->getLastName();
        $query['website'] = $this->getWebsite();


        try{


            if(!$this->getFirstName() || !$this->getWebsite() || !$this->getLastName()){
                throw new JsonEncodedException(array(
                    'error_key' => 'search',
                    'company' => $this->company,
                    'error' => "First name, Last name or domain is missing",
                    'code' => 400,
                ));
            }



            $re_scrap = Utils::extractFromRequest('re_scrap');

            if(!$re_scrap){
                $search = $this->searcher->doSearch($query);


                if(count($search) > 0){

                    throw new JsonEncodedException(array(
                        'error_key' => 'already_done',
                        'company' => $this->company,
                        'error' => "{$this->first_name} {$this->last_name} Company {$this->company} already extracted in folder {$search[0]['dir']}",
                        'code' => 400,
                    ));


                }
            }


            $result = $this->hunter->findEmail($this->getFirstName(),$this->getLastName(),$this->getWebsite());

            $this->email = $result['email'];
            $this->setScore('email',$result['score']);


            if(!$re_scrap){
                $search = $this->searcher->doSearch(array('email' => $this->getEmail()));

                if(count($search) > 0){

                    throw new JsonEncodedException(array(
                        'error_key' => 'already_done',
                        'company' => $this->company,
                        'error' => "{$this->getEmail()} from {$this->company} already extracted in folder {$search[0]['dir']}",
                        'code' => 400,
                    ));


                }
            }


        }catch (JsonEncodedException $e){
            $this->setResponseFromException($e);
        }

    }




    /**
     * Go get the name from Tech Crunch or Societe.com
     */
    protected function findName()
    {

        // First & last name are already set
        if($this->first_name && $this->last_name || $this->hasError()){
            return;
        }


        if(Utils::extractFromRequest('scrap_ceo')){
            if(isset($_GET['societe_only'])){
                $this->findInfosFromSociete();
            }else{
                $data = CrunchBaseSearch($this->jsonSerialize());

                if(isset($data['first_name'])){
                    $this->setFirstName($data['first_name']);
                    $this->setLastName($data['last_name']);
                    $this->setDescription($data['description']);
                    $this->setPosition('CEO');
                }else{
                    $this->findInfosFromSociete();
                }
            }
        }
    }


    /**
     *
     */
    public function findInfosFromSociete()
    {


        $societe = new Societe();

        $c = trim(preg_replace('/\(.+\)/','',$this->getCompany()));

        //  $url = $societe->search($c,$this->getAddress());


        //    if($url instanceof JsonResponse){

        $result = $this->searchEngine->search("site:societe.com/societe/ {$this->company}  $this->address $this->siren",array(),array('societe'));

        if($result instanceof JsonResponse){
            sleep(2);
            $result = $this->searchEngine->search("site:societe.com/societe/ {$this->company} $this->siren",array(),array('societe'));
        }


        if($result instanceof JsonResponse){
            $this->response = $result;
            return;

        }

        $percent = $this->calculateCoherence($result);


        // If coherence limited or page of a list, start again !
        if ($percent < 30 || preg_match('/societe\.com\/liste-/', $result['url'])) {
            sleep(2);
            $result = $this->searchEngine->search("site:societe.com/societe/ {$this->company}",array(),array('societe'));

            if($result instanceof JsonResponse){

                $this->response = $result;
                return;

            }

        }


        $url = $result['url'];

        //  }

        if($url instanceof JsonResponse){

            if(!$this->first_name) {

                $this->response = $url;
                return;
            }else{
                return;
            }
        }


        $data = $societe->parse($url);

        if($data instanceof JsonResponse){

            if(!$this->first_name) {

                $this->response = $data;
                return;
            }else{
                return;
            }
        }


        if(!$this->description){
            $this->setDescription($data['description']);
        }


        $this->setTurnover($data['turnover']);


        if(!($this->first_name)){
            $this->setFirstName($data['first_name']);
            $this->setLastName($data['last_name']);
            $this->setFrom('Societe.com');
        }

        if(!$this->position){
            $this->setPosition($data['position']);
        }

        if(isset($data['creances'])){
            $this->setCreance($data['creances']);
        }

        if(isset($data['date_du_bilan'])){
            $this->setDateBilan($data['date_du_bilan']);
        }
        if(isset($data['siren'])){
            $this->setSiren($data['siren']);
        }


    }


    /**
     * @param $result
     */
    private function calculateCoherence($result)
    {

        $text = trim(preg_replace('/(\.\.\.|r(é|e)sultat|Chiffre d\'affaires,?|,|bilans( sur)?|\(.+\)|-|(s|S)ociete\.com)/', '', $result['title']));

        // Calculate coherence
        similar_text(strtolower($text), strtolower($this->company), $percent);

        return $percent;
    }




    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }




    /**
     * @param string $first_name
     */
    public function setFirstName($first_name = null)
    {
        if(!($first_name)){
            $first_name = Utils::extractFromRequest(array('fname','first_name','firstName'));
        }


        if(!$first_name ){
            $full_name = Utils::extractFromRequest(array('fullname','full_name','fullName'));

            if($full_name){

                $result = $this->parseName($full_name);

                if($result){

                    $first_name = $result['first_name'];
                    $this->setLastName($result['last_name']);
                }
            }
        }

        $this->first_name = trim(ucfirst($first_name));

    }



    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }



    /**
     * @param string $last_name
     */
    public function setLastName($last_name = null)
    {
        if(!($last_name)){
            $last_name = Utils::extractFromRequest(array('lname','last_name','lastName'));
        }


        $this->last_name = trim(ucfirst($last_name));
    }




    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }





    /**
     * @param string $company
     */
    public function setCompany($company = null)
    {

        if(!$company){
            $company = Utils::extractFromRequest(array('name','company'));
        }

        if(!$company){

            $response = new JsonResponse(array(
                    'error_key' => 'query',
                    'company' => '',
                    'error' => "Please set a company name"
                )
                ,400);

            $this->response = $response;
            return;
        }




        $this->company = ucfirst(trim(preg_replace('/( SARL| SAS)/i',' ',$company)));

    }






    /**
     * @param $key
     * @return string
     */
    public function getWebsite($key = '')
    {
        if($key && isset($this->website[$key])){
            return $this->website[$key];
        }

        if(!$this->website['domain'] || !$this->website['extension']){
            return false;
        }

        else{
            return $this->website['domain'] . '.' . $this->website['extension'];
        }
    }

    /**
     * @return int
     */
    public function getTurnover()
    {
        return $this->turnover;
    }

    /**
     * @param int $turnover
     */
    public function setTurnover($turnover)
    {
        if(!($turnover)){
            $turnover = Utils::extractFromRequest(array('turnover','CA','Chiffre d\'affaire'));
        }

        if($turnover && !empty($turnover)){
            $this->turnover = $turnover;
        }
    }



    /**
     * @param int $linkedin
     */
    public function setLinkedin($linkedin = null)
    {
        if(!($linkedin)){
            $linkedin = Utils::extractFromRequest(array('linkedin'));
        }

        if($linkedin && !empty($linkedin)){
            $this->linkedin = $linkedin;
            $this->from = 'linkedin';
        }
    }







    /**
     * @param string $website
     */
    public function setWebsite($website = null)
    {
        if(!($website)){
            $website = Utils::extractFromRequest(array('url','domain','website'));
        }

        if(!$website || $website == ''){
            return;
        }

        $extractor = new WebsiteExtracter();


        $result = $extractor->parse($website);

        $this->website['domain'] = $result->getHostname();
        $this->website['extension'] = $result->getSuffix();


        $this->setScore('search',100);


    }


    /**
     * Retrieve the website from the database
     *
     */
    private function getWebsiteFromCompanyName()
    {


        if($this->getWebsite() || $this->hasError()){
            return;
        }



        $result = $this->searchEngine->search($this->company,array('societe'));

        if($result instanceof JsonResponse){
            $this->response = $result;
            return;
        }



        $this->setScore('search',($result['score']*7 + $this->calculateSimilarity()*3)/10);


        $this->setWebsite($result['url']);
    }


    /**
     * @param $array
     * @return null|string
     */
    protected static function extractFromRequest($array)
    {


        foreach($_GET as $key => $value){
            if(in_array($key,$array) && !empty($value)){
                return $value;
                break;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position = null)
    {
        if(!($position)){
            $position = Utils::extractFromRequest(array('job','position','title'));
        }

        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description = null)
    {
        if(!($description)){
            $description = Utils::extractFromRequest(array('description'));
        }

        $this->description = $description;
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
    public function setDir($dir = null)
    {
        if(!($dir)){
            $dir = Utils::extractFromRequest(array('dir','directory','folder'));
        }

        if($dir){
            $this->dir = $dir;
        }
    }







    /**
     * @param null $api_key
     * @param string $email
     */
    public function setApiKey($api_key = null,$email = '')
    {


        if(!($api_key)){
            $api_key = Utils::extractFromRequest(array('api_hunter','api_key','apiKey'));
        }

        if(!($email)){
            $email = Utils::extractFromRequest(array('api_hunter_email'));
        }


        if(!$api_key){
            return;
        }

        try{
            $this->hunter->setApiKey($api_key,$email);
        }catch (JsonEncodedException $e){
            $this->setResponseFromException($e);
        }

    }


    /**
     *
     * Set the response
     *
     * @param JsonEncodedException $e
     */
    protected function setResponseFromException(JsonEncodedException $e)
    {

        $message = $e->getDecodedMessage();
        $this->response = new JsonResponse($message,isset($message['code']) ? $message['code'] : 500);
    }



    /**
     * Save the data inside a json file
     */
    public function save()
    {
        if($this->hasError()){
            return;
        }


        $dir = __DIR__ . "/../../exports/{$this->dir}/";

        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir,0777);
        }
        $file_name = $dir  . str_replace('/','',base64_encode($this->company . $this->email)) . '.json';

        file_put_contents($file_name, json_encode($this, JSON_PRETTY_PRINT));
        chmod($file_name,0777);
    }




    /**
     * @return array
     */
    public function jsonSerialize()
    {

        if($this->hasError()){
            return json_decode($this->response->getContent(),true);
        }

        return array(
            'company' => strtoupper($this->getCompany()),
            'website' => $this->getWebsite(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'title' => $this->getPosition(),
            'description' => $this->getDescription(),
            'email' => $this->getEmail(),
            'Chiffre D\'affaire' => $this->getTurnover(),
            'score' => $this->getScore(),
            'date' => $this->getDateBilan(),
            'creances' => $this->getCreance(),
            'phone' => $this->getPhone(),
            'linkedin' => $this->getLinkedin(),
            'address' => $this->getAddress(),
            'siren' => $this->getSiren(),
            'from' => $this->getFrom(),
            'score_search' => $this->getScore('search'),
            'score_email' => $this->getScore('email'),
        );
    }


    /**
     *
     * Export to excel
     *
     */
    public function export()
    {
        $this->setDir();

        $dir = __DIR__ . "/../../exports/$this->dir";

        if(!is_dir($dir)){
            $response = new JsonResponse("Le dossier {$this->dir} n'existe pas",400);
            $this->response = $response;
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


        usort($data, array($this,'usort'));



        if(empty($data) || !isset($data[0])){
            $response = new JsonResponse(array(
                'error' => 'Incorrect data',
                'error_key' => 'data',
                'data' => $data,

            ),500);
            $this->response = $response;
            return;
        }





        $response = new CSVResponse($data);
        $response->setFilename("export-{$this->dir}.csv");

        $this->response = $response;
        return;

    }


    /**
     * @param $a
     * @param $b
     * @return mixed
     */
    public function usort($a,$b)
    {
        return $a["company"] - $b["company"];

    }


    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone = null )
    {

        if(!($phone)){
            $phone = Utils::extractFromRequest(array('phone','tel','Téléphone'));
        }

        $this->phone = $phone;

    }



    /**
     * @return mixed
     */
    public function getCreance()
    {
        return $this->creance;
    }

    /**
     * @param mixed $creance
     */
    public function setCreance($creance)
    {
        $this->creance = $creance;
    }


    /**
     *
     * Return the general score or a specific one
     *
     * @param string $score_key
     * @return array|float
     */
    public function getScore($score_key = '')
    {


        if($score_key && isset($this->score[$score_key])){
            return $this->score[$score_key];
        }




        if(isset($this->score['search'])){
            return
                $this->getScore('search')*3 +
                $this->getScore('email');
        }

        return $this->score;

    }

    /**
     * @param string $key
     * @param int $score
     */
    public function setScore($key,$score)
    {
        $this->score[$key] = $score;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email = null )
    {

        if(!($email)){
            $email = Utils::extractFromRequest(array('email','mail','E-mail'));
        }

        $this->email = $email;

    }



    /**
     * @return string
     */
    public function getDateBilan()
    {
        return $this->date_bilan;
    }

    /**
     * @param string $date_bilan
     */
    public function setDateBilan($date_bilan)
    {
        $this->date_bilan = $date_bilan;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address = null)
    {
        if(!($address)){
            $address = Utils::extractFromRequest(array('address','city','zip_code'));
        }

        $this->address = $address;
    }


    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->response instanceof JsonResponse;
    }

    /**
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }


    /**
     * @return mixed
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * @param mixed $siren
     */
    public function setSiren($siren)
    {

        if(!($siren)){
            $siren = Utils::extractFromRequest(array('siren'));
        }
        $this->siren = $siren;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from = null)
    {
        if(!($from)){
            $from = Utils::extractFromRequest(array('from','dir'));
        }

        $this->from = $from;
    }









    /**
     * Display the result of the research
     */
    public function display()
    {

        if($this->response instanceof Response){

            if($this->response instanceof JsonResponse){
                $d = json_decode($this->response->getContent(),true);


                if(is_array($d)){
                    $d['company'] = $this->company;
                    $d['trace'] = $this->jsonSerialize();
                    $this->response->setData($d);
                }
            }


            $this->response->send();
            die();


        }

        $response =  new JsonResponse($this);
        $response->setEncodingOptions(JSON_PRETTY_PRINT);
        $response->send();
        die();
    }


    /**
     * called when cloning the extractor
     *
     */
    public function __clone()
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->position = '';
        $this->response = null;
        $this->setScore('email',0);

    }


    /**
     *
     * Calculate the smilarity between the website and the company
     *
     * @return float|int
     */
    protected function calculateSimilarity()
    {

        if(!$this->getWebsite('domain') || !$this->getCompany()){
            return 0;
        }


        return Utils::similarity($this->getWebsite('domain'),$this->getCompany())*100;

    }

}