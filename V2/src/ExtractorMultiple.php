<?php

use \Symfony\Component\HttpFoundation\JsonResponse;


use \Extractor\Service\Searcher;
use \Extractor\Service\Linkedin;
use \Extractor\Service\Verif;
use Extractor\Service\Utils;

use Extractor\SearchEngine\Bing;
use Extractor\SearchEngine\Google;
use Extractor\SearchEngine\SearchEngine;

use \Extractor\Exception\JsonEncodedException;


/**
 *
 * @since 1.0.0
 *
 * This class is made to extract some data from a company and export it into a folder as a JSON file
 *
 * Class Extractor
 */
class ExtractorMultiple implements JsonSerializable
{


    /**
     * @var array
     */
    protected $data = array();


    /**
     * @var Extractor
     */
    protected $extractor;


    /**
     * @var SearchEngine
     */
    protected $searchEngine;


    /**
     * Extractor constructor.
     */
    public function extract()
    {


        $this->setSearchEngine();


        $this->extractor  = new Extractor($this->searchEngine,new Searcher());

        $this->extractor->extract();

        if($this->extractor->hasError()){
            $this->extractor->display();
        }


        $this->extractor->extractPersonNameAndEmail();
        $this->data[] = $this->extractor;



        if(true == Utils::extractFromRequest(array('scrap_linkedin','scrap_verif'))){
            // if(!$this->extractor->getFirstName() || !$this->extractor->getLastName()){


            $users = $this->getInfosFromVerif($this->extractor->getCompany(),$this->extractor->getSiren());

            if(!($users instanceof JsonResponse)){
                foreach($users as $user)
                {
                    $extractor = clone $this->extractor;


                    $extractor->setFirstName($user['first_name']);
                    $extractor->setLastName($user['last_name']);
                    $extractor->setPosition($user['position']);
                    $extractor->setFrom('Verif');

                    $extractor->extractPersonNameAndEmail();
                    $this->data[] = $extractor;

                }
            }


            /**
            $users = $this->getInfosFromLinkedin($this->extractor->getCompany());

            if(!($users instanceof JsonResponse)){
            foreach($users as $user)
            {
            $extractor = clone $this->extractor;


            $extractor->setFirstName($user['first_name']);
            $extractor->setLastName($user['last_name']);
            $extractor->setPosition($user['position']);
            $extractor->setLinkedin($user['linkedin']);
            $extractor->setFrom('Linkedin');



            $extractor->extractPersonNameAndEmail();
            $this->data[] = $extractor;

            }
            } **/
        }
        //  }


    }


    /**
     * @param $company
     * @param $siren
     * @return array|JsonResponse
     */
    protected function getInfosFromVerif($company,$siren = null)
    {
        $objct  = new Verif();


        try{
            $r = $objct->search($company,$siren);
            $r = $objct->parse($r);


        }catch (JsonEncodedException $e)
        {

            $message = $e->getDecodedMessage();
            $response = new JsonResponse($message,isset($message['code']) ? $message['code'] : 500);

            return $response;

        }

        return $r;
    }



    /**
     *
     * Get infos form Linkedin
     *
     * @return array|JsonResponse
     */
    protected function getInfosFromLinkedin($company)
    {
        sleep(1);
        $linkedin = new Linkedin();

        $result =  $linkedin->search($company,$this->searchEngine);

        return $result;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return Extractor
     */
    public function getExtractor()
    {
        return $this->extractor;
    }

    /**
     * @param Extractor $extractor
     */
    public function setExtractor($extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine()
    {
        return $this->searchEngine;
    }

    /**
     * @param SearchEngine|null $searchEngine
     */
    public function setSearchEngine(SearchEngine $searchEngine = null)
    {

        if(!$searchEngine){
            $searchEngine = Utils::extractFromRequest(array('search_engine','searchEngine'));
        }


        switch ($searchEngine){
            case 'Bing':
                $this->searchEngine = new Bing();
                break;
            case "Google":
                $this->searchEngine = new Google();
                break;
            default:
                $this->searchEngine = new Bing();
                break;
        }
    }






    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'data' => $this->data,
        );
    }

    /**
     * Display the result of the research
     */
    public function display()
    {
        $error = true;
        /** @var Extractor $d */
        foreach($this->data as $d)
        {
            if(!$d->hasError()){
                $error = false;
            }
        }



        $response =  new JsonResponse($this,$error ? 404 : 200);
        $response->setEncodingOptions(JSON_PRETTY_PRINT);
        $response->send();
    }

}