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
    
    /**
        Returns true if the user answers "y" or "Y", false otherwise
    **/
    public static function answerYN(string $msg): bool {
        $answer = readline($msg . "Are you sure (y/n)? ");
        if(strtolower($answer) == 'y') {
            return true;
        }
        else{
            if(strtolower($answer) == 'n') {
                echo "OK, nothing was modified\n";
                return false;
            }
            else {
                echo "WRONG ANSWER - respond with 'y' or 'n'.\nNothing was modified\n";
                return false;
            }
            return true;
        }
    }
    
} // end class
