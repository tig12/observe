<?php
/******************************************************************************
    
    Table of contents of generated pages.

    @license    GPL
    @history    2021-03-11 16:30:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\shared\page;

class toc {
    
    /** 
        @param  $toc
                Ex: $toc = [
                        'birthyear' => 'Year of birth',
                        'birthday' => 'Day of birth',
                        'age-C' => 'Age at child birth',
                        'age-W' => 'Age at wedding',
                        'planets' => 'Planets at births',
                        'aspects' => 'Aspects at birth',
                    ];
    **/
    public static function html($toc){
        $res = '';
        $res .= <<<HTML
<div class="toc">
    <ul>
HTML;
        foreach($toc as $href => $label){
            $res .= '<li><a href="#' . $href . '">' . $label . '</a></li>' . "\n";
        }
        $res .= <<<HTML
    </ul>
</div>
HTML;
        return $res;
    }
    
}// end class
