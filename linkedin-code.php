<?php

/**
 *
 * @author Bastien Malahieude <b.malahieude@free.fr>
 *
 * This page is displaying the LinkedIn Code you need to paste to scrap contacts in a list of companies
 *
 * For more information read the Documentation
 *
 */

if(!isset($_GET['companies'])){
    die();
}

$code = file_get_contents('V2/assets/scrapping/linkedin-chrome.js');


echo '<pre>';
$code =  str_replace("'%COMPANIES%'",$_GET['companies'],$code);
echo htmlspecialchars($code);
echo '</pre>';

die();