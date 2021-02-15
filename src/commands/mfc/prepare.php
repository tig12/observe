<?php
/******************************************************************************
    First step of report generation for a MFC group
    Generates index.html and copies CSS files to ouptut directory.
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

class prepare implements Command {
    
    /** Parameters passed to execute() **/
    private static $params;
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        if(!isset($params['output-dir'])){
            throw new ObserveException("$classname needs a parameter 'output-dir'");
        }
        self::$params = $params;
        //
        //  execute
        //
        $outputDir = $params['output-dir'];
        if(!is_dir($outputDir)){
            echo "Created directory $outputDir\n";
            mkdir($outputDir, 0755, true);
        }
        //
        $staticDir = $outputDir . DS . 'static';
        if(!is_dir($staticDir)){
            echo "Created directory $staticDir\n";
            mkdir($staticDir, 0755, true);
        }
        //
        // index.html
        //
        $output = '';
        $output .= self::indexBegin();
        $output .= self::indexEnd();
        //
        $filename = $outputDir . DS . 'index.html';
        file_put_contents($filename, $output);
        echo "Wrote $filename\n";
        //
        // css
        //
        $in = dirname(dirname(__DIR__)) . DS . 'parts' . DS . 'static' . DS . 'style.css';
        $out = $outputDir . DS . 'static' . DS . 'style.css';
        copy($in, $out);
        echo "Wrote $out\n";
        $in = dirname(dirname(__DIR__)) . DS . 'parts' . DS . 'static' . DS . 'observe.css';
        $out = $outputDir . DS . 'static' . DS . 'observe.css';
        copy($in, $out);
        echo "Wrote $out\n";
    }
    
    // ******************************************************
    /**
        Computes the beginning of index.html
    **/
    private static function indexBegin(){
        $desc = self::$params['description'] ?? '';
        $title = self::$params['title'] ?? '';
        $intro = self::$params['intro'] ?? '';
        $intro = nl2br(trim($intro), false);
        $res = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>$title</title>
    <meta name="description" content="$desc">
    <link rel="stylesheet" href="static/observe.css" type="text/css">
</head>

<body class="index">

<header>
<h1>$title</h1>
</header>

<article>
<table><tr>
<td class="vertical-align-top padding-right2">
    <ul>
HTML;
        foreach(['M', 'F'] as $k){
            $v = constant("observe\commands\mfc\MFC::$k");
            $page = strtolower($v) . '.html';
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
                    <span class="padding-left"><a href="$page#age-C.html">at child birth</a>
                    <span class="padding-left"><a href="$page#age-W.html">at wedding</a>
                </li>
                <li>
                    <b><a href="$page#planets">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        } // end M F
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
                <li>
                    <b><a href="$page#planets">Planets</a></b>
                </li>
            </ul>
        </li>
    </ul>
HTML;
        $res .= <<<HTML
</td>
<td class="vertical-align-top border-left">
    <ul>
        <li>
            <div><b>Inter-aspects</b></div>
HTML;
        foreach(['MF', 'MW', 'MC', 'FW', 'FC', 'WC', ] as $k){
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
        return $res;
    }
    
    // ******************************************************
    /**
        Computes the end of index.html
    **/
    private static function indexEnd(){
        $res = '';
        $res .= <<<HTML
</article>
</body>
</html>
HTML;
        return $res;
    }
    
}// end class
