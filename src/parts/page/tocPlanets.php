<?php
/******************************************************************************
    
    List of planets, part of table of contents of some generated pages.
    
    @license    GPL
    @history    2021-03-15 18:01:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\page;

use tigeph\model\IAA;

class tocPlanets {
    
    /** 
        @param  $planets    Array containing IAA planet codes
    **/
    public static function html($planets){
        $res = '';
        foreach($planets as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $res .= '<span class="padding-right"><a href="#planet-' . $planet . '">' . $planetName . '</a></span>' . "\n";
        }
        return $res;
    }
    
}// end class
