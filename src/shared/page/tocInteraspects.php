<?php
/******************************************************************************
    
    List of inter-aspects, part of table of contents of some generated pages.
    
    @license    GPL
    @history    2021-03-16 05:49:59+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\shared\page;

use tigeph\model\IAA;

class tocInteraspects {
    
    /** 
        @param  $planets1   Array containing IAA planet codes.
        @param  $planets2   Array containing IAA planet codes.
        @param  $label1     Label of member related to $planets1.
        @param  $label1     Label of member related to $planets2.
    **/
    public static function html(
        $planets1,
        $planets2,
        $label1,
        $label2,
    ){
        $res = '';
        $N1 = count($planets1);
        $N2 = count($planets2);
        $label1U = strToUpper($label1);
        $label2U = strToUpper($label2);
        $initial1 = substr($label1U, 0, 1);
        $initial2 = substr($label2U, 0, 1);
        $label1Vertical = implode('<br>', str_split($label1U));
        $res .= <<<HTML
<table class="border padded3 margin">
    <tr>
        <td colspan="2" rowspan="2"></td>
    </tr>
    <tr>
        <th colspan="$N2">$label2U</th>
    </tr>
    <tr>
        <td colspan="2"></td>
HTML;
        foreach($planets2 as $planet2){
            $res .= '<th>' . IAA::PLANET_NAMES[$planet2] . '</th>';
        }
        $res .= "\n    </tr>\n";
        for($i=0; $i < $N1; $i++){
            $planet1 = $planets1[$i];
            $res .= "    <tr>\n";
            if($i == 0){
                $res .= '        <th style="padding-left:5px; padding-right:5px;" rowspan="' . $N1 . '">' . $label1Vertical . "</th>\n";
            }
            $res .= "        <th><a href=\"#$planet1\">" . IAA::PLANET_NAMES[$planet1] . '</a></th>' . "\n";
            foreach($planets2 as $planet2){
                $href = "$initial1-$planet1--$initial2-$planet2";
                $label = "$initial1-$planet1 / $initial2-$planet2";
                $res .= "        <td><a href=\"#$href\">$label</a></td>\n";
            }
            $res .= "    </tr>\n";
        }
        $res .= "</table>\n";
        return $res;
    }
    
}// end class
