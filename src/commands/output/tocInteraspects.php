<?php
/******************************************************************************
    
    List of inter-aspects with links to the details, presented in a table.
    (inter-aspects = aspects between planets coming from two different dates)
    
    @license    GPL
    @history    2026-04-07 18:21:57+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\output;

use tigeph\model\IAA;

class tocInteraspects {
    
    /** 
        Return a string contining a table with links to the different aspects between $planets.
        @param  $planets    Array containing IAA planet codes
        @param  $hrefPrefix Prefix used to form the urls inside the a href links
                Ex: "mypage.html#interaspect-"
        @param  $statsDistrib   Array of stats info ; fields listed in observe\model\distrib\StatsDistrib::STATS_CSV_FIELDS
**/
/* 
Give me php code to add a string between each character of a string.
ex: my string is "abc"
and I want "a<br>b<br>"
*/
    public static function html(
        array $planets,
        string $hrefPrefix,
        array $statsDistrib,
        string $dateName_ver,
        string $dateName_hor,
    ): string{
        $res = '';
        $N = count($planets);
        $N1 = $N + 1;
        $dateName_ver = implode("<br>", str_split($dateName_ver));
        $res .= "<table class=\"toc-aspects\" style=\"border:none;\">\n";
        // table header
        $res .= "<tr>
                    <th style=\"border:none;\"></th>
                    <th style=\"border:none;\"></th>
                    <th class=\"center\" colspan=\"$N\">$dateName_hor</th>
                 </tr>";
        $res .= "<tr>\n";
        $res .= "<th style=\"border:none;\"></th><th style=\"border:none;\"></th>\n";
        for($i=0; $i < $N; $i++){
            $res .= '<th class="center">' . IAA::PLANET_NAMES[$planets[$i]] . "</th>\n";
        }
        $res .= "</tr>\n";
        $res .= "<tr><th rowspan=\"$N1\">$dateName_ver</th></tr>\n";
        
        // table body
        for($i=0; $i < $N; $i++){
            $res .= "<tr>\n";
            $res .= '<th>' . IAA::PLANET_NAMES[$planets[$i]] . "</th>\n";
            for($j=0; $j < $N; $j++){
                $planet1 = $planets[$i];
                $planet2 = $planets[$j];
                $aspectCode = "$planet1-$planet2";
                $class_css = '';
                if($statsDistrib['interaspects'][$aspectCode]['P_LIMIT'] == 'Y'){
                    $class_css = ' class="significant"';
                }
                $res .= '<td' . $class_css . '><a href="' . $hrefPrefix . $aspectCode . '">'
                    . "$planet1 - $planet2" . '</a></td>' . "\n";
            }
            $res .= "</tr>\n";
        }
        $res .= "</table>\n";
        return $res;
    }
    
}// end class
