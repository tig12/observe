<?php
/******************************************************************************
    Computes mother.html and father.html pages of a MFCW experiment
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use observe\app\Observe;
use observe\commands\mfc\MFC;
use observe\shared\page\header;
use observe\shared\page\footer;
use observe\shared\page\toc;
use observe\shared\page\tocPlanets;
use observe\shared\page\tocAspects;
use observe\shared\page\nav;
use observe\shared\distrib\csvDistrib;
use observe\shared\stats\misc;
use observe\shared\fileSystem;
use tigdraw\bar;
use tigeph\model\IAA;

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
            '<li class="padding-top05"><a href="#planets">Planets at birth</a><div class="padding-left">' . $tocPlanets . '</div></li>',
            $toc
        );
        $tocAspects = tocAspects::html($params['planets']);
        $toc = str_replace(
            '<li><a href="#aspects">Aspects at birth</a></li>',
            '<li class="padding-top05"><a href="#aspects">Aspects at birth</a><div class="padding-left">' . $tocAspects . '</div></li>',
            $toc
        );
        $res .= $toc;
        
        //
        //  =============== y-m-d ===============
        //
        if($params['svg-separate'] == true){
// HERE - confusion between img src and filesystem
// Also in all other calls to bar::svg()
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . $MF;
            fileSystem::mkdir($svgdir);
        }
        //
        // year
        //
        $infile = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'year.csv';
        $dist = csvDistrib::csv2distrib($infile, header:false, sep:Observe::CSV_SEP);
        $stats = [
            'mean' => misc::mean($dist),
        ];
        [$stats['top-key'], $stats['top-key-index']] = misc::topKey($dist);
        [$html_markup, $file_contents] = bar::svg(
            data:           $dist,
            title:          "$MFucstring - year of birth",
            svg_separate:   $params['svg-separate'],
// HERE - confusion between img src and filesystem
            img_src:        $params['svg-path'] . "/$MF/year.svg",
            img_alt:        "$MFucstring - year of birth",
            barW:           8,
            xlegends:       ['min', 'max', 'top'],
            ylegends:       ['min', 'max', 'mean'],
            ylegendsRound:  1,
            stats:          $stats,
            meanLine:       true,
        );
        $res .= '<div id="birthyear"></div>';
        $res .= $html_markup;
        if($params['svg-separate'] == true){
            fileSystem::saveFile($svgdir . DS . 'year.svg', $file_contents);
        }
        //
        // day
        //
        $infile = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'day.csv';
        $dist = csvDistrib::csv2distrib($infile, header:false, sep:Observe::CSV_SEP);
        $stats = [
            'mean' => misc::mean($dist),
        ];
        [$html_markup, $file_contents] = bar::svg(
            data:           $dist,
            title:          "$MFucstring - day of birth",
            svg_separate:   $params['svg-separate'],
            img_src:        $params['svg-path'] . "/$MF/day.svg",
            img_alt:        "$MFucstring - day of birth",
            barW:           2,
            xlegends:       ['min', 'max'],
            ylegends:       ['min', 'max', 'mean'],
            ylegendsRound:  1,
            stats:          $stats,
            meanLine:       true,
        );
        $res .= '<div id="birthday"></div>';
        $res .= $html_markup;
        if($params['svg-separate'] == true){
            fileSystem::saveFile($svgdir . DS . 'day.svg', $file_contents);
        }
        //
        // age at child birth
        //
        $infile = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'age-child.csv';
        $dist = csvDistrib::csv2distrib($infile, header:false, sep:Observe::CSV_SEP);
        $stats = [
            'mean' => misc::mean($dist),
        ];
        [$stats['top-key'], $stats['top-key-index']] = misc::topKey($dist);
        [$html_markup, $file_contents] = bar::svg(
            data:           $dist,
            title:          "$MFucstring - age at child birth",
            svg_separate:   $params['svg-separate'],
            img_src:        $params['svg-path'] . "/$MF/age-child.svg",
            img_alt:        "$MFucstring - age at child birth",
            barW:           8,
            xlegends:       ['min', 'max', 'top'],
            ylegends:       ['min', 'max', 'mean'],
            ylegendsRound:  1,
            meanLine:       true,
            stats:          $stats,
        );
        $res .= '<div id="age-C"></div>' . "\n";
        $res .= '<div style="float:left;">' . "\n";
        $res .= $html_markup;
        $res .= '</div><!-- end float-left -->' . "\n";
        if($params['svg-separate'] == true){
            fileSystem::saveFile($svgdir . DS . 'age-child.svg', $file_contents);
        }
        //
        // age at wedding
        //
        if($params['experience']['has-wedding'] === true){
            $infile = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'age-wed.csv';
            $dist = csvDistrib::csv2distrib($infile, header:false, sep:Observe::CSV_SEP);
            $stats = [
                'mean' => misc::mean($dist),
            ];
            [$stats['top-key'], $stats['top-key-index']] = misc::topKey($dist);
            [$html_markup, $file_contents] = bar::svg(
                data:           $dist,
                title:          "$MFucstring - age at wedding",
                svg_separate:   $params['svg-separate'],
                img_src:        $params['svg-path'] . "/$MF/has-wedding.svg",
                img_alt:        "$MFucstring - age at wedding",
                barW:           8,
                xlegends:       ['min', 'max', 'top'],
                ylegends:       ['min', 'max', 'mean'],
                ylegendsRound:  1,
                meanLine:       true,
                stats:          $stats,
            );
            $res .= '<div id="age-W"></div>';
            $res .= '<div style="float:left;">' . "\n";
            $res .= $html_markup;
            $res .= '</div><!-- end float-left -->' . "\n";
            $res .= '<br style="clear:left;">' . "\n";
            if($params['svg-separate'] == true){
                fileSystem::saveFile($svgdir . DS . 'has-wedding.svg', $file_contents);
            }
        }
        
        //
        //  =============== planets ===============
        //
        if($params['svg-separate'] == true){
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . $MF . DS . 'planets';
            fileSystem::mkdir($svgdir);
        }
        //
        $res .= '<h2 id="planets">Planets at birth</h2>';
        $res .= '<div class="padding-left">' . $tocPlanets . '</div>';
        $indir = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'planets';
        foreach($params['planets'] as $planet){
            $planetName = IAA::PLANET_NAMES[$planet];
            $infile = $indir . DS . $planet . '.csv';
            $dist = csvDistrib::csv2distrib($infile, header:false, sep:Observe::CSV_SEP);
            $stats = [
                'mean' => misc::mean($dist),
            ];
            [$html_markup, $file_contents] = bar::svg(
                data:           $dist,
                title:          "$MFucstring - $planetName at birth",
                svg_separate:   $params['svg-separate'],
                img_src:        $params['svg-path'] . "/$MF/planets/$planet.svg",
                img_alt:        "$MFucstring - $planetName at birth",
                barW:           2,
                xlegends:       ['min', 'max'],
                ylegends:       ['min', 'max', 'mean'],
                ylegendsRound:  1,
                stats:          $stats,
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
            $svgdir = $params['out-dir'] . DS . $params['svg-path'] . DS . $MF . DS . 'aspects';
            fileSystem::mkdir($svgdir);
        }
        //
        $res .= '<h2 id="aspects">Aspects at birth</h2>';
        $res .= '<div class="padding-left">' . $tocAspects . '</div>';
        $indir = $params['in-dir'] . DS . 'distrib' . DS . $MF . DS . 'aspects';
        for($i=0; $i < count($params['planets']); $i++){
            for($j=$i+1; $j < count($params['planets']); $j++){
                $planet1 = $params['planets'][$i];
                $planet2 = $params['planets'][$j];
                $planetName1 = IAA::PLANET_NAMES[$planet1];
                $planetName2 = IAA::PLANET_NAMES[$planet2];
                $aspectCode = "$planet1-$planet2";
                $infile = $indir . DS . $aspectCode . '.csv';
                $dist = csvDistrib::csv2distrib($infile, header:false, sep:Observe::CSV_SEP);
                $stats = [
                    'mean' => misc::mean($dist),
                ];
                [$html_markup, $file_contents] = bar::svg(
                    data:           $dist,
                    title:          "$MFucstring - Aspects $planetName1 / $planetName2 at birth",
                    svg_separate:   $params['svg-separate'],
                    img_src:        $params['svg-path'] . "/$MF/aspects/$aspectCode.svg",
                    img_alt:        "$MFucstring - Aspects $planetName1 / $planetName2 at birth",
                    barW:           2,
                    xlegends:       ['min', 'max'],
                    ylegends:       ['min', 'max', 'mean'],
                    ylegendsRound:  1,
                    stats:          $stats,
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
