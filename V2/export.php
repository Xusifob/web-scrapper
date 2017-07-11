<?php

include __DIR__ . '/src/header.php';

$extractor = new Extractor();

$extractor->export();
$extractor->display();