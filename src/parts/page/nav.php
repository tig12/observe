<?php
/******************************************************************************
    
    Navigation of generated pages.
    Handles top, next, prev

    @license    GPL
    @history    2021-03-11 16:30:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\page;

class nav {
    
    const labels = [
        'top'   => '&uarr;',
        'prev'  => '&larr;',
        'next'  => '&rarr;',
    ];
    
    /** 
        @param  $nav
                Ex: $nav = [
                        'top'   => ['index.html', 'a00 experience'],
                        'prev'  => ['father.html', 'Father'],
                        'next'  => ['wedding.html', 'Wedding'],
                    ];
    **/
    public static function html($nav){
        $res = '';
        
        $res .= <<<HTML
<nav class="prevnext">
HTML;
        foreach($nav as $navKey => $element){
            [$href, $title] = $element;
            $label = self::labels[$navKey];
            $res .= <<<HTML
<a class="$navKey" href="$href" title="$title">$label</a>
HTML;
        }
        $res .= <<<HTML
</nav>
HTML;
        return $res;
    }
    
}// end class
