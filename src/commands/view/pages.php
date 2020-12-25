<?php
/******************************************************************************
    Generates SVG horizontal chart bar from the column of a file
    
    TODO add 'in-data' parameter ; in-data|(input-file & col)
    TODO add 'out-data' parameter ; out-data|(ouput-file)
    
    @license    GPL
    @history    2020-12-21 13:02:28+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\view;

use observe\Observe;
use observe\patterns\Command;
use observe\ObserveException;
use tiglib\arrays\csvAssociative;
use tiglib\arrays\csvRegular;

class pages implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters                                                     
        //
        $classname = 'pages'; // TODO copute by reflection
        if(!isset($params['input-dir'])){
            throw new ObserveException("$classname needs a parameter 'input-dir'");
        }
        $indir = $params['input-dir'];
        if(!is_dir($indir)){
            throw new ObserveException("Input directory does not exist : $indir");
        }
        //
        if(!isset($params['output-dir'])){
            throw new ObserveException("$classname needs a parameter 'output-dir'");
        }
        $outdir = $params['output-dir'];
        if(!is_dir($outdir)){
            throw new ObserveException("Input directory does not exist : $outdir");
        }
        //
        if(!isset($params['view'])){
            throw new ObserveException("$classname needs a parameter 'view'");
        }
        // view details
        if(!isset($params['view']['command'])){
            throw new ObserveException("$classname - 'view' needs a parameter 'command'");
        }
        $viewClassname = 'observe\\commands\\' . $params['view']['command'];
        if(!class_exists($viewClassname)){
            throw new ObserveException("Invalid key 'command' in step '$stepStr' : class $viewClassname does not exist");
        }
        $viewMethod = new \ReflectionMethod("$viewClassname::execute");
        $viewDefaultParams = $params['view'];
        unset($viewDefaultParams['command']);
        //
        if(!isset($params['pages'])){
            throw new ObserveException("$classname needs a parameter 'pages'");
        }
        // pages details
        for($i=0; $i < count($params['pages']); $i++){
            $page = $params['pages'][$i];
            if(!isset($page['title'])){
                throw new ObserveException("Page " . ($i+1) . "  needs a parameter 'title'");
            }
            if(!isset($page['input-files'])){
                throw new ObserveException("Page " . ($i+1) . "  needs a parameter 'input-files'");
            }
            if(!isset($page['subtitle-template'])){
//                throw new ObserveException("Page " . ($i+1) . "  needs a parameter 'subtitle-template'");
            }
            if(!isset($page['output-file'])){
                throw new ObserveException("Page " . ($i+1) . "  needs a parameter 'output-file'");
            }
        }
        //
        //  execute
        //
        foreach($params['pages'] as $page){
            $res = '';
            $res .= self::pageHeader($page['title']);
            $res .= "<h1>{$page['title']}</h1>";
            $infiles = glob($indir . DS . $page['input-files']);
            $res .= self::pageToc($infiles);
            if(count($infiles) == 0){
                throw new ObserveException("Pattern {$page['input-files']}  does not correspond to existing files");
            }
            foreach($infiles as $infile){
                // compute SVG image
                $viewParams = $viewDefaultParams;
                $viewParams['input-file'] = $infile;
                $viewParams['output-data'] = true;                                                                                                       
                $svg = $viewMethod->invoke(null, $viewParams);
                $anchor = self::anchor($infile);
                $label = $anchor; // TODO change
                $res .= "<h2><a name=\"$anchor\">$label</a></h2>\n";
                $res .= $svg;
            }
            $res .= "</body>\n</html>\n";
            $outfile = $outdir . DS . $page['output-file'];
            file_put_contents($outfile, $res);
            echo "Wrote $outfile\n";
        }
    }
    
    // ******************************************************
    /** Builds page's table of contents **/
    private static function pageToc($infiles){
        $res = "<nav class=\"toc\">\n";
        foreach($infiles as $infile){
            $anchor = self::anchor($infile);
            $label = $anchor; // TODO change
            $res .= "<div><a href=\"#$anchor\">$label</a></div>\n";
        }
        $res .= '</nav>' . "\n";
        return $res;
    }
    
    // ******************************************************
    /** Builds page's table of contents **/
    private static function anchor($infile){
        $pathinfo = pathinfo($infile);
        return str_replace('.' . $pathinfo['extension'], '', $pathinfo['basename']);
    }
    
    // ******************************************************
    private static function pageHeader($title){
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>$title</title>
    <style>
        body{
            padding:0.5rem;
            background:#eee;
            font-family:Arial,Helvetica,sans-serif;
        }
        h1{
            width:100%;
            margin:auto;
            text-align:center;
            padding:0.3rem;
            margin:1rem 0;
        }
        .toc div{
            display:inline-block;
            padding-right:2rem;
        }
        svg{
            margin:1rem;
            background:lightyellow;
            border:1px solid grey;
        }
    </style>
</head>
<body>

HTML;
    }
    
}// end class
