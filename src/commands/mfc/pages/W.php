<?php
/******************************************************************************
    Computes wedding.html page of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2021-03-07 17:43:59+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use tigeph\model\IAA;

use observe\parts\page\headfoot;
use observe\parts\stats\distrib;
use observe\parts\stats\constant;
use observe\parts\draw\bar;
use observe\parts\fileSystem;

class W {
    
    /**
        @param $params  Parameters passed to all::execute()
    **/
    public static function computePage(&$params): string {
        
        $res = '';
        
        $titleString = 'wedding';
        $titleUCString = ucfirst($titleString);
        
        $title = $params['experience']['code'] . ' - ' . $titleUCString;
        $res .= headfoot::header(
            pathToRoot:     '../../..',
            title:          $title,
            description:    '',
        );
        
        $res .= "<h1>$title</h1>\n";
        //
        // proportion
        //
        if($params['wedding-proportion'] === true){
            $NWed = constant::loadFromTXT($params['in-dir'] . DS . 'distrib' . DS . 'W' . DS . 'N.txt');
            $N = $params['experience']['N'];
            $percent = round($NWed * 100 / $N, 2);
            $res .= '<div id="proportion"></div>';
            $res .= "<h2>Proportion</h2>\n";
            $res .= '<div class="big2 bold padding-left">' . number_format($NWed, thousands_separator:' ')
                 . ' wedding dates in the data'
                 . ' = ' . $percent . ' %' . "</div>\n";
        }
        //
        // year
        //
        if($params['child-by-year'] === true){
            $filename = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'year.csv';
            $dist = distrib::loadFromCSV($filename, header:false);
            $svg = bar::svg(
                data: $dist,
                title: "$titleUCString - year of birth",
                barW: 8,
                xlegends: ['min', 'max', 'top'],
                ylegends: ['min', 'max', 'mean'],
                ylegendsRound: 1,
            );
            $res .= '<div id="birthyear"></div>';
            $res .= $svg;
        }
        //
        // day
        //
        $filename = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'day.csv';
        $dist = distrib::loadFromCSV($filename, header:false);
        $svg = bar::svg(   
            data: $dist,
            title: "$titleUCString - day of birth",
            barW: 2,
            xlegends: ['min', 'max'],
            ylegends: ['min', 'max', 'mean'],
            ylegendsRound: 1,
        );
        $res .= '<div id="birthday"></div>';
        $res .= $svg;
        //
        // "age at wedding" = duration [wedding - birth]
        //
        if($params['wedding'] === true){
            $filename = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'wed-birth.csv';
            $dist = distrib::loadFromCSV($filename, header:false);
            $svg = bar::svg(
                data: $dist,
                title: "$titleUCString - Nb of months between wedding and birth",
                barW: 2,
                xlegends: ['min', 'max', 'top'],
                ylegends: ['min', 'max', 'mean'],
                ylegendsRound: 1,
            );
            $res .= '<div id="age-W"></div>';
            $res .= $svg;
        }
        //
        // planets
        //
        $res .= '<h2 id="planets">Planets at wedding date</h2>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . 'W' . DS . 'planets';
        foreach($params['planets'] as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $filename = $dirname . DS . $planet . '.csv';
            $dist = distrib::loadFromCSV($filename, header:false);
            $svg = bar::svg(
                data: $dist,
                title: "$planetName at wedding date",
                barW: 2,
                xlegends: ['min', 'max'],
                ylegends: ['min', 'max', 'mean'],
                ylegendsRound: 1,
            );
            $res .= '<div id="planet-' . $planet . '"></div>';
            $res .= $svg;
        }

        $res .= headfoot::footer();

        return $res;
    }
    
}// end class
