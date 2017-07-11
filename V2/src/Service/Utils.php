<?php

namespace Extractor\Service;


/**
 *
 * Get bunch of usefull methods
 *
 * @author Xusifob <b.malahieude@free.fr>
 *
 * @package Xusifob\Extractor\Service
 *
 * Class Utils
 */
abstract class Utils
{


    /**
     *
     * Return a parameter from
     *
     * @param array|string $array
     * @param array $parameters
     *
     * @return null|string
     */

    public static function extractFromRequest($array,$parameters = array())
    {

        if(is_string($array)){
            $array = array($array);
        }

        if(empty($parameters)){
            $parameters = $_GET;
        }


        if(!is_array($parameters)){
            return null;
        }

        $array = array_map('strtolower', $array);


        foreach($parameters as $key => $value){
            if(in_array(strtolower($key),$array) && !empty($value)){
                if($value == "false"){
                    return false;
                }
                return $value;
                break;
            }
        }

        return null;
    }


    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    public static function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }


    /**
     * @param $var
     */
    public static function vardump($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }



    /**
     * @param $csvFile
     * @return mixed
     */
    public static function detectDelimiter($csvFile)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }



    /**
     *
     * Transform an array to a CSV file
     *
     * @param $data
     * @param string $filename
     * @param string $delimiter
     */
    public static function array_to_csv($data, $filename = 'export.csv',$delimiter = ';')
    {
        $exist = false;

        if(file_exists($filename)){
            $exist = true;
        }

        $output = fopen($filename, 'a+');


        if(!$exist) {
            $header = array_keys( $data[0] );
            fputcsv( $output, $header,$delimiter );
        }


        foreach ($data as $row) {
            fputcsv($output, $row,$delimiter);
        }
        rewind($output);
        $data = '';
        while ($line = fgets($output)) {
            $data .= $line;
        }
        $data .= fgets($output);

        fclose($output);
        //chmod($filename, 0777);
    }




    /**
     * @param string $filename
     * @param string $delimiter
     *
     * @return array
     */
    public static function csv_to_array($filename = 'data.csv',$delimiter = ';')
    {

        $csv_data = file_get_contents($filename);

        $delimiter = Utils::detectDelimiter($filename);

        $lines = explode("\n", $csv_data);
        $head = str_getcsv(array_shift($lines),$delimiter);

        $array = array();
        foreach ($lines as $line) {

            $csv = str_getcsv($line,$delimiter);

            if(count($head) == count($csv)) {
                $array[] = array_combine( $head, $csv );
            }
        }

        return $array;
    }


    /**
     *
     *
     * Calculate the similarity between 2 strings
     *
     *
     * @param $s1
     * @param $s2
     * @return float
     */
    public static function similarity($s1, $s2) {
        $longer = $s1;
        $shorter = $s2;
        if(strlen($s1) < strlen($s2)) {
            $longer = $s2;
            $shorter = $s1;
        }
        $longerLength = strlen($longer);
        if ($longerLength == 0) {
            return 1.0;
        }
        return ($longerLength - self::editDistance($longer, $shorter)) / floatval($longerLength);
    }


    /**
     *
     * Edit the distance between 2 stings
     *
     *
     * @param $s1
     * @param $s2
     * @return mixed
     */
    public static function editDistance($s1, $s2) {
        $s1 = strtolower($s1);
        $s2 = strtolower($s2);

        $costs = array();
        for ($i = 0;$i <= strlen($s1); $i++) {
            $lastValue = $i;
            for ($j = 0; $j <= strlen($s2); $j++) {
                if ($i == 0)
                    $costs[$j] = $j;
                else {
                    if ($j > 0) {
                        $newValue = $costs[$j - 1];
                        if (substr($s1,$i -1,1) != substr($s2,$i -1,1))
                            $newValue = min(min($newValue, $lastValue),
                                    $costs[$j]) + 1;
                        $costs[$j - 1] = $lastValue;
                        $lastValue = $newValue;
                    }
                }
            }
            if ($i > 0)
                $costs[strlen($s2)] = $lastValue;
        }
        return $costs[strlen($s2)];
    }


}