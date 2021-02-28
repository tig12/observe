<?php
/******************************************************************************
    Computes index.html page of a MFCW group
    Auxiliary of all::compute()
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\page\headfoot;
use observe\parts\stats\distrib;

class MF {
    
    /**
        @param $params  Parameters passed to all::execute()
        @param $MF      'M' or 'F'
    **/
    public static function computePage(&$params, $MF): string {
        
        $res = '';
        
        $MFstring = $MF == 'M' ? 'mother' : 'father';
        $MFucstring = ucfirst($MFstring);
        
        $title = $params['experience']['code'] . ' - ' . $MFucstring;
        $res .= headfoot::header(
            pathToRoot:     '../../..',
            title:          $title,
            description:    '',
        );
        
        $res .= "<h1>$title</h1>\n";
        
        $filename = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'year.csv';
        $dist = distrib::loadFromCSV($filename, header:false);
echo "\n<pre>"; print_r($dist); echo "</pre>\n"; exit;
        $res .= <<<HTML
HTML;

        $res .= headfoot::footer();

        return $res;
    }
    
}// end class
