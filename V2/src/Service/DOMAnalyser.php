<?php

namespace Extractor\Scrapper;


use \Extractor\Service\RequestDoer;
use ForceUTF8\Encoding;


use \Symfony\Component\HttpFoundation\JsonResponse;



/**
 *
 *
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * Class DOMAnalyser
 *
 */
abstract class DOMAnalyser extends RequestDoer
{



    /**
     *
     * List of all selectors
     *
     * @var array
     */
    protected $selectors = array();




    /**
     *
     * Return the content of an HTML element
     *
     * @param array|\simple_html_dom_node $element
     * @param string $attribute
     * @return string
     */
    public function getContent($element,$attribute = 'innertext')
    {
        if(!$element){
            return false;
        }


        if(is_array($element) && isset($element[0])){
            $element = $element[0];
        }

        $text = $element->{$attribute};

       return $this->sanitize($text);

    }


    /**
     * @return string
     */
    public function sanitize($text)
    {

        $text = str_replace('&nbsp;',' ',$text);


        $text = preg_replace('/<( +)?\/?( +)?br(( |\/)+)?>/',' ',$text);
        $text = preg_replace('/ +/',' ',$text);


        $text = strip_tags($text);

        $text = Encoding::fixUTF8($text);

        $text = html_entity_decode($text);


        return trim($text);

    }



    /**
     * @return array
     */
    public function getSelectors()
    {
        return $this->selectors;
    }

    /**
     * @param array $selectors
     */
    public function setSelectors($selectors)
    {

        $this->selectors = array_merge($this->selectors,array_filter($selectors));
    }


    /**
     * @param $key
     * @param $selector
     */
    public function addSelector($key,$selector)
    {
        $this->selectors[$key] = $selector;
    }



    /**
     * @param $selector
     * @return bool
     */
    public function getSelector($selector)
    {
        return isset($this->selectors[$selector]) ? $this->selectors[$selector] : false;
    }


    /**
     *
     * Return the text from a selector if it exit on the page
     *
     * @param \simple_html_dom $html
     * @param $selector
     * @param $attribute
     *
     * @return string
     */
    public function getSelectorText(\simple_html_dom $html,$selector,$attribute = 'innertext')
    {
        $_selector = $this->getSelector($selector);


        if(!$_selector){
            return '';
        }

        $element = $html->find($this->getSelector($selector));

        if(!isset($element[0])){
            return '';
        }


        return $this->getContent($element[0],$attribute);
    }


}
