<?php
/******************************************************************************
    Computes one inter-aspects page of a MFCW experiment.
    (inter-aspects = aspects between mother and child, father and child etc.). 
    Auxiliary of all::execute()
    
    @license    GPL
    @history    2021-03-16 05:24:49+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc\pages;

use tigeph\model\IAA;

use observe\commands\mfc\MFC;
use observe\parts\page\header;
use observe\parts\page\footer;
use observe\parts\page\tocInteraspects;
use observe\parts\page\nav;
use observe\parts\stats\distrib;
use observe\parts\draw\bar;
use observe\parts\fileSystem;

class interaspects {
    
    /**
        Computes html page of $member1 - $member2 interaspects
        @param  $params     Parameters passed to all::execute()
        @param  $member1    'M' or 'F' or 'C' or 'W'
        @param  $member2    'M' or 'F' or 'C' or 'W'
    **/
    public static function computePage(&$params, $member1, $member2): string {
        $res = '';
        $label1 = MFC::LABELS[$member1];
        $label2 = MFC::LABELS[$member2];
        $titleString = "$label1 / $label2 inter-aspects";
        $title = $params['experience']['code'] . ' - ' . $titleString;
        $pathToRoot = '../../..';
        $res .= header::html(
            pathToRoot:     $pathToRoot,
            title:          $title,
            description:    '',
        );
        $res .= nav::html(MFC::nav($params), $pathToRoot);
        $res .= "<h1>$title</h1>\n";
        $res .= tocInteraspects::html($params['planets'], $params['planets'], $label1, $label2);
        //
        $dirname = $params['in-dir'] . DS . 'distrib' . DS . "$member1-$member2";
        foreach($params['planets'] as $planet1){
            $planetName1 = IAA::PLANET_NAMES[$planet1];
            $res .= "<h2 id=\"$planet1\">$label1 $planetName1 / $label2 planets</h2>\n";
            foreach($params['planets'] as $planet2){
                $filename = "$dirname/$planet1-$planet2.csv";
                $planetName2 = IAA::PLANET_NAMES[$planet2];
                $dist = distrib::loadFromCSV($filename, header:false);
                $svg = bar::svg(
                    data: $dist,
                    title: "$label1 $planetName1 / $label2 $planetName2 inter-aspects",
                    barW: 2,
                    xlegends: ['min', 'max'],
                    ylegends: ['min', 'max', 'mean'],
                    ylegendsRound: 1,
                );
                $res .= '<div id="' . "$member1-$planet1--$member2-$planet2" . '"></div>';
                $res .= $svg;
            }
        }
        $res .= footer::html();
        return $res;
    }
    
}// end class