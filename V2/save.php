<?php

include __DIR__ . '/src/header.php';

header("Access-Control-Allow-Origin: http://www.verif.com",true);
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers:Authorization, Content-Type');

$body = file_get_contents('php://input');
$body = json_decode($body,true);

if(!isset($_GET['file_name'])){
    die('File name must be provided');
}


if(isset($_GET['export']))
{

    $data = \Extractor\Service\Utils::csv_to_array('exports/saves/' .$_GET['file_name']);

    $response = new \Extractor\HttpFoundation\CSVResponse($data);

    $response->setFilename($_GET['file_name']);

    $response->send();
    die();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' )
{
    \Extractor\Service\Utils::array_to_csv($body,'exports/saves/' .$_GET['file_name']);

}


die('ok');