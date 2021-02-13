<?php
/******************************************************************************
    Builds the page of a person in a MFCW (mother, father, child, mariage) context
    
    @license    GPL
    @history    2021-02-08 15:38:31+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

use observe\Observe;
use observe\patterns\Command;
use observe\ObserveException;
use tiglib\arrays\csvAssociative;
use observe\parts\person\pageHeader;
    
class person implements Command {
    
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
        $intro = self::$params['intro'] ?? '';
        $intro = nl2br(trim($intro), false);
        $res = '';
        $res .= pageHeader::execute(
            desc: self::$params['description'] ?? '',
            title: = self::$params['title'] ?? '',
            intro: $intro,
            //intro: nl2br(trim(self::$params['intro'] ?? ''), false),
        );
        foreach(['M', 'F'] as $k){
            $v = constant("observe\commands\mfc\MFC::$k");
            // $const =  new \ReflectionClassConstant('MFC' , $k);
            //$v = eval("MFC::$k");
            //$v = MFC::$k;
            $res .= <<<HTML
        <li>
            <b>$k - $v</b>
            <ul>
                <li>
                    <b>Birth</b>
                    <span class="padding-left"><a href="#$k-birthyear">years</a>
                    <span class="padding-left"><a href="#$k-birthday">days</a>
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
            <b>$k - $v</b>
            <ul>
                <li>
                    <a href="#$k-proportion">Proportion</a>
                </li>
                <li>
                    <b>Date</b>
                    <span class="padding-left"><a href="#$k-year">years</a>
                    <span class="padding-left"><a href="#$k-hday">days</a>
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
            <b>$k - $v</b>
            <ul>
                <li>
                    <b>Date</b>
                    <span class="padding-left"><a href="#$k-year">years</a>
                    <span class="padding-left"><a href="#$k-hday">days</a>
                </li>
                <li>
                    <b><a href="$k-planets.html">Planets</a></b>
                </li>
            </ul>
        </li>
HTML;
        $res .= <<<HTML
        <li class="padding-top">
            <div><b>Inter-aspects</b></div>
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

<!-- ************************************* -->
<h3>-- PROTOTYPE --</h3>
<ul>
    <li>Each link will point to curve(s) with the distribution(s).</li>
    <li>These curves will show original and/or random data.</li>
    <li>For each curve : display min, max, mean, median ?</li>
</ul>
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
