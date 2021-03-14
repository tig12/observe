<?php
/******************************************************************************
    Computes the planet distributions of each member of a MFCW experience
    
    @license    GPL
    @history    2021-03-14 22:42:57+01:00, Thierry Graff : Creation
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

class interaspects implements Command {
    
    public static function execute($params=[]) {
        // TODO check params
        $dirDistrib = $params['out-dir'] . DS . 'distrib';
        $inDir = $params['in-dir'] . DS . 'data' . DS . 'planets';
        $data = [];
        $members = ['M', 'F', 'C'];
        if($params['experience']['has-wedding']){
            $members[] = 'W';
        }
        $couples = [];
        for($i=0; $i < count($members); $i++){
            for($j=$i+1; $j < count($members); $j++){
                $couples[] = [$members[$i], $members[$j]];
            }
        }
        foreach($couples as $couple){
            $member1 = $couple[0];
            $member2 = $couple[1];
            // 1 - load data
            $inFile1 = $params['in-dir'] . DS . 'data' . DS . 'planets' . DS . $member1 . '.csv';
            $inFile2 = $params['in-dir'] . DS . 'data' . DS . 'planets' . DS . $member2 . '.csv';
            if(!file_exists($inFile1)){
                throw new ObserveException("File $inFile1 does not exist");
            }
            if(!file_exists($inFile2)){
                throw new ObserveException("File $inFile2 does not exist");
            }
            $in1 = csvAssociative::compute($inFile1);
            $in2 = csvAssociative::compute($inFile2);
            $inCols1 = array_keys($in1[0]);
            $inCols2 = array_keys($in2[0]);
            // 2 - compute distributions
            // ex: $aspects : [0 => ['SO-SO' => 253.3, 'SO-MO' => 24.4 ...], ...]
throw new Exception("IMPLEMENT HERE");
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