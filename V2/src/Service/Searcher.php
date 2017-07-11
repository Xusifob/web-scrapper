<?php

namespace Extractor\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Extractor\Service\Utils;



/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * Class Searcher
 * @package Extractor\Service
 */
class Searcher
{


    /**
     *
     * The DB containing all the infos
     *
     * @var array
     */
    protected $db = array();



    /**
     * Searcher constructor.
     */
    public function __construct()
    {
        $this->buildDb();
    }


    /**
     *
     * Do a search
     *
     * @return JsonResponse
     */
    public function search()
    {

        $data = $this->doSearch($_GET);
        $response = new JsonResponse($data);
        $response->setEncodingOptions(JSON_PRETTY_PRINT);

        return $response;
    }


    /**
     *
     * Build the database
     *
     */
    private function buildDb()
    {

        if(!empty($this->db)){
            return;
        }



        $d = Utils::csv_to_array(MAIN_DIR  . '/data/contacts.csv',',');


        foreach($d as $du)
        {

            $email = Utils::extractFromRequest(array('email','e-mail','Email(default)','Email(Work)','Email(Personal)'),$du);

            $domain = null;
            if(isset(explode('@',$email)[1])){
                $domain = explode('@',$email)[1];
            }

            if(isset($du['Company'])){
                $this->db[] = array(
                    'company' => trim($du['Company']),
                    'first_name' => trim($du['First Name']),
                    'last_name' => trim($du['Last Name']),
                    'title' => trim($du['Title']),
                    'website' => $domain,
                    'email' => $email,
                    'dir' => 'agile',
                    'score_search' => '',

                );
            }
        }


        $dirs = array();

        $tmp = scandir( MAIN_DIR . '/../exports/');

        foreach($tmp as $d){
            if(is_dir( MAIN_DIR . "/../exports/$d") and $d != "." && $d != ".."){
                $dirs[] = $d;
            }
        }


        foreach($dirs as $my_dir) {
            $dir = MAIN_DIR . "/../exports/$my_dir";


            if ($handle = opendir($dir)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        $d = json_decode(file_get_contents($dir . '/' . $entry), true);

                        $position = '';
                        if(isset($d['position'])){
                            $position = $d['position'];
                        }

                        if(isset($d['title'])){
                            $position = $d['title'];
                        }


                        $this->db[] = array(
                            'company' => $d['company'],
                            'first_name' => $d['first_name'],
                            'last_name' => $d['last_name'],
                            'website' => $d['website'],
                            'email' => $d['email'],
                            'position' => $position,
                            'dir' => $my_dir,
                            'score_search' => isset($d['score_search']) ? $d['score_search'] : '',
                        );
                    }
                }
                closedir($handle);
            }
        }

        unset($dirs);
    }


    /**
     *
     * Do a search
     *
     * @param array $query  The data you're looking for
     * @return array
     */
    public function doSearch($query)
    {

        $data = array();


        foreach($this->db as $entry)
        {
            if($this->matchSearch($query,$entry['company'],$entry['first_name'],$entry['last_name'],$entry['website'],$entry['email'])){
                $data[] = $entry;
            }
        }

        return $data;
    }





    /**
     *
     * Return if a search matches the criteria
     *
     * @param array $data
     * @param string|null $company
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $domain
     * @param string|null $email
     * @return bool|int
     */
    private function matchSearch($data,$company,$firstName  = null,$lastName = null,$domain = null,$email = null)
    {
        $match = false;


        if(isset($data['company']) && $company){
            $c = trim(str_replace('/','\/',$data['company']));

            $match = preg_match("#^{$c}#i",$company);

            if(!$match){
                return false;
            }
        }

        if(isset($data['first_name']) && $firstName){
            $match = preg_match("#{$data['first_name']}#i",$firstName);

            if(!$match){
                return false;
            }
        }

        if(isset($data['website']) && $domain){
            $match = preg_match("#{$data['website']}#i",$domain);

            if(!$match){
                return false;
            }
        }


        if(isset($data['last_name']) && $lastName){
            $match = preg_match("/{$data['last_name']}/i",$lastName);

            if(!$match){
                return false;
            }
        }

        if(isset($data['email']) && $email){
            $match = preg_match("/{$data['email']}/i",$email);

            if(!$match){
                return false;
            }
        }


        return $match;
    }



}