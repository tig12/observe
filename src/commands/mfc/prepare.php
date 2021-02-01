<?php
/******************************************************************************

    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

use observe\Observe;
use observe\patterns\Command;
use observe\ObserveException;
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
        // index
        //
        $filename = $outputDir . DS . 'index.html';
        file_put_contents($filename, self::computeIndex());
        echo "Wrote $filename\n";
        //
        // css
        //
        $in = dirname(dirname(__DIR__)) . DS . 'widgets' . DS . 'static' . DS . 'style.css';
        $out = $outputDir . DS . 'static' . DS . 'style.css';
        copy($in, $out);
        echo "Wrote $out\n";
        $in = dirname(dirname(__DIR__)) . DS . 'widgets' . DS . 'static' . DS . 'observe.css';
        $out = $outputDir . DS . 'static' . DS . 'observe.css';
        copy($in, $out);
        echo "Wrote $out\n";
    }
    
    // ******************************************************
    /** Creates file index.html **/
    private static function computeIndex(){
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

<body>

<header>
<h1>$title</h1>

<div class="intro">
$intro
</div>
</header>

<article>
<div class="toc">
    <ul>
HTML;
        foreach(['M', 'F'] as $k){
            $v = constant("observe\commands\mfc\MFC::$k");
            // $const =  new \ReflectionClassConstant('MFC' , $k);
            //$v = eval("MFC::$k");
            //$v = MFC::$k;
            $res .= <<<HTML
        <li>
            <b>$v</b>
            <ul>
                <li>
                    <b>Birth</b>
                    <span class="padding-left"><a href="#$k-birthyear">years</a>
                    <span class="padding-left"><a href="#$k-birthday">days</a>
                    <span class="padding-left"><a href="#$k-birthour">hours</a>
                    <span class="padding-left"><a href="#$k-birtminute">minutes</a>
                </li>
                <li>
                    <b>Age</b>
                    <span class="padding-left"><a href="$k-age-C.html">at child</a>
                    <span class="padding-left"><a href="$k-age-W.html">at wedding</a>
                </li>
                <li>
                    <b><a href="$k-planets.html">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        } // end M F
        $k = 'W';
        $v = "Wedding";
        $res .= <<<HTML
        <li>
            <b>$v</b>
            <ul>
                <li>
                    <a href="#$k-proportion">Proportion</a>
                </li>
                <li>
                    <b>Date</b>
                    <span class="padding-left"><a href="#$k-year">years</a>
                    <span class="padding-left"><a href="#$k-hday">days</a>
                    <span class="padding-left"><a href="#$k-hour">hours</a>
                    <span class="padding-left"><a href="#$k-minute">minutes</a>
                </li>
                <li>
                    <b><a href="$k-planets.html">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;                                                                                                                                      
        $k = 'C';
        $v = "Child";
        $res .= <<<HTML
        <li>
            <b>$v</b>
            <ul>
                <li>
                    <b>Date</b>
                    <span class="padding-left"><a href="#$k-year">years</a>
                    <span class="padding-left"><a href="#$k-hday">days</a>
                    <span class="padding-left"><a href="#$k-hour">hours</a>
                    <span class="padding-left"><a href="#$k-minute">minutes</a>
                </li>
                <li>
                    <b><a href="$k-planets.html">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        $res .= <<<HTML
        <li class="padding-top">
            <div><b>Relations</b></div>
HTML;
        foreach(['MF', 'MW', 'MC', 'FW', 'FC', 'WC', ] as $k){
            $v = $k[0] . '-' . $k[1];
            $res .= <<<HTML
            <span class="padding-left"><a href="#$v">$v</a>
HTML;
        } // end relations
        $res .= <<<HTML
        </li>
HTML;
        $res .= <<<HTML
    </ul>
</div>

</article>

</body>
</html>
HTML;
        return $res;
    }
    
}// end class
