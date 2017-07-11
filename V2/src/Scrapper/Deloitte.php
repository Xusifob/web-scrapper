<?php

include __DIR__ . '/src/header.php';


/**
 *
 * @deprecated
 *
 * Class Deloitte
 */
class Deloitte extends Scrapper implements ScrapperInterface
{


    /**
     * The export directory
     *
     * @var string
     */
    private $dir = 'deloitte_es';



    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;


    protected $file = __DIR__ . '/data/deloitte.csv';

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client(array(
            'base_uri' => "https://angel.co/",
            'cookies' => true,
            'allow_redirects' => true,
        ));

    }

    /**
     *
     */
    public function parse()
    {
        $data = csv_to_array($this->file,',');

        $parser = new FullNameParser();


        $tmp = array();

        foreach($data as $d){
            if(trim($d['Country']) == "Spain"){
                $tmp[] = $d;
            }
        }

        $data = $tmp;

        foreach($data as $d){

            $leaders = preg_split('/(-|&|\.)/',$d['Company Leaders']);

            foreach($leaders as $leader){
                $name = $parser->parse($leader);


                $query = http_build_query(array(
                    'company' => $d['Company Name'],
                    'first_name' => $name['fname'],
                    'last_name' => $name['lname']
                ));


                $result = json_decode(file_get_contents('http://localhost/annee_4/tests/500/V2/search.php?' . $query),true);
                // Already a match
                if(count($result) > 0 ){
                    continue;
                }


                // Already a match
                echo '<pre>';
                var_dump($this->extract(array(
                    'first_name' => $name['fname'],
                    'last_name' => $name['lname'],
                    'company' => $d['Company Name'],
                    'website' => $d['website'],
                    'position' => 'CEO'

                )));
                echo '</pre>';

            }

        }

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



}


$scrapper = new Deloitte();

$scrapper->parse();