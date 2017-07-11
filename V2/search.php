<?php

include __DIR__ . '/src/header.php';

$searcher = new \Extractor\Service\Searcher();

$r = $searcher->search();

if($r instanceof \Symfony\Component\HttpFoundation\JsonResponse){
    $r->send();
    die();
}