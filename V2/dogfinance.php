<?php

include __DIR__ . '/src/header.php';
include __DIR__ . '/src/Scrapper/DogFinance.php';

use \Extractor\Service\Utils;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Extractor\Scrapper\DogFinance;



// Init the scrapper
$scrapper  = new DogFinance();
$scrapper->setUrl($scrapper->prefix);



$page = Utils::extractFromRequest('page');
if($page){

    $profiles = $scrapper->getProfileUrls('http://www.dogfinance.com/fr/a/groupes/struct/fb_all_abos/211/' . $page);



    $profiles = $scrapper->removeDuplicates($profiles);
    $companies = array();

    foreach($profiles as $profile){
        $scrapper->setUrl($scrapper->prefix . $profile['url']);

        $companies[] = $scrapper->parse();
    }



    $response = new JsonResponse($companies);

    $response->send();
    die();
}

$url = Utils::extractFromRequest('url');



if($url)
{


    $scrapper->setUrl($scrapper->prefix . Utils::extractFromRequest('url'));


    $company = $scrapper->parse();

    $response = new JsonResponse($company);

    $response->send();
    die();

}

$one_fiche = Utils::extractFromRequest('id');


if($one_fiche){
    $company = $scrapper->scrapOneFiche('/fr/a/profil/banniere/show_coord/' . $one_fiche);

    $response = new JsonResponse($company);

    $response->send();
    die();
}


$export = Utils::extractFromRequest('export');

if($export){

    $list = Utils::extractFromRequest('list');
    $file = Utils::extractFromRequest('file');

    $scrapper->export($list,$file);
}