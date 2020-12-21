<?php
/******************************************************************************
    Generates SVG horizontal chart bar from the column of a file
    
    TODO add 'in-data' parameter ; in-data|(input-file & col)
    TODO add 'out-data' parameter ; out-data|(ouput-file)
    
    @license    GPL
    @history    2020-12-21 13:02:28+01:00, Thierry Graff : Creation
********************************************************************************/
namespace distrib\commands\view;

use distrib\Distrib;
use distrib\patterns\Command;
use distrib\DistribException;
use tiglib\arrays\csvAssociative;
use tiglib\arrays\csvRegular;

class pages implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters                                                     
        //
        $classname = 'pages'; // TODO copute by reflection
        if(!isset($params['input-dir'])){
            throw new DistribException("$classname needs a parameter 'input-dir'");
        }
        $indir = $params['input-dir'];
        if(!is_dir($indir)){
            throw new DistribException("Input directory does not exist : $indir");
        }
        //
        if(!isset($params['output-dir'])){
            throw new DistribException("$classname needs a parameter 'output-dir'");
        }
        $outdir = $params['output-dir'];
        if(!is_dir($outdir)){
            throw new DistribException("Input directory does not exist : $outdir");
        }
        //
        if(!isset($params['view'])){
            throw new DistribException("$classname needs a parameter 'view'");
        }
        // view details
        if(!isset($params['view']['command'])){
            throw new DistribException("$classname - 'view' needs a parameter 'command'");
        }
        $viewClassname = 'distrib\\commands\\' . $params['view']['command'];
        if(!class_exists($viewClassname)){
            throw new DistribException("Invalid key 'command' in step '$stepStr' : class $viewClassname does not exist");
        }
        $viewMethod = new \ReflectionMethod("$viewClassname::execute");
        $viewDefaultParams = $params['view'];
        unset($viewDefaultParams['command']);
        //
        if(!isset($params['pages'])){
            throw new DistribException("$classname needs a parameter 'pages'");
        }
        // pages details
        for($i=0; $i < count($params['pages']); $i++){
            $page = $params['pages'][$i];
            if(!isset($page['title'])){
                throw new DistribException("Page " + ($i+1) + "  needs a parameter 'title'");
            }
            if(!isset($page['input-files'])){
                throw new DistribException("Page " + ($i+1) + "  needs a parameter 'input-files'");
            }
            if(!isset($page['subtitle-template'])){
                throw new DistribException("Page " + ($i+1) + "  needs a parameter 'subtitle-template'");
            }
            if(!isset($page['output-file'])){
                throw new DistribException("Page " + ($i+1) + "  needs a parameter 'output-file'");
            }
        }
        
        //
        //  execute
        //
        foreach($params['pages'] as $page){
            $res = '';
            $res .= self::pageHeader($page['title']);
            $infiles = glob($indir . DS . $page['input-files']);
            $res .= self::pageToc($infiles);
            if(count($infiles) == 0){
                throw new DistribException("Pattern {$page['input-files']}  does not correspond to existing files");
            }
            foreach($infiles as $infile){
                // compute SVG image
                $viewParams = $viewDefaultParams;
                $viewParams['input-file'] = $infile;
                $viewParams['output-data'] = true;
                $svg = $viewMethod->invoke(null, $viewParams);
//echo $svg;
exit;
            }
            $res .= "</body>\n</html>\n";


exit;
            
            file_put_contents($outfile, $res);
            echo "Wrote $outfile\n";
        }
    }
    
    // ******************************************************
    /** Builds page's table of contents **/
    private static function pageToc($infiles){
        foreach($infiles as $infile){
echo "$infile\n";
            $pathinfo = pathinfo($infile);
            $anchor = str_replace('.' . $pathinfo['extension'], '', $pathinfo['basename']);
echo "\n"; print_r($pathinfo); echo "\n";
echo "$anchor\n";
exit;
        }
    
    }
    
    // ******************************************************
    private static function pageHeader($title){
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>$title</title>
</head>
<body>

HTML;
    }
    
}// end class
