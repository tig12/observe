<?php
/******************************************************************************

    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace distrib\commands;

use distrib\Distrib;
use distrib\patterns\Command;
use distrib\DistribException;
use tiglib\arrays\csvAssociative;

class convertFile implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = 'convertFile'; // TODO copute by reflection
        if(!isset($params['input-file'])){
            throw new DistribException("$classname needs a parameter 'input-file'");
        }
        //
        $infile = $params['input-file'];
        if(!is_file($infile)){
            throw new DistribException("File not found : $infile");
        }
        //
        if(!isset($params['actions'])){
            throw new DistribException("$classname needs a parameter 'actions'");
        }
        $actions = self::computeActions($params['actions']);
        //
        if(!isset($params['output-file'])){
            throw new DistribException("$classname needs a parameter 'output-file'");
        }
        $outfile = $params['output-file'];
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            throw new DistribException("Create directory '$dir' and try again");
        }
        //
        //  execute
        //
        $outcols = [];
        foreach($actions as $action){
            $outcols[] = $action['out-col'];
        }
        //
        $res = implode(Distrib::CSV_SEP, $outcols) . "\n";
        $in = csvAssociative::compute($infile);
        //
        $Nactions = count($actions);
        $N =0;
        foreach($in as $old){
            $new = array_fill_keys($outcols, '');
            for($i=0; $i < $Nactions; $i++){
                $new = array_merge(
                    $new,
                    $actions[$i]['method']->invoke(
                        null,
                        $old,
                        $actions[$i]['in-col'],
                        $actions[$i]['out-col'],
                ));
            }
            $res .= implode(Distrib::CSV_SEP, $new) . "\n";
            $N++;
            if($N % 100000 == 0) echo "$N\n";
        }
        file_put_contents($outfile, $res);
        echo "Wrote $N lines in $outfile\n";
    }
    
    /**
        Parses lines expressing actions, like
            ymd2iso JNAISP MNAISP ANAISP    -> F
        The first word is the method name
        Following words (before "->") represent column names in input file
        The word after "->" represents the name of the resulting column in $outfile
    **/
    private static function computeActions($param){
        $res = [];
        foreach($param as $line){
            $action = [];
            $tmp = explode('->', $line);
            if(count($tmp) != 2){
                throw new DistribException("Invalid syntax : $line");
            }
            $tmp2 = explode(' ', trim($tmp[0]));
            $action['method-name'] = trim($tmp2[0]);
            $action['in-col'] = [];
            $action['out-col'] = trim($tmp[1]);
            for($i=1; $i < count($tmp2); $i++){
                if(trim($tmp2[$i]) == ''){
                    continue;
                }
                $action['in-col'][] = trim($tmp2[$i]);
            }
            // add reflection method to action
            try{
                $method = new \ReflectionMethod(__CLASS__ . '::' . $action['method-name']);
            }
            catch(\ReflectionException $e){
                throw new DistribException("Invalid method name '{$action['method-name']}' in line : $line");
            }
            $method->setAccessible(true);
            $action['method'] = $method;
            $res[] = $action;
        }
        return $res;
    }
    
    /** 
    **/
    private static function ymd2iso($inLine, $inCols, $outCol){
        return [$outCol => $inLine[$inCols[0]] . '-' . $inLine[$inCols[1]] . '-' . $inLine[$inCols[2]]];
    }
    
    /** 
    **/
    private static function copy($inLine, $inCols, $outCol){
        return [$outCol => $inLine[$inCols[0]]];
    }
    
}// end class
