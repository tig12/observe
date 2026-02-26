<?php
/******************************************************************************
    Like sleep() but parameter is a nb of seconds, and it prints a message.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2019-06-11 06:37:11+02:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\misc;

class smallDump{
    
    /** 
        Like print_r() but on one line.
        Adapted to small arrays.
        @param  $arr    The array to dump
    **/
    public static function print_r(mixed $arr, bool $return = false): string|true {
        $raw = print_r($arr, true);
        $tmp = explode("\n", $raw);
        $tmp = array_map('trim', $tmp);
        $res = implode(' ', $tmp);
        if($return) {
            return $res;
        }
        echo $res;
        return true;
    }

}// end class
