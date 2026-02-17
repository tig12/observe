<?php
/******************************************************************************
    Computes the planet distributions of each member of a MFCW experience
    
    @license    GPL
    @history    2021-03-14 22:42:57+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc\distrib;

use observe\app\Observe;
use observe\app\ObserveException;
use observe\app\Command;
use tiglib\arrays\csvAssociative;

use observe\commands\mfc\MFC;
use observe\shared\fileSystem;
use observe\shared\distrib\csvDistrib;
use observe\shared\distrib\degrees;
use observe\shared\astro\aspects as aspects2;

class interaspects implements Command {
    
    public static function execute($params=[]) {
        // TODO check params
        $data = [];
        $indir = $params['in-dir'] . DS . 'planets';
        $outdir = $params['out-dir'] . DS . 'distrib';
        $members = ['M', 'F', 'C'];
        if($params['experience']['has-wedding']){
            $members[] = 'W';
        }
        $couples = MFC::computeCouples($params['experience']['has-wedding']);
        foreach($couples as $couple){
            $member1 = $couple[0];
            $member2 = $couple[1];
            // 1 - load data
            $infile1 = $indir . DS . $member1 . '.csv';
            $infile2 = $indir . DS . $member2 . '.csv';
            if(!file_exists($infile1)){
                throw new ObserveException("File $infile1 does not exist");
            }
            if(!file_exists($infile2)){
                throw new ObserveException("File $infile2 does not exist");
            }
            $in1 = csvAssociative::compute($infile1);
            $in2 = csvAssociative::compute($infile2);
            $inCols1 = array_keys($in1[0]);
            $inCols2 = array_keys($in2[0]);
            // 2 - compute distributions
            // ex: $aspects : [0 => ['SO-SO' => 253.3, 'SO-MO' => 24.4 ...], ...]
            $aspects = aspects2::computeDouble(
                data1:      $in1,
                data2:      $in2,
                cols1:      $inCols1,
                cols2:      $inCols2,
                skip:       $params['interaspects']['skip'],
                precision:  $params['interaspects']['precision']
            );
            $distribs = degrees::computeDistrib($aspects);
            // 3 - store distributions
            $outsubdir = $outdir . DS . "$member1-$member2"; // ex distrib/M-F/
            fileSystem::mkdir($outsubdir);
            foreach($distribs as $aspectCode => $distrib){
                $outfile = $outsubdir . DS . $aspectCode . '.csv';
                fileSystem::saveFile($outfile, csvDistrib::distrib2csv($distrib, Observe::CSV_SEP));
            }
        }
    }
    
} // end class
