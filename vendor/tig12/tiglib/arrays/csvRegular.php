<?php
/******************************************************************************
    Fills a csv file to an array of regular arrays.
    
    @license    GPL
    @history    2020-12-20 23:30:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\arrays;


class csvRegular{
    
    /**
        Fills a csv file to an array of regular arrays.
        @param      $filename Absolute path to the csv file
        @param      $delimiter field delimiter (one character only).
        @return     false or regular array of regular array
    **/
    public static function compute($filename, $delimiter=';'){
        $res = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false){
                if(count($data) == 1 && $data[0] == ''){
                    continue; // skip empty lines
                }
                $res[] = $data;
            }
            fclose($handle);
        }
        return $res;
    }
    
}// end class
