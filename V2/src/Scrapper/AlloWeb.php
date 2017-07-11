<?php

namespace Extractor\Scrapper;


include __DIR__ . '/../header.php';



$scrapper = new Scrapper();

$scrapper->setUrl('http://www.alloweb.org/annuaire-startups/annuaire-startups/startups-services-b2b/page/');
$scrapper->setHasPages(true);
$scrapper->setStart(80);
$scrapper->setStop(81);

$scrapper->setSelectors(array(
    'list' => '#loop_listing_taxonomy .post',
    'company' => 'h1.entry-title',
    'address' => '#frontend_address',
    'data_list' => '#ft_headcontact .entry-header-custom-wrap p',
));

$scrapper->export();
die();


$scrapper->export();