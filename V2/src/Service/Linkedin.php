<?php

namespace Extractor\Service;


use \Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;
use \GuzzleHttp\Cookie\FileCookieJar;


use Extractor\SearchEngine\SearchEngine;
use Extractor\SearchEngine\Google;

use Extractor\Traits\NameParser;


/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * This class is to do a Linkedin search
 *
 * Class Linkedin
 */
class Linkedin extends RequestDoer
{


    use NameParser;


    /**
     *
     * Sites to do the search on
     *
     * @var string[]
     */
    protected $sites = array(
        'fr.linkedin.com/in/',
        'www.linkedin.com/in/'
    );


    /**
     *
     * Keywords to remove
     *
     * @var string[]
     */
    protected $removed = array(
        '/recherche/',
        '/search/',
        '/pub/',
    );


    /**
     *
     * All job to look for
     *
     * @var string[]
     */
    protected $jobs = array(
        'Director',
        'CEO',
        'CFO',
        'DAF',
        'chief financial officer',
        'Président',
        'Dirécteur',
        'Dirécteur Financier',
        'Dirécteur Général'
    );


    /**
     * All jobs you try to av
     *
     * @var array
     */
    protected $wrong_job = array(
        'CTO',
        'Technical',
        'Ressources Humaines',
    );





    /**
     *
     * The Google Query
     *
     * @var string
     */
    protected $query ;


    /**
     *
     * The Company you try to scrapp
     *
     * @var string
     */
    protected $company;


    /**
     *
     * Countries
     *
     * @var array
     */
    protected $countries = array(
        'France',
    );


    /**
     * @var CookieJar;
     */
    protected $jar;



    /**
     * Linkedin constructor.
     */
    public function __construct()
    {

        $this->jar = new FileCookieJar(MAIN_DIR . 'data/cookies.json');
        ;

    }


    /**
     *
     * Login to Linkedin
     *
     * @THIS FUNCTION DOES NOT WORK YET
     *
     */
    protected function login()
    {
        $login_page = $this->getHtml('https://www.linkedin.com/',array(
            'cookies' => $this->jar,
        ));


        $loginCsrfParam = $login_page->find('#loginCsrfParam-login')[0]->getAttribute('value');
        $sourceAlias = $login_page->find('#sourceAlias-login')[0]->getAttribute('value');

        $client = new Client(array(
            'cookies' => true,
        ));


        $response = $client->post('https://www.linkedin.com/uas/login-submit',array(
            'cookies' => $this->jar,
            'form_params' => array(
                'session_key' => 'b.malahieude@free.fr',
                'session_password' => 'dcb8ae282',
                'isJsEnabled' => 'false',
                'loginCsrfParam' => $loginCsrfParam,
                'sourceAlias' => $sourceAlias,
            )
        ));


        vardump($response->getBody()->getContents());

    }


    /**
     *
     * Do a search on the websit
     *
     * THIS FUNCTION DOES NOT WORK YET
     * @param $search
     */
    public function searchOnSite($search)
    {

        $this->login();



        die();

    }


    /**
     *
     * @param $company
     * @param SearchEngine|null $search_engine
     * @return array|JsonResponse
     */
    public function search($company,SearchEngine $search_engine = null)
    {

        $this->company = $company;

        $this->buildQuery($company);


        sleep(1);

        if(!$search_engine){
            $search_engine = new Google();
        }

        $results = $search_engine->rawSearch($this->query);

        sleep(1);

        if($results instanceof JsonResponse){
            return $results;
        }


        return $this->parseSearch($results);

    }



    protected function buildQuery($company)
    {
        $this->query = '"'. $company .'"';

        $this->query .= ' AND (';
        foreach($this->sites as $key => $site){
            $this->query .= "site:$site ";
            if($key != (count($this->sites) -1) ){
                $this->query .= ' OR ';
            }
        }
        $this->query .= ') AND (';
        foreach($this->jobs as $key => $job){
            $this->query .= "\"$job\"";
            if($key != (count($this->jobs) -1) ){
                $this->query .= ' OR ';
            }
        }
        $this->query .= ') AND (';
        foreach($this->countries as $key => $country){
            $this->query .= "\"$country\"";
            if($key != (count($this->countries) -1) ){
                $this->query .= ' OR ';
            }
        }
        $this->query .= ') ';

    }



    /**
     * @param \Serps\Core\Serp\IndexedResultSet  $results
     * @return array
     */
    protected function parseSearch($results)
    {

        $users = array();

        foreach($results as $result)
        {
            $user = array();


            if(!($subtitle = $result->getData()['subtitle'])){
                continue;
            }
            if(!($title = $result->getData()['title'])){
                continue;
            }
            if(!($url = $result->getData()['url'])){
                continue;
            }

            $position = $this->parseSubtitle($subtitle);


            if(!$position){
                continue;
            }else{
                $user['position'] = $position['position'];
            }

            $user  = array_merge($user,$this->parseTitleName($title));

            $user['linkedin'] = $url;


            $users[] = $user;

        }


        if(count($users) == 0){
            return new JsonResponse(array(
                'error_key' => 'error',
                'error' => 'No one found on linkedin',
            ),404);
        }


        return $users;
    }


    /**
     * @param $title
     * @return array
     */
    protected function parseTitleName($title)
    {

        $name = trim(explode('|',$title)[0]);

        return $this->parseName($name);
    }



    /**
     *
     * Parse the subtitle
     *
     * @param $subtitle
     * @return array|bool
     */
    protected function parseSubtitle($subtitle)
    {
        $data = preg_split('/(·| \| | , | - | @ | at | chez )/',$subtitle);



        $company_ok = false;
        $job_ok = false;

        $job_found = '';


        foreach($data as $d)
        {
            if(strpos(strtolower($d),strtolower($this->company)) !== false){
                $company_ok = true;
            }

            foreach ($this->jobs as $job)
            {

                if(strpos(strtolower($job),strtolower($d)) !== false){
                    $job_ok = true;
                    $job_found = trim(str_replace($this->company,'',$d));
                }
            }

            foreach ($this->wrong_job as $job)
            {
                if(strpos(strtolower($d),strtolower($job)) !== false){
                    $job_ok = false;
                }
            }

        }




        if($job_ok && $company_ok){
            return array(
                'position' => $job_found,
            );
        }else{
            return false;
        }

    }

}