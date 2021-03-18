<?php
/******************************************************************************
    Computes mother.html and father.html pages of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use tigeph\model\IAA;

use observe\commands\mfc\MFC;
use observe\parts\page\header;
use observe\parts\page\footer;
use observe\parts\page\toc;
use observe\parts\page\tocPlanets;
use observe\parts\page\tocAspects;
use observe\parts\page\nav;
use observe\parts\stats\distrib;
use observe\parts\draw\bar;
use observe\parts\fileSystem;

class MF {
    
    /**
        TOC = Table of contents
        Correct when $params['experience']['has-wedding'] = true
    **/
    private static $toc = [
        'birthyear' => 'Year of birth',
        'birthday' => 'Day of birth',
        'age-C' => 'Age at child birth',
        'age-W' => 'Age at wedding',
        'planets' => 'Planets at birth',
        'aspects' => 'Aspects at birth',
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
        $pathToRoot = '../../..';
        $res .= header::html(
            pathToRoot:     $pathToRoot,
            title:          $title,
            description:    '',
        );
        $res .= nav::html(MFC::nav($params), $pathToRoot);
        $res .= "<h1>$title</h1>\n";
        if(!$params['experience']['has-wedding']){
            unset(self::$toc['age-W']);
        }
        $toc = toc::html(self::$toc);
        $tocPlanets = tocPlanets::html($params['planets']);
        $toc = str_replace(
            '<li><a href="#planets">Planets at birth</a></li>',
            '<li><a href="#planets">Planets at birth</a><div class="padding-left">' . $tocPlanets . '</div></li>',
            $toc
        );
        $tocAspects = tocAspects::html($params['planets']);
        $toc = str_replace(
            '<li><a href="#aspects">Aspects at birth</a></li>',
            '<li><a href="#aspects">Aspects at birth</a><div class="padding-left">' . $tocAspects . '</div></li>',
            $toc
        );
        $res .= $toc;
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
        if($params['experience']['has-wedding'] === true){
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
        $res .= '<div class="padding-left">' . $tocPlanets . '</div>';
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
        //
        // aspects
        //
        $res .= '<h2 id="aspects">Aspects at birth</h2>';
        $res .= '<div class="padding-left">' . $tocAspects . '</div>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'aspects';
        
        for($i=0; $i < count($params['planets']); $i++){
            for($j=$i+1; $j < count($params['planets']); $j++){
                $planet1 = $params['planets'][$i];
                $planet2 = $params['planets'][$j];
                $planetName1 = IAA::PLANET_NAMES[$planet1];
                $planetName2 = IAA::PLANET_NAMES[$planet2];
                $aspectCode = "$planet1-$planet2";
                $filename = $dirname . DS . $aspectCode . '.csv';
                $dist = distrib::loadFromCSV($filename, header:false);
                $svg = bar::svg(
                    data: $dist,
                    title: "$MFucstring - Aspects $planetName1 / $planetName2 at birth",
                    barW: 2,
                    xlegends: ['min', 'max'],
                    ylegends: ['min', 'max', 'mean'],
                    ylegendsRound: 1,
                );
                $res .= '<div id="aspect-' . $aspectCode . '"></div>';
                $res .= $svg;
            }
        }
        //
        $res .= footer::html();
        return $res;
    }
    
}// end class
