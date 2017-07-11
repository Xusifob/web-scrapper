<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

include __DIR__ . '/src/header.php';

$extractor = new ExtractorMultiple();

$extractor->extract();

$extractor->display();