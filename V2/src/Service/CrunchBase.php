<?php


namespace Extractor\Service;

use Extractor\Exception\JsonEncodedException;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Exception\ServerException;



/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * This class handles all the calls on Hunter.io
 *
 * Class Hunter
 */
class CrunchBase
{


    /**
     * @var string
     */
    protected $api_key  = '4568c46b5c97886c88b28f311616ed62';


    /**
     * @var string
     */
    protected $app_id  = 'A0EF2HAQR0';


    /**
     *
     * Do a search on a company
     *
     * @param $company
     * @return array
     */
    public function search($company)
    {



        try {
            $client = new Client(array(
                'base_uri' => 'https://a0ef2haqr0-1.algolia.io/1/indexes/main_production/',
            ));

            $request = $client->post('query', array(
                'headers' => array(
                    'Origin' => 'https://www.crunchbase.com',
                    'Referer' => 'https://www.crunchbase.com/',
                    'X-Algolia-API-Key' => $this->api_key,
                    'X-Algolia-Application-Id' => $this->app_id,
                    'Content-Type' => 'application/json',
                ),
                'json' => array(
                    'params' => "query={$company['company']}",
                    'apiKey' => $this->api_key,
                    'appID' => $this->app_id
                )
            ));


            return $this->parse(json_decode($request->getBody()->getContents(),true),$company);
        }catch (ClientException $e)
        {
            return array();
        }
    }




    /**
     *
     * Parse the search data
     *
     * @param $data
     * @param array $company
     * @return array
     */
    protected function parse($data,$company = [])
    {

        if(count($data['hits']) == 0){
            return $company;
        }

        $permalink = $data['hits'][0]['permalink'];


        foreach($data['hits'] as $hit)
        {

            // Prevent error
            if(!isset($hit['_highlightResult']['title']['value'])){
                continue;
            }


            if(isset($hit['organization_permalink']) && $hit['type'] == 'Person' && $permalink === $hit['organization_permalink'] && preg_match('/(CEO|Chief Operating Officer|founder)/i',$hit['_highlightResult']['title']['value'])){
                if(isset($hit['location_name'])){
                    $company['address'] = $hit['location_name'];
                }else{
                    $company['address'] = '';
                }

                $company['siret'] = '';
                $company['description'] = $data['hits'][0]['description'];

                $company['first_name'] = $hit['first_name'];
                $company['last_name'] = $hit['last_name'];

                break;
            }

        }




        return $company;
    }


}