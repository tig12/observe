<?php
/******************************************************************************
    Computes mother.html and father.html pages of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2021-03-07 17:00:42+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use tigeph\model\IAA;

use observe\parts\page\headfoot;
use observe\parts\page\toc;
use observe\parts\page\nav;
use observe\parts\stats\distrib;
use observe\parts\draw\bar;
use observe\parts\fileSystem;

class C {
    
    /**
        TOC = Table of contents
        Correct when $params['wedding'] = true
    **/
    const toc = [
        'birthday' => 'Day of birth',
        'birthyear' => 'Year of birth',
        'age-W' => 'Duration between parents\' wedding and birth',
        'planets' => 'Planets at births',
        'aspects' => 'Aspects at birth',
    ];
    
    /** Navigation **/
    const nav = [
        'top'   => ['index.html', 'a00 experience'],
    ];
    
    /**
        @param $params  Parameters passed to all::execute()
    **/
    public static function computePage(&$params): string {
        
        $res = '';
        
        $titleString = 'child';
        $titleUCString = ucfirst($titleString);
        
        $title = $params['experience']['code'] . ' - ' . $titleUCString;
        $res .= headfoot::header(
            pathToRoot:     '../../..',
            title:          $title,
            description:    '',
        );
        
        $res .= "<h1>$title</h1>\n";
        $toc = self::toc;
        if(!$params['wedding']){
            unset($toc['age-W']);
        }
        if(!$params['child-by-year']){
            unset($toc['birthyear']);
        }
        $res .= nav::html(self::nav);
        $res .= toc::html($toc);
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
                title: "$titleUCString - Duratio between parents'wedding and birth (in months)",
                barW: 2,
                xlegends: ['min', 'max'],
                ylegends: ['min', 'max', 'mean'],
                ylegendsRound: 1,
            );
            $res .= '<div id="age-W"></div>';
            $res .= $svg;
        }
        //
        // planets
        //
        $res .= '<h2 id="planets">Planets at birth</h2>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'planets';
        foreach($params['planets'] as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $filename = $dirname . DS . $planet . '.csv';
            $dist = distrib::loadFromCSV($filename, header:false);
            $svg = bar::svg(
                data: $dist,
                title: "Child - $planetName at birth",
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
