<?php


namespace Extractor\Traits;

/**
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Traits
 *
 *
 * Parse a full name to return a first name and a last name
 *
 *
 * Class NameParser
 */
trait NameParser
{


    /**
     *
     * Parse the name of someone
     *
     * @param $name
     * @return array|bool
     */
    protected function parseName($name)
    {
        $parser = new \FullNameParser();

        $du = $parser->parse_name($name);

        if ($du['fname'] == '' || $du['lname'] == '') {
            return false;
        }


        return array(
            'first_name' => ucfirst(strtolower($du['fname'])),
            'last_name' => ucfirst(strtolower($du['lname'])),
        );
    }


}