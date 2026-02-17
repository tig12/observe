<?php
/******************************************************************************
    Computes the planet distributions of each member of a MFCW experience

    @license    GPL
    @history    2021-02-14 11:05:05+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc\distrib;

use observe\app\Observe;
use observe\app\ObserveException;
use observe\app\Command;
use tiglib\arrays\csvAssociative;

use observe\shared\fileSystem;
use observe\shared\distrib\csvDistrib;
use observe\shared\distrib\degrees;

class planets implements Command {
    
    // ******************************************************
    
    public static function execute($params=[]) {
        // TODO check parameters
        $dirDistrib = $params['out-dir'] . DS . 'distrib';
        //
        echo "Computing planet distributions...\n";
        $inDir = $params['in-dir'] . DS . 'planets';
        $members = ['M', 'F', 'C'];
        if($params['experience']['has-wedding']){
            $members[] = 'W';
        }
        foreach($members as $member){
            $inFile = $inDir . DS . $member . '.csv';
            if(!file_exists($inFile)){
                throw new ObserveException("File $inFile does not exist");
            }
            // 1 - load data
            $data = csvAssociative::compute($inFile);
            // 2 - compute distributions
            $distribs = degrees::computeDistrib($data);
            // 3 - store distributions
            $outdir = $dirDistrib . DS . $member . DS . 'planets'; // ex distrib/F/planets/
            fileSystem::mkdir($outdir);
            foreach($distribs as $planet => $distrib){
                $csv = csvDistrib::distrib2csv($distrib, Observe::CSV_SEP);
                $outfile = $outdir . DS . $planet . '.csv';
                fileSystem::saveFile($outfile, $csv);
            }
        }
    }
    
} // end class
