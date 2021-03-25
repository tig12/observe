<?php
/******************************************************************************
    Computes mother.html and father.html pages of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2021-03-07 17:00:42+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

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
use tigeph\model\IAA;

class C {
    
    /**
        TOC = Table of contents
        Correct when $params['experience']['has-wedding'] = true
    **/
    private static $toc = [
        'birthday' => 'Day of birth',
        'birthyear' => 'Year of birth',
        'age-W' => 'Duration between parents\' wedding and birth',
        'planets' => 'Planets at birth',
        'aspects' => 'Aspects at birth',
    ];
    
    /**
        @param $params  Parameters passed to all::execute()
    **/
    public static function computePage(&$params): string {
        $res = '';
        $titleString = 'child';
        $titleUCString = ucfirst($titleString);
        $title = $params['experience']['code'] . ' - ' . $titleUCString;
        $pathToRoot = '../../..';
        $res .= header::html(
            pathToRoot:     $pathToRoot,
            title:          $title,
            description:    '',
        );
        
        $res .= "<h1>$title</h1>\n";
        $res .= nav::html(MFC::nav($params), $pathToRoot);
        if(!$params['experience']['has-wedding']){
            unset(self::$toc['age-W']);
        }
        if(!$params['child-by-year']){
            unset(self::$toc['birthyear']);
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
        //  =============== y-m-d ===============
        //
        if($params['svg-separate'] == true){
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . 'C';
            fileSystem::mkdir($svgdir);
        }
        //
        // year
        //
        if($params['child-by-year'] === true){
            $infile = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'year.csv';
            $dist = distrib::loadFromCSV($infile, header:false);
            [$html_markup, $file_contents] = bar::svg(
                data: $dist,
                title: "$titleUCString - year of birth",
                svg_separate:   $params['svg-separate'],
                img_src:        $params['svg-path'] . "/C/year.svg",
                barW:           8,
                xlegends:       ['min', 'max', 'top'],
                ylegends:       ['min', 'max', 'mean'],
                ylegendsRound:  1,
                meanLine:       true,
            );
            $res .= '<div id="birthyear"></div>';
            $res .= $html_markup;
            if($params['svg-separate'] == true){
                fileSystem::saveFile($svgdir . DS . 'year.svg', $file_contents);
            }
        }

        //
        // day
        //
        $infile = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'day.csv';
        $dist = distrib::loadFromCSV($infile, header:false);
        [$html_markup, $file_contents] = bar::svg(
            data:           $dist,
            title:          "$titleUCString - day of birth",
            svg_separate:   $params['svg-separate'],
            img_src:        $params['svg-path'] . "/C/day.svg",
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
        // "age at wedding" = duration [wedding - birth]
        //
        if($params['experience']['has-wedding'] === true){
            $infile = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'wed-birth.csv';
            $dist = distrib::loadFromCSV($infile, header:false);
            [$html_markup, $file_contents] = bar::svg(
                data:           $dist,
                title:          "$titleUCString - Duration between parents' wedding and birth (in months)",
                svg_separate:   $params['svg-separate'],
                img_src:        $params['svg-path'] . "/C/wed-birth.svg",
                barW: 2,
                xlegends:       ['min', 'max'],
                ylegends:       ['min', 'max', 'mean'],
                ylegendsRound:  1,
                meanLine:       true,
            );
            $res .= '<div id="age-W"></div>';
            $res .= $html_markup;
            if($params['svg-separate'] == true){
                fileSystem::saveFile($svgdir . DS . 'wed-birth.svg', $file_contents);
            }
        }
        
        //
        //  =============== planets ===============
        //
        if($params['svg-separate'] == true){
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . 'C' . DS . 'planets';
            fileSystem::mkdir($svgdir);
        }
        //
        $res .= '<h2 id="planets">Planets at birth</h2>';
        $res .= '<div class="padding-left">' . $tocPlanets . '</div>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'planets';
        foreach($params['planets'] as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $infile = $dirname . DS . $planet . '.csv';
            $dist = distrib::loadFromCSV($infile, header:false);
            [$html_markup, $file_contents] = bar::svg(
                data:           $dist,
                title:          "Child - $planetName at birth",
                svg_separate:   $params['svg-separate'],
                img_src:        $params['svg-path'] . "/C/planets/$planet.svg",
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
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . 'C' . DS . 'aspects';
            fileSystem::mkdir($svgdir);
        }
        $res .= '<h2 id="aspects">Aspects at birth</h2>';
        $res .= '<div class="padding-left">' . $tocAspects . '</div>';
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . 'C' . DS . 'aspects';
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
                    title:          "Child - Aspects $planetName1 / $planetName2 at birth",
                    svg_separate:   $params['svg-separate'],
                    img_src:        $params['svg-path'] . "/C/aspects/$aspectCode.svg",
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
