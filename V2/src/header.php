<?php

ini_set('display_errors',1);

ini_set('max_execution_time',3600);


define('MAIN_DIR',__DIR__ . '/../');

include_once MAIN_DIR . '/../vendor/autoload.php';
include_once MAIN_DIR . 'libs/simple_html_dom.php';
include_once MAIN_DIR . 'libs/PHP-Name-Parser/parser.php';
include_once MAIN_DIR . '/libs/Encoding.php';
include_once MAIN_DIR . '/../functions.php';

include_once __DIR__  . '/Exception/JsonEncodedException.php';

include_once __DIR__ . '/Traits/NameParser.php';

include_once __DIR__ . '/Service/RequestDoer.php';
include_once __DIR__ . '/Service/DOMAnalyser.php';
include_once __DIR__ . '/Service/Hunter.php';
include_once __DIR__ . '/Service/Utils.php';
include_once __DIR__ . '/Service/Linkedin.php';
include_once __DIR__ . '/Service/Searcher.php';


include_once __DIR__ . '/HttpFoundation/CSVResponse.php';


include_once __DIR__ . '/Scrapper/ScrapperInterface.php';
include_once __DIR__ . '/Scrapper/Scrapper.php';


include_once __DIR__ . '/SearchEngine/SearchEngine.php';
include_once __DIR__ . '/SearchEngine/Google.php';
include_once __DIR__ . '/SearchEngine/Bing.php';


include_once __DIR__ . '/ExtractorMultiple.php';
include_once __DIR__ . '/Extractor.php';
include_once __DIR__ . '/Service/Societe.php';
include_once __DIR__ . '/Service/Verif.php';

