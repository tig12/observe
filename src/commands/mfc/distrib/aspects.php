<?php
/******************************************************************************
    Computes the aspect distributions of each member of a MFCW experience
    
    @license    GPL
    @history    2021-03-14 20:41:53+01:00, Thierry Graff : Big refactor
    @history    2021-02-14 11:05:05+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc\distrib;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\fileSystem;
use observe\parts\distrib\distrib;
use observe\parts\distrib\degrees;
use observe\parts\astro\aspects as aspects2;

class aspects implements Command {
    
    public static function execute($params=[]) {
        // TODO check params
        $dirDistrib = $params['out-dir'] . DS . 'distrib';
        $inDir = $params['in-dir'] . DS . 'data' . DS . 'planets';
        $data = [];
        $members = ['M', 'F', 'C'];
        if($params['experience']['has-wedding']){
            $members[] = 'W';
        }
        foreach($members as $member){
            // 1 - load data
            $inFile = $params['in-dir'] . DS . 'data' . DS . 'planets' . DS . $member . '.csv';
            if(!file_exists($inFile)){
                throw new ObserveException("File $inFile does not exist");
            }
            $in = csvAssociative::compute($inFile);
            $inCols = array_keys($in[0]); // HERE we compute the aspects between all planets
            // 2 - compute distributions
            // ex: $aspects : [0 => ['SO-MO' => 253.3, 'SO-ME' => 24.4 ...], ...]
            $aspects = aspects2::computeSingle(
                data: $in,
                cols: $inCols,
                skip: $params['aspects']['skip'],
                precision: $params['aspects']['precision']
            );
            $distribs = degrees::computeDistrib($aspects);
            // 3 - store distributions
            $outDir = $dirDistrib . DS . $member . DS . 'aspects'; // ex distrib/F/aspects/
            fileSystem::mkdir($outDir);
            foreach($distribs as $aspectCode => $distrib){
                $outFile = $outDir . DS . $aspectCode . '.csv';
                fileSystem::saveFile($outFile, distrib::distrib2csv($distrib));
            }
        }
    }
    
} // end class
