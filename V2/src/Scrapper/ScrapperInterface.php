<?php


namespace Extractor\Scrapper;


/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Scrapper
 *
 * Interface ScrapperInterface
 *
 */
interface ScrapperInterface
{


    /** @const DIR The name of the export directory */
    const DIR = MAIN_DIR . 'exports/';


    /** Parse the website to get the data to extract */
    public function parse();

    /** Extract the data from the website to the extract */
    public function export($list = null,$file_name = '');


    /**
     *
     * Scrap data from one specific company
     *
     * @param $url
     * @return mixed
     */
    public function scrapFiche($url);


    /** Setter for the dir property */
    public function getDir();


    /** Getter for the dir property */
    public function setDir($dir);

}