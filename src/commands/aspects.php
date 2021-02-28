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
use tiglib\math\mod360;

use observe\parts\fileSystem;

class aspects implements Command {
    
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
        // skip
        //
        $skip = false;
        if(isset($params['skip'])){
            $skip = $params['skip'];
        }
        // actions
        if(!isset($params['actions'])){
            throw new ObserveException("$classname needs a parameter 'actions'");
        }
        //
        //  execute
        //
        $t1 = microtime(true);
        $in = csvAssociative::compute($infile);
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "Read $infile ($dt s)\n";

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
        $t1 = microtime(true);
        foreach($in as $old){
            $new = [];
            foreach($pairs as $pair){
                if($old[$pair[0]] === $skip || $old[$pair[1]] === $skip){
                    // HERE decide that an empty string is written if skip encountered
                    // maybe add a parameter 'replace' to decide what to do
                    $new[] = '';
                }
                else{
                    $new[] = mod360::compute(round($old[$pair[1]] - $old[$pair[0]], 1));
                }
            }
            $res .= implode(Observe::CSV_SEP, $new) . "\n";
            $N++;
            if($N % 10000 == 0) echo "$N\n";
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "Compute aspects ($dt s)\n";
        
        fileSystem::saveFile($outfile, $res, message:"Wrote $N lines in $outfile\n");
    }
        
} // end class
