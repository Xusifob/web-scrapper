<?php

include __DIR__ . '/src/header.php';


/**
 *
 * @deprecated
 *
 * Class Angel
 */
class Angel extends Scrapper implements ScrapperInterface
{


    /**
     * The export directory
     *
     * @var string
     */
    private $dir = 'angel';


    const ID_FILE = '/../tmp/ids.json';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;


    /**
     *
     * List of ids of companies you're looking for
     *
     * @var array
     */
    protected $ids;


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
        $this->getCompanyData();
    }


    /**
     *
     * Login the user
     *
     */
    public function login()
    {

        $response = $this->client->get('login');

        $html = $response->getBody()->getContents();

        $dom = new PHPHtmlParser\Dom();

        $dom->load($html);

        $a = $dom->find('meta[name="csrf-token"]')->getAttribute('content');


        $this->client->post('users/login',array(
            'form_params' => array(
                'login_only' => true,
                'user' => array(
                    'email' => 'b.malahieude@free.fr',
                    'password' => 'dcb8ae282',
                ),
                'authenticity_token' => $a,
            )
        ));
    }


    /**
     *
     */
    public function getStartupIds()
    {
        try {
            $response = $this->client->post('job_listings/startup_ids', array(
                'form_params' => array(
                    'tab' => 'find',
                    'filter_data' => array(
                        'company_size' => '51-200',
                        'locations[]' => '1717-France',
                        //  'company_stage' => 'Series A',
                    )
                ),
                'headers' => array(
                    'Host' => 'angel.co',
                    'Origin' => 'https//angel.co',
                    'Referer' => 'https://angel.co/jobs',
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                    'X-CSRF-Token' => 'Lp2kf8K6vdZBtEmLrxx7SdrmR7z34wnbzB8BxF4292o=',
                    'X-Requested-With' => 'XMLHttpRequest',
                )
            ));


            $data =  json_decode($response->getBody()->getContents(),true);

            $this->setIds($data['ids']);

            return $data['ids'];


        }catch (\GuzzleHttp\Exception\ClientException $e){
            echo $e->getMessage();
        }
        catch (\GuzzleHttp\Exception\ServerException $e){
            echo $e->getMessage();
        }
    }


    /**
     * @param $ids
     */
    public function setIds($ids)
    {
        $this->ids = $ids;

        if(!is_dir(dirname(__DIR__ . self::ID_FILE))){
            mkdir(dirname(__DIR__ . self::ID_FILE));
        }

        file_put_contents(__DIR__ . self::ID_FILE,json_encode($ids,JSON_PRETTY_PRINT));
        chmod(__DIR__ . self::ID_FILE,0777);
    }


    /**
     * @return string
     */
    public function getIds()
    {
        if(!file_exists(__DIR__ . self::ID_FILE)){
            return $this->getStartupIds();
        }else{
            return json_decode(file_get_contents(__DIR__ . self::ID_FILE),true);
        }
    }


    public function getCompanyData()
    {

        $ids = array_chunk($this->getIds(),20)[15];

        $query = '';

        foreach($ids as $id){
            $query .= "startup_ids[]=$id&";
        }

        try {
            $response = $this->client->get('job_listings/browse_startups_table', array(
                'query' => $query,
                'headers' => array(
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                )
            ));


            $this->parseCompanyResponse($response->getBody()->getContents());




        }catch (\GuzzleHttp\Exception\ClientException $e){
            echo $e->getRequest()->getUri();
            echo $e->getResponse()->getBody();
        }
    }


    /**
     * @param $response
     */
    protected function parseCompanyResponse($response)
    {
        $dom = new \PHPHtmlParser\Dom();

        $dom->load($response);

        $startups = $dom->find('.browse_startups_table_row');

        /** @var \PHPHtmlParser\Dom\AbstractNode $startup */
        foreach ($startups as $startup) {

            try {

                $company = array();

                $website = $startup->find('.website-link')->getAttribute('href');

                if(!preg_match('/bit\.ly/',$website)){
                    $company['website'] = $website;
                }
                $company['name'] = $startup->find('.startup-link')->text;


                try {
                    if ($startup->find('.description')) {
                        $company['description'] = $startup->find('.description')->text;
                    }
                }catch (\PHPHtmlParser\Exceptions\EmptyCollectionException $e){
                    $company['description'] = '';
                }

                $team = $startup->find('.team .content .person');

                foreach ($team as $person) {
                    $position = $person->find('.name .title')->text;

                    if (preg_match('/(CEO|Founder|CFO|Financial)/i', $position) && !preg_match('/CTO/i', $position)) {
                        $parser = new FullNameParser();

                        $p = $parser->parse($person->find('.name .profile-link')->text);

                        $company['firstName'] = $p['fname'];
                        $company['lastName'] = $p['lname'];
                        $company['job'] = trim(preg_replace('/(&middot;)/i', '', $position));
                    }

                    echo '<pre>';
                    echo $this->extract($company);
                    echo '</pre>';

                }
            }catch (\PHPHtmlParser\Exceptions\EmptyCollectionException $e){
                echo $e->getMessage() . ' ' . $e->getTraceAsString();
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


$scrapper = new Angel();

$scrapper->parse();