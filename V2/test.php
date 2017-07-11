<?php

include __DIR__ . '/src/header.php';




$objct  = new LayerShifter\TLDExtract\Extract();

$r = ($objct->parse('www.ares.asso.fr/'));




if($r instanceof \Symfony\Component\HttpFoundation\JsonResponse){
    $r->send();
    die();
}

vardump($r);