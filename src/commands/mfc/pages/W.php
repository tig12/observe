<?php
/******************************************************************************
    Computes wedding.html page of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2021-03-07 17:43:59+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
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
use observe\parts\stats\constant;
use observe\parts\draw\bar;
use observe\parts\fileSystem;

class W {
    
    /** TOC = Table of contents **/
    private static $toc = [
        'proportion' => 'Day of birth',
        'year' => 'Wedding year',
        'day' => 'Wedding day',
        'planets' => 'Planets at wedding date',
        'aspects' => 'Aspects at wedding date',
    ];
    
    /**
        @param $params  Parameters passed to all::execute()
    **/
    public static function computePage(&$params): string {
        $res = '';
        $titleString = 'wedding';
        $titleUCString = ucfirst($titleString);
        $title = $params['experience']['code'] . ' - ' . $titleUCString;
        $pathToRoot = '../../..';
        $res .= header::html(
            pathToRoot:     $pathToRoot,
            title:          $title,
            description:    '',
        );
        $res .= nav::html(MFC::nav($params), $pathToRoot);
        $res .= "<h1>$title</h1>\n";
        $toc = toc::html(self::$toc);
        $tocPlanets = tocPlanets::html($params['planets']);
        $toc = str_replace(
            '<li><a href="#planets">Planets at wedding date</a></li>',
            '<li class="padding-top05"><a href="#planets">Planets at wedding date</a><div class="padding-left">' . $tocPlanets . '</div></li>',
            $toc
        );
        $tocAspects = tocAspects::html($params['planets']);
        $toc = str_replace(
            '<li><a href="#aspects">Aspects at wedding date</a></li>',
            '<li class="padding-top05"><a href="#aspects">Aspects at wedding date</a><div class="padding-left">' . $tocAspects . '</div></li>',
            $toc
        );
        $res .= $toc;
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
        //  =============== y-m-d ===============
        //
         if($params['svg-separate'] == true){
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . 'W';
            fileSystem::mkdir($svgdir);
        }
       //
        // year
        //
        $infile = $params['in-dir'] . DS . 'distrib' . DS . 'W' . DS . 'year.csv';
        $dist = distrib::loadFromCSV($infile, header:false);
        [$html_markup, $file_contents] = bar::svg(
            data:           $dist,
            title:          "$titleUCString year",
            svg_separate:   $params['svg-separate'],
            img_src:        $params['svg-path'] . "/W/year.svg",
            img_alt:        "$titleUCString year",
            barW:           8,
            xlegends:       ['min', 'max'],
            ylegends:       ['min', 'max', 'mean'],
            ylegendsRound:  1,
            meanLine:       true,
        );
        $res .= '<div id="birthyear"></div>';
        $res .= $html_markup;
        if($params['svg-separate'] == true){
            fileSystem::saveFile($svgdir . DS . 'year.svg', $file_contents);
        }
//return $res;
        //
        // day
        //
        $infile = $params['in-dir'] . DS . 'distrib' . DS . 'W' . DS . 'day.csv';
        $dist = distrib::loadFromCSV($infile, header:false);
        [$html_markup, $file_contents] = bar::svg(
            data:           $dist,
            title:          "$titleUCString day",
            svg_separate:   $params['svg-separate'],
            img_src:        $params['svg-path'] . "/W/day.svg",
            img_alt:        "$titleUCString day",
            barW:           2,
            xlegends:       ['min', 'max'],
            ylegends:       ['min', 'max', 'mean'],
            ylegendsRound:  1,
            meanLine:       true,
        );
        $res .= '<div id="birthday"></div>';
        $res .= $html_markup;
        if($params['svg-separate'] == true){
            fileSystem::saveFile($svgdir . DS . 'day.svg', $file_contents);
        }
        
        //
        //  =============== planets ===============
        //
        if($params['svg-separate'] == true){
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . 'W' . DS . 'planets';
            fileSystem::mkdir($svgdir);
        }
        //
        $res .= '<h2 id="planets">Planets at wedding date</h2>';
        $res .= '<div class="padding-left">' . $tocPlanets . '</div>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . 'W' . DS . 'planets';
        foreach($params['planets'] as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $infile = $dirname . DS . $planet . '.csv';
            $dist = distrib::loadFromCSV($infile, header:false);
            [$html_markup, $file_contents] = bar::svg(
                data:           $dist,
                title:          "$planetName at wedding date",
                svg_separate:   $params['svg-separate'],
                img_src:        $params['svg-path'] . "/W/planets/$planet.svg",
                img_alt:        "$planetName at wedding date",
                barW:           2,
                xlegends:       ['min', 'max'],
                ylegends:       ['min', 'max', 'mean'],
                ylegendsRound:  1,
            );
            $res .= '<div id="planet-' . $planet . '"></div>';
            $res .= $html_markup;
            if($params['svg-separate'] == true){
                fileSystem::saveFile($svgdir . DS . $planet . '.svg', $file_contents);
            }
        }
        
        //
        //  =============== aspects ===============
        //
        if($params['svg-separate'] == true){
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . 'W' . DS . 'aspects';
            fileSystem::mkdir($svgdir);
        }
        $res .= '<h2 id="aspects">Aspects at wedding date</h2>';
        $res .= '<div class="padding-left">' . $tocAspects . '</div>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . 'W' . DS . 'aspects';
        for($i=0; $i < count($params['planets']); $i++){
            for($j=$i+1; $j < count($params['planets']); $j++){
                $planet1 = $params['planets'][$i];
                $planet2 = $params['planets'][$j];
                $planetName1 = IAA::PLANET_NAMES[$planet1];
                $planetName2 = IAA::PLANET_NAMES[$planet2];
                $aspectCode = "$planet1-$planet2";
                $infile = $dirname . DS . $aspectCode . '.csv';
                $dist = distrib::loadFromCSV($infile, header:false);
                [$html_markup, $file_contents] = bar::svg(
                    data:           $dist,
                    title:          "Aspects $planetName1 / $planetName2 at wedding date",
                    svg_separate:   $params['svg-separate'],
                    img_src:        $params['svg-path'] . "/W/aspects/$aspectCode.svg",
                    img_alt:        "Aspects $planetName1 / $planetName2 at wedding date",
                    barW:           2,
                    xlegends:       ['min', 'max'],
                    ylegends:       ['min', 'max', 'mean'],
                    ylegendsRound:  1,
                );
                $res .= '<div id="aspect-' . $aspectCode . '"></div>';
                $res .= $html_markup;
                if($params['svg-separate'] == true){
                    fileSystem::saveFile($svgdir . DS . $aspectCode . '.svg', $file_contents);
                }
            }
        }
        //
        $res .= footer::html();
        return $res;
    }
    
}// end class
