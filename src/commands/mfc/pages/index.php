<?php
/******************************************************************************
    Computes index.html page of a MFCW experience
    Auxiliary of all::compute()
    
    @license    GPL
    @history    2021-02-28 22:09:39+01:00, Thierry Graff : Creation from former class pages
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation of observe\commands\mfc\pages
********************************************************************************/
namespace observe\commands\mfc\pages;

use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\page\headfoot;

class index {
    
    /**
        @param $params  Parameters passed to all::execute()
    **/
    public static function computePage(&$params): string {
        
        $res = '';
        
        $intro = $params['experience']['intro'] ?? '';
        $title = $params['experience']['title'] ?? '';
        $subtitle = $params['experience']['subtitle'] ?? '';
        $description = $params['experience']['description'] ?? '';
        
        $res .= headfoot::header(
            pathToRoot:     '../../..',
            title:          $title,
            description:    $description,
        );
        
        $intro = nl2br(trim($intro), false);
        $res .= <<<HTML
<h1>
    $title
    <div class="subtitle">$subtitle</div>
</h1>

HTML;
        
        $res .= <<<HTML
<table><tr>
<td class="vertical-align-top padding-right2">
    <ul class="naked">
HTML;
        //
        // M - F
        //
        foreach(['M', 'F'] as $k){
            $v = constant("observe\commands\mfc\MFC::$k");
            $page = strtolower($v) . '.html';
            $ageAtWeddingStr = $params['wedding'] === true ? '<span class="padding-left"><a href="' . $page . '#age-W">at wedding</a>' : '';
            $res .= <<<HTML
        <li>
            <b><a href="$page">$k - $v</a></b>
            <ul>
                <li>
                    <b>Birth</b>
                    <span class="padding-left"><a href="$page#birthyear">years</a>
                    <span class="padding-left"><a href="$page#birthday">days</a>
                </li>
                <li>
                    <b>Age</b>
                    <span class="padding-left"><a href="$page#age-C">at child birth</a>
                    $ageAtWeddingStr
                </li>
                <li>
                    <b><a href="$page#planets">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        } // end M F
        //
        // C
        //
        $k = 'C';
        $v = "Child";
        $page = strtolower($v) . '.html';
        $res .= <<<HTML
        <li>
            <b><a href="$page">$k - $v</a></b>
            <ul>
                <li>
                    <b>Date</b>
                    <span class="padding-left"><a href="$page#birthday">days</a>
                </li>
HTML;
        if($params['wedding']){
            $res .= <<<HTML
                <li>
                    <b>Age</b>
                    <span class="padding-left"><a href="$page#age-W">at wedding</a>
                </li>
HTML;
        }
        $res .= <<<HTML
                <li>
                    <b><a href="$page#planets">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        //
        // W
        //
        if($params['wedding'] === true){
            $k = 'W';
            $v = "Wedding";
            $page = strtolower($v) . '.html';
            $res .= <<<HTML
        <li>
            <b><a href="$page">$k - $v</a></b>
            <ul>
                <li>
                    <a href="$page#proportion">Proportion</a>
                </li>
                <li>
                    <b>Date</b>
                    <span class="padding-left"><a href="$page#year">years</a>
                    <span class="padding-left"><a href="$page#day">days</a>
                </li>
                <li>
                    <b><a href="$page#planets">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        }
        $res .= "    </ul>\n";
        //
        // relations
        //
        $res .= <<<HTML
</td>
<td class="vertical-align-top border-left">
    <ul class="naked">
        <li>
            <div><b>Inter-aspects</b></div>
HTML;
        $combinations = 
            $params['wedding'] === true
            ? ['MF', 'MW', 'MC', 'FC', 'FW', 'CW']
            : ['MF', 'MC', 'FC'];
        foreach($combinations as $k){
            $v0 = constant('observe\commands\mfc\MFC::' . $k[0]);
            $v1 = constant('observe\commands\mfc\MFC::' . $k[1]);
            $page = strtolower($v0) . '-' . strtolower($v1) . '.html';
            $res .= <<<HTML
            <div class="padding-left"><a href="$page">$v0 - $v1</a></div>
HTML;
        } // end relations
        $res .= <<<HTML
        </li>
HTML;
        $res .= <<<HTML
    </ul>
</td>
</tr></table>
<div class="intro">
$intro
</div>
HTML;

        $res .= headfoot::footer();

        return $res;
    }
    
}// end class
