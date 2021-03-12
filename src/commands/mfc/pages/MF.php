<?php
/******************************************************************************
    Computes mother.html and father.html pages of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use tigeph\model\IAA;

use observe\parts\page\headfoot;
use observe\parts\page\toc;
use observe\parts\page\nav;
use observe\parts\stats\distrib;
use observe\parts\draw\bar;
use observe\parts\fileSystem;

class MF {
    
    /**
        TOC = Table of contents
        Correct when $params['wedding'] = true
    **/
    const toc = [
        'birthyear' => 'Year of birth',
        'birthday' => 'Day of birth',
        'age-C' => 'Age at child birth',
        'age-W' => 'Age at wedding',
        'planets' => 'Planets at births',
        'aspects' => 'Aspects at birth',
    ];
    
    /** Navigation **/
    const nav = [
        'top'   => ['index.html', 'a00 experience'],
    ];
    
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
        $toc = self::toc;
        if(!$params['wedding']){
            unset($toc['age-W']);
        }
        $res .= nav::html(self::nav);
        $res .= toc::html($toc);
        //
        // year
        //
        $filename = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'year.csv';
        $dist = distrib::loadFromCSV($filename, header:false);
        $svg = bar::svg(
            data: $dist,
            title: "$MFucstring - year of birth",
            barW: 8,
            xlegends: ['min', 'max', 'top'],
            ylegends: ['min', 'max', 'mean'],
            ylegendsRound: 1,
        );
        $res .= '<div id="birthyear"></div>';
        $res .= $svg;
        //
        // day
        //
        $filename = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'day.csv';
        $dist = distrib::loadFromCSV($filename, header:false);
        $svg = bar::svg(
            data: $dist,
            title: "$MFucstring - day of birth",
            barW: 2,
            xlegends: ['min', 'max'],
            ylegends: ['min', 'max', 'mean'],
            ylegendsRound: 1,
        );
        $res .= '<div id="birthday"></div>';
        $res .= $svg;
        //
        // age at child birth
        //
        $filename = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'age-child.csv';
        $dist = distrib::loadFromCSV($filename, header:false);
        $svg = bar::svg(
            data: $dist,
            title: "$MFucstring - age at child birth",
            barW: 8,
            xlegends: ['min', 'max', 'top'],
            ylegends: ['min', 'max', 'mean'],
            ylegendsRound: 1,
        );
        $res .= '<div id="age-C"></div>';
        $res .= $svg;
        //
        // age at wedding
        //
        if($params['wedding'] === true){
            $filename = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'age-wed.csv';
            $dist = distrib::loadFromCSV($filename, header:false);
            $svg = bar::svg(
                data: $dist,
                title: "$MFucstring - age at wedding",
                barW: 8,
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
        $res .= '<h2 id="planets">Planets at birth</h2>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'planets';
        foreach($params['planets'] as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $filename = $dirname . DS . $planet . '.csv';
            $dist = distrib::loadFromCSV($filename, header:false);
            $svg = bar::svg(
                data: $dist,
                title: "$MFucstring - $planetName at birth",
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
