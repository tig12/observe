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

class aspects implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        if(!isset($params['input-file'])){
            throw new ObserveException("$classname needs a parameter 'input-file'");
        }
        //
        $infile = $params['input-file'];
        if(!is_file($infile)){
            throw new ObserveException("File not found : $infile");
        }
        //
        if(!isset($params['actions'])){
            throw new ObserveException("$classname needs a parameter 'actions'");
        }
        //
        if(!isset($params['output-file'])){
            throw new ObserveException("$classname needs a parameter 'output-file'");
        }
        $outfile = $params['output-file'];
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            throw new ObserveException("Create directory '$dir' and try again");
        }
        //
        //  execute
        //
        $in = csvAssociative::compute($infile);
        $incols = array_keys($in[0]);
        //
        // Build pairs of input columns, using actions
        //
        $pSpaces = '/\s+/';
        $pairs = [];
        foreach($params['actions'] as $action){
            $parts = preg_split($pSpaces, $action);
            if(count($parts) != 2){
                throw new ObserveException("Invalid line : $action");
            }
            [$p1, $p2] = $parts;
            $elts1 = $elts2 = [];
            foreach($incols as $col){
                if(fnmatch($p1, $col)){
                    $elts1[] = $col;
                }
                if(fnmatch($p2, $col)){
                    $elts2[] = $col;
                }
            }
            foreach($elts1 as $e1){
                foreach($elts2 as $e2){
                    $pairs[] = [$e1, $e2];
                }
            }
        }
        // Build output columns
        $SEP = '--';
        $outcols = [];
        foreach($pairs as $pair){
            $outcols[] = $pair[0] . $SEP . $pair[1];
        }
        // build res
        $res = implode(Observe::CSV_SEP, $outcols) . "\n";
        $N = 0;
        foreach($in as $old){
            $new = [];
            foreach($pairs as $pair){
                $new[] = self::mod360(round($old[$pair[1]] - $old[$pair[0]], 1));
            }
            $res .= implode(Observe::CSV_SEP, $new) . "\n";
            $N++;
            if($N % 100000 == 0) echo "$N\n";
        }
        file_put_contents($outfile, $res);
        echo "Wrote $N lines in $outfile\n";
    }
    
    //***************************************************
    /** Returns a number modulo 360 (between 0 and 360). **/
    private static function mod360($nb){
        if($nb >= 0){
            $dec = $nb - floor($nb);
            return $nb%360 + $dec;
        }
        else{
            $dec = $nb - floor($nb);
            if($dec != 0) $dec -= 1;
            return $nb%360 + $dec + 360;
        }
    }
    
}// end class
