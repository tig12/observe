<?php
/******************************************************************************
    Computes the aspects of a MFCW experience.
    - "normal aspects" (aspects of a single person)
    - "inter aspects" (aspects between 2 persons)
    
    @todo   Maybe specify which aspects are computed.
            Current version computes aspects for all possible pairs of planets
    
    @license    GPL
    @history    2021-03-09 09:46:38+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\fileSystem;
use observe\parts\astro\aspects as aspectsParts;

class aspects implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        // in-dir
        if(!isset($params['in-dir'])){
            throw new ObserveException("$classname needs a parameter 'in-dir'");
        }
        // out-dir
        if(!isset($params['out-dir'])){
            throw new ObserveException("$classname needs a parameter 'out-dir'");
        }
        $outdir = $params['out-dir'] . DS . 'data' . DS . 'aspects';
        fileSystem::mkdir($outdir);
        // skip (optional)
        $skip = false;
        if(isset($params['skip'])){
            $skip = $params['skip'];
        }
        // processWedding (optional)
        $processWedding = false;
        if(isset($params['process-wedding'])){
            $processWedding = $params['process-wedding'];
        }
        // precision (optional)
        $precision = false;
        if(isset($params['precision'])){
            $precision = $params['precision'];
        }
        //
        //  execute
        //
        $keys = ['M', 'F', 'C'];
        if($processWedding){
            $keys[] = 'W';
        }
        //
        // 1 - individual aspects
        //
        foreach($keys as $key){
            $infile = $params['in-dir'] . DS . 'data' . DS . 'planets' . DS . $key . '.csv';
            $outfile = $params['in-dir'] . DS . 'data' . DS . 'aspects' . DS . $key . '.csv';
            if(!is_file($infile)){
                throw new ObserveException("File not found : $infile");
            }
            $in = csvAssociative::compute($infile);
            $incols = array_keys($in[0]); //HERE we compute the aspects between all planets
            $aspects = aspectsParts::computeSingle($in, $incols, skip:$skip, precision:$precision);
            $outcols = array_keys($aspects[0]);
            $out = implode(Observe::CSV_SEP, $outcols) . "\n";
            foreach($aspects as $line){
                $out .= implode(Observe::CSV_SEP, $line) . "\n";
            }
            fileSystem::saveFile($outfile, $out);
        }
    }
        
} // end class
