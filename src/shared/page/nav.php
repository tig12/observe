<?php
/******************************************************************************
    
    Navigation of generated pages.
    Handles top, next, prev

    @license    GPL
    @history    2021-03-11 16:30:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\shared\page;

class nav {
    
    const labels = [
        'top'   => '&uarr;',
        'prev'  => '&larr;',
        'next'  => '&rarr;',
    ];
    
    /** 
        @param  $nav
                ex: [
                        ['index.html', 'a00 - Births in France, year 2000'],
                        ['mother.html', 'Mother'],
                        ...
                    ]
        @return The html code of navigation menu
    **/
    public static function html($nav, $pathToRoot){
        $res = '';
        $res .= <<<HTML
<ul class="menus">
  <li class="dropdown">
    <a class="dropbtn" href="index.html"><img src="$pathToRoot/static/menu.png" class="border" style="border-radius:10px;"></a>
    <div class="dropdown-content">
HTML;
        foreach($nav as [$href, $label]){
            $res .= '    ' . ($href == '' ? "$label\n" : "<a href=\"$href\">$label</a>\n");
        }
        $res .= <<<HTML
    </div>
  </li>
</ul>

HTML;
        return $res;
    }
    
}// end class
