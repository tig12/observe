<?php
/******************************************************************************
    
    List of aspects, part of table of contents of some generated pages.
    
    @license    GPL
    @history    2021-03-15 18:06:11+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\page;

use tigeph\model\IAA;

class tocAspects {
    
    /** 
        @param  $planets    Array containing IAA planet codes
    **/
    public static function html($planets){
        $res = '';
        $N = count($planets);
        $res .= "<table class=\"border padded2\">\n";
        $res .= "<tr>\n";
        $res .= "<th></th>\n";
        for($i=0; $i < $N-1; $i++){
            $res .= '<th>' . IAA::PLANET_NAMES[$planets[$i]] . "</th>\n";
        }
        $res .= "<tr>\n";
        for($i=1; $i < $N; $i++){
            $res .= "<tr>\n";
            $res .= '<th>' . IAA::PLANET_NAMES[$planets[$i]] . "</th>\n";
            for($j=0; $j < $i; $j++){
                $planet1 = $planets[$i];
                $planet2 = $planets[$j];
                $aspectCode = "$planet2-$planet1";
                $res .= '<td><a href="#aspect-' . $aspectCode . '">'
                    . "$planet2 - $planet1" . '</a></td>' . "\n";
            }
            $colspan = $N - $i;
            $res .= "<td colspan=\"$colspan\"></td>\n";
            $res .= "</tr>\n";
        }
        $res .= "</table>\n";
        return $res;
    }
    
}// end class
