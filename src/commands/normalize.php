<?php
/******************************************************************************
    
    Converts an input csv file in a normalized format, usable by other commands
    
    Benchmark:
        php run-observe.php castille/a00 normalize
        
        With yield:
        dt = 0.638392
        N = 591936
        
        with ~file_get_contents
        dt = 7.314358
        N = 591936
    
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use observe\parts\fileSystem;
use tiglib\arrays\yieldCsv;
use tiglib\arrays\yieldCsvAsso;

class normalize implements Command {
    
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
        if(!isset($params['in-file-associative'])){
            throw new ObserveException("$classname needs a parameter 'in-file-associative' (boolean)");
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
        //  compute $outcols
        //
        $outcols = [];
        foreach($actions as $action){
            $outcols[] = $action['out-col'];
        }
        //
        // loop on input file - benefit from yield
        //
        $res = implode(Observe::CSV_SEP, $outcols) . "\n";
//$infile = '/home/thierry/share/astrostat/data/castille/a00/a00.csv';
//$infile = '/home/thierry/dev/astrostats/observe/tmp/experiences/castille/a00/planets-small/C.csv';
//$infile = '/home/thierry/dev/astrostats/observe/tmp/experiences/castille/a00/planets-full/C.csv';
        $N =0;
        $Nactions = count($actions);
        $t1 = microtime(true);
        $lines = $params['in-file-associative']
               ? yieldCsvAsso::loop($infile)
               : yieldCsv::loop($infile);
        foreach ($lines as $old) {
            //
            // execute actions
            //
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
        $t2 = microtime(true);
        $dt = round($t2-$t1, 6);
        //
        // write output file
        //
        fileSystem::saveFile($outfile, $res, message: "Wrote $N lines in $outfile ($dt seconds)\n");
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
                throw new ObserveException("Invalid syntax in command file (normalize / actions) : $line");
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
    
    /* 
    ====================================================================================
    Implementation of actions
    All actions have the same parameters :
         $inline Associative or regular array containing the full input line
                 keys = column names
                 values = column values
         $inCols Regular array containing the names of the columns to use in $inLine
                 If $inline is an associative array, $inCols must contain the keys of the concerned columns
                 If $inline is a regular array, $inCols must contain the indexed of the concerned columns (starting with 0)
                 Example for a regular array : $inline = [0, 1, 2]
         $outCol Name of the generated column
    //
    They all return a key value pair : name of the output column => value
    ====================================================================================
    */
    
    /**
        Converts the content of 3 columns containing year, month, day
        To a column containing a YYYY-MM-DD string
        @param  $inCols Array containing 3 elements,
                designating the columns containing year, month, day
    **/
    private static function ymd2iso(array $inLine, array $inCols, string $outCol){
        return [
            $outCol =>
                    $inLine[$inCols[0]]
            . '-' . str_pad($inLine[$inCols[1]], 2, 0)
            . '-' . str_pad($inLine[$inCols[2]], 2, 0)
        ];
    }
    
    /**
        Converts the content of 6 columns containing year, month, day, hour, minute, second
        To a column containing a YYYY-MM-DD HH:MM:SS string
        @param  $inCols Array containing 6 elements,
                designating the columns containing year, month, day, hour, minute, seconds
    **/
    private static function ymdhms2iso(array $inLine, array $inCols, string $outCol){
        return [
            $outCol =>
                        $inLine[$inCols[0]]                                 // Y
                . '-' . str_pad($inLine[$inCols[1]], 2, '0', STR_PAD_LEFT)  // M
                . '-' . str_pad($inLine[$inCols[2]], 2, '0', STR_PAD_LEFT)  // D
                . ' ' . str_pad($inLine[$inCols[3]], 2, '0', STR_PAD_LEFT)  // H
                . ':' . str_pad($inLine[$inCols[4]], 2, '0', STR_PAD_LEFT)  // M
                . ':' . str_pad($inLine[$inCols[5]], 2, '0', STR_PAD_LEFT)  // S
        ];
    }
    
    /** 
        Converts a string like "2E20" to a decimal latitude.
        Separating letter can be "E" or "W"
        Result is rounded to 2 decimals
        @param  $inCols Array containing 1 element ($inLine[$inCols[0]] contains the string to convert).
    **/
    private static function lg_ddmm2dec($inLine, $inCols, $outCol){
        $tmp = explode('E', $inLine[$inCols[0]]);
        $multiply = 1;
        if(count($tmp) != 2) {
            $tmp = explode('W', $inLine[$inCols[0]]);
            $multiply = -1;
        }
        if(count($tmp) != 2){
            throw new ObserveException("Invalid longitude : " . $inLine[$inCols[0]]);
        }
        $res = round($multiply * ($tmp[0] + $tmp[1] / 60), 2);
        return [$outCol => $res]; 
    }
    
    /** 
        Converts a string like "48N50" to a decimal longitude.
        Separating letter can be "N" or "S"
        Result is rounded to 2 decimals
        @param  $inCols Array containing 1 element ($inLine[$inCols[0]] contains the string to convert).
    **/
    private static function lat_ddmm2dec($inLine, $inCols, $outCol){
        $tmp = explode('N', $inLine[$inCols[0]]);
        $multiply = 1;
        if(count($tmp) == 0) {
            $tmp = explode('N', $inLine[$inCols[0]]);
            $multiply = -1;
        }
        if(count($tmp) != 2){
            throw new ObserveException("Invalid latitude : " . $inLine[$inCols[0]]);
        }
        $res = round($multiply * ($tmp[0] + $tmp[1] / 60), 2);
        return [$outCol => $res];
    }
    
    /** 
        Copies the content of a column to resulting column.
        @param  $inCols Array containing 1 element
    **/
    private static function copy($inLine, $inCols, $outCol){
        return [$outCol => $inLine[$inCols[0]]]; 
    }
    
} // end class
