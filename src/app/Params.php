<?php
/********************************************************************************
    
    Functions shared by several commands to hadle parameters
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-06 11:53:40+02:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\app;

class Params {
    
    /**
        
        @param  $param can be a number (ex: "2") or a range (ex: "2-4")

    **/
    public static function computeControls(string $param): array {
        $res = [
            'controls'  => [],
            'msg'       => '',
        ];
        $p_one = '/^\d+$/';
        $p_range = '/^\d+-\d+$/';
        preg_match($p_one, $param, $m);
        if(count($m) == 1){
            $res['controls'][] = $m[0];
        }
        else {
            preg_match($p_range, $param, $m);
            if(count($m) == 1){
                [$from, $to] = explode('-', $m[0]);
                $res['controls'] = range($from, $to); // if $to > $from, range() returns $controls from $to to $from
            }
            else {
                $res['msg'] = "INVALID PARAMETER: \"{$param}\"";
            }
        }
        return $res;
    }
    
} // end class
