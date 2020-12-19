<?php
/******************************************************************************

    @license    GPL
    @history    2020-12-19 07:39:34+01:00, Thierry Graff : Creation
********************************************************************************/
namespace distrib\commands;

use distrib\Distrib;
use distrib\patterns\Command;
use distrib\DistribException;
use tiglib\arrays\csvAssociative;

class groupByNumber implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = 'groupByNumber'; // TODO copute by reflection
        if(!isset($params['input-file'])){
            throw new DistribException("$classname needs a parameter 'input-file'");
        }
        //
        $infile = $params['input-file'];
        if(!is_file($infile)){
            throw new DistribException("File not found : $infile");
        }
        //
        if(!isset($params['range'])){
            throw new DistribException("$classname needs a parameter 'range'");
        }
        $range = $params['range'];
        if(!is_int($range)){
            throw new DistribException("$classname : Parameter 'range' must be an integer");
        }
        //
        if(!isset($params['output-dir'])){
            throw new DistribException("$classname needs a parameter 'output-dir'");
        }
        $outdir = $params['output-dir'];
        if(!is_dir($outdir)){
            mkdir($outdir, 0755, true);
        }
        //
        //  execute
        //
        $in = csvAssociative::compute($infile);
        $incols = array_keys($in[0]);
        $outlines = array_fill(0, $range, 0);
        $res = array_fill_keys($incols, $outlines); // contains the nb of occurences
        $N = 0;
        foreach($in as $cur){
            foreach($cur as $k => $v){
                $int = floor($v);
                if($int == $range){
                    $int = 0;
                }
                $res[$k][$int]++;
            }
            $N++;
            if($N % 100000 == 0) echo "$N\n";
        }
        //
        // write output files
        //
        foreach($res as $k => $v){
            $outfile = $outdir . DS . "$k.csv";
            $res = '';
            foreach($v as $col1 => $col2){
                $res .= $col1 . Distrib::CSV_SEP . $col2 . "\n";
            }
            file_put_contents($outfile, $res);
        }
        echo "Wrote repartitions in $outdir\n";
    }
    
}// end class
