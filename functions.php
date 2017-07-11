<?php

use GuzzleHttp\Client;


function vardump($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}




/**
 *
 * Return an url from bing search
 *
 * @param $company
 * @param bool|true $with_website
 * @return bool|mixed|string
 */
function bing_search_url($company,$with_website = true)
{

    if($with_website){
        $search = urlencode(" {$company['name']}  {$company['website']}");
    }else{
        $search = urlencode(" {$company['name']} ");
    }

    $html = file_get_html('http://www.bing.com/search?cc=fr&q=' . $search);


    // Remove bing ads
    foreach ($html->find('.b_ad') as $item) {
        $item->outertext = '';
    }
    // Remove bing orthographic thing
    foreach ($html->find('.b_ans') as $item) {
        $item->outertext = '';
    }


    $html->save();

    $html = preg_replace('/(<strong>)(.*?)(<\/strong>)/', '$2', $html);

    preg_match_all('/<cite>([^<])+<\/cite>/', $html, $matches);

    $url = '';

    foreach ($matches[0] as $m) {
        if (!preg_match('/(domains5|nozio|archive.org|blogspot|youtube|wordpress|wikipedia|twitter|google|hubspot|znwhois|facebook|linkedin|societe.com|amazon|minify|privea|BEST-PRICE|Fotosearch|bat\.bing)/', $m)) {
            $url = $m;
            break;
        }
    }

    if(empty($url)){
        if(!$with_website){
            return false;
        }else{
            return bing_search_url($company,false);
        }
    }

    $url = preg_replace('/(<cite>)(.*?)(<\/cite>)/', '$2', $url);

    $extract = new LayerShifter\TLDExtract\Extract();

    $result = $extract->parse($url);
    $url =  $result->getHostname() . '.' . $result->getSuffix();



    return $url;
}



function CrunchBaseSearch($company)
{

    $api_key = '4568c46b5c97886c88b28f311616ed62';

    $app_id = 'A0EF2HAQR0';

    try {
        $client = new Client(array(
            'base_uri' => 'https://a0ef2haqr0-1.algolia.io/1/indexes/main_production/',
        ));

        $request = $client->post('query', array(
            'headers' => array(
                'Origin' => 'https://www.crunchbase.com',
                'Referer' => 'https://www.crunchbase.com/',
                'X-Algolia-API-Key' => $api_key,
                'X-Algolia-Application-Id' => $app_id,
                'Content-Type' => 'application/json',
            ),
            'json' => array(
                'params' => "query={$company['company']}",
                'apiKey' => $api_key,
                'appID' => $app_id
            )
        ));


        return crunchBaseParseFromSearch(json_decode($request->getBody()->getContents(),true),$company);
    }catch (\GuzzleHttp\Exception\ClientException $e)
    {
        return array();
    }
}


/**
 * @param $data
 * @param array $company
 * @return array
 */
function crunchBaseParseFromSearch($data,$company = [])
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
            //  $company['president'] = $hit['first_name'] . ' ' . $hit['last_name'];

            $company['first_name'] = $hit['first_name'];
            $company['last_name'] = $hit['last_name'];

            break;
        }

    }




    return $company;
}


function crunchBaseParse($permalink)
{

    $api_key = '4568c46b5c97886c88b28f311616ed62';

    $app_id = 'A0EF2HAQR0';

    $link = "https://www.crunchbase.com/organization/$permalink";

    // $dom = file_get_html($link);

    $client = new Client();


    $request = $client->get($link, array(
        'headers' => array(
            'Host' => 'www.crunchbase.com',
            'Referer' => $link,
            'Content-Type' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
        )
    ));

    echo $request->getBody()->getContents(); /*$dom->find('.people');*/

    return '';
}



