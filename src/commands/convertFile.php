<?php
/******************************************************************************

    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\fileSystem;

class convertFile implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        //
        // in-file
        if(!isset($params['in-dir'])){
            throw new ObserveException("$classname needs a parameter 'in-dir'");
        }
        if(!isset($params['in-file'])){
            throw new ObserveException("$classname needs a parameter 'in-file'");
        }
        $infile = $params['in-dir'] . DS . $params['in-file'];
        if(!is_file($infile)){
            throw new ObserveException("File not found : $infile");
        }
        //
        // out-file
        if(!isset($params['out-dir'])){
            throw new ObserveException("$classname needs a parameter 'out-dir'");
        }
        if(!isset($params['out-file'])){
            throw new ObserveException("$classname needs a parameter 'out-file'");
        }
        $outfile = $params['out-dir'] . DS . $params['out-file'];
        fileSystem::mkdir(dirname($outfile));
        //
        // actions
        if(!isset($params['actions'])){
            throw new ObserveException("$classname needs a parameter 'actions'");
        }
        $actions = self::computeActions($params['actions']);
        //
        //  compute actions
        //
        $outcols = [];
        foreach($actions as $action){
            $outcols[] = $action['out-col'];
        }
        //
        // load input file
        //
        $res = implode(Observe::CSV_SEP, $outcols) . "\n";
        
        $in = csvAssociative::compute($infile);
        //
        // execute actions
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
            $res .= implode(Observe::CSV_SEP, $new) . "\n";
            $N++;
            if($N % 100000 == 0) echo "$N\n";
        }
        //
        // write output file
        //
        fileSystem::saveFile($outfile, $res, message: "Wrote $N lines in $outfile\n");
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
                throw new ObserveException("Invalid syntax : $line");
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
                throw new ObserveException("Invalid method name '{$action['method-name']}' in line : $line");
            }
            $method->setAccessible(true);
            $action['method'] = $method;
            $res[] = $action;
        }
        return $res;
    }
    
    // ====================================================================================
    // Implementation of actions
    // All actions have the same parameters :
    //      $inline Assoc array containing the full input line
    //              keys = column names
    //              values = column values
    //      $inCols Array containing the names of the columns to use in $inLine
    //      $outCol Name of the generated column
    //
    // They all return a key value pair : name of the output column => value
    // ====================================================================================
    
    /**
        Converts the content of 3 columns containing year, month, day
        To a column containing a YYYY-MM-DD string
    **/
    private static function ymd2iso($inLine, $inCols, $outCol){
        return [$outCol => $inLine[$inCols[0]] . '-' . $inLine[$inCols[1]] . '-' . $inLine[$inCols[2]]];
    }
    
    /**
        Converts the content of 6 columns containing year, month, day, hour, minute, second
        To a column containing a YYYY-MM-DD HH:MM:SS string
    **/
    private static function ymdhms2iso($inLine, $inCols, $outCol){
        return [$outCol => $inLine[$inCols[0]] . '-' . $inLine[$inCols[1]] . '-' . $inLine[$inCols[2]]];
    }
    
    /** 
        Copies the content of a column to resulting column.
    **/
    private static function copy($inLine, $inCols, $outCol){
        return [$outCol => $inLine[$inCols[0]]];
    }
    
}// end class
