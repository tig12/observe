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
        for($i=0; $i < count($planets); $i++){
            $res .= '<div>';
            for($j=$i+1; $j < count($planets); $j++){
                $planet1 = $planets[$i];
                $planet2 = $planets[$j];
                $planetName1 = IAA::PLANET_NAMES[$planet1];
                $planetName2 = IAA::PLANET_NAMES[$planet2];
                $aspectCode = "$planet1-$planet2";
                $res .= '<span class="padding-right"><a href="#aspect-' . $aspectCode . '">'
                    . "$planetName1-$planetName2" . '</a></span>' . "\n";
            }
            $res .= '</div>';
        }
        return $res;
    }
    
}// end class
