<?php
/******************************************************************************
    
    List of aspects with links to the details, presented in a table.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2021-03-15 18:06:11+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\output;

use tigeph\model\IAA;

class tocAspects {
    
    /** 
        Return a string contining a table with links to the different aspects between $planets.
        
        @param  $planets    Array containing IAA planet codes
        @param  $hrefPrefix Prefix used to form the urls inside the a href links
                Ex: "mypage.html#aspect-"
        @param  $statsDistrib   Array of stats info ; fields listed in observe\model\distrib\StatsDistrib::STATS_CSV_FIELDS
    **/
    public static function html(
        array $planets,
        string $hrefPrefix,
        array $statsDistrib,
    ): string{
        $res = '';
        $N = count($planets);
        $res .= "<table class=\"toc-aspects\" style=\"border:none;\">\n";
        for($i=1; $i < $N; $i++){
            $res .= "<tr>\n";
            $res .= '<th>' . IAA::PLANET_NAMES[$planets[$i]] . "</th>\n";
            for($j=0; $j < $i; $j++){
                $planet1 = $planets[$i];
                $planet2 = $planets[$j];
                $aspectCode = "$planet2-$planet1";
                $class_css = '';
                if($statsDistrib['aspects'][$aspectCode]['P_LIMIT'] == 'Y'){
                    $class_css = ' class="significant"';
                }
                $res .= '<td' . $class_css . '><a href="' . $hrefPrefix . $aspectCode . '">'
                    . "$planet2 - $planet1" . '</a></td>' . "\n";
            }
            $res .= "</tr>\n";
        }
        $res .= "<tr>\n";
        $res .= "<th></th>\n";
        for($i=0; $i < $N-1; $i++){
            $res .= '<th class="center">' . IAA::PLANET_NAMES[$planets[$i]] . "</th>\n";
        }
        $res .= "</tr>\n";
        $res .= "</table>\n";
        return $res;
    }
    
}// end class
