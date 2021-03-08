<?php
/******************************************************************************
    Conducts the generation of reports for a MFCW (mother, father, child, mariage) group.
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc\pages;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\fileSystem;

class all implements Command {
    
    /** Parameters passed to execute() **/
    private static $params;
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        //
        $indir = $params['in-dir'] ?? false;
        if(!$indir){
            throw new ObserveException("$classname needs a parameter 'in-dir'");
        }
        if(!is_dir($indir)){
            throw new ObserveException("$indir does not exist");
        }
        //
        $outdir = $params['out-dir'] ?? false;
        if(!$outdir){
            throw new ObserveException("$classname needs a parameter 'out-dir'");
        }
        fileSystem::mkdir($outdir);
        //
        self::$params = $params;
        //
        //  execute
        //
        fileSystem::saveFile("$outdir/index.html", index::computePage($params));
        fileSystem::saveFile("$outdir/mother.html", MF::computePage(params:$params, MF:'M'));
//exit;
        fileSystem::saveFile("$outdir/father.html", MF::computePage(params:$params, MF:'F'));
        fileSystem::saveFile("$outdir/child.html", C::computePage($params));
        if($params['wedding'] === true){
            fileSystem::saveFile("$outdir/wedding.html", W::computePage($params));
        }
        
    }
    
}// end class
