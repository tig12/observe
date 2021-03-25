<?php
/******************************************************************************
    Conducts the generation of reports for a MFCW (mother, father, child, mariage) group.
    
    @license    GPL
    @history    2020-12-16 18:17:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc\pages;

use observe\app\Command;
use observe\app\ObserveException;
use observe\commands\mfc\MFC;
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
// TODO put all this code in commands/page::cleanParams()
// this preg_split for planet codes should be in a function (see commands.computeAstro) 
        // planets, convert string like "SO MO ME VE MA JU SA UR NE PL NN" to array
        $tmp = preg_split('/\s+/', $params['planets']);
        $params['planets'] = $tmp;
        //
        if(!isset($params['svg-separate'])){
            $params['svg-separate'] = false;
        }
        if(!isset($params['svg-path'])){
            $params['svg-path'] = 'svg';
        }
        //
        self::$params = $params;
        //
        //  execute
        //
        fileSystem::mkdir($outdir . DS . $params['svg-path']);
        fileSystem::saveFile($outdir . DS . 'index.html',   index::computePage($params));
        fileSystem::saveFile($outdir . DS . 'mother.html',  MF::computePage($params, MF:'M'));
        fileSystem::saveFile($outdir . DS . 'father.html',  MF::computePage($params, MF:'F'));
        fileSystem::saveFile($outdir . DS . 'child.html',   C::computePage($params));
        if($params['experience']['has-wedding'] === true){
            fileSystem::saveFile($outdir . DS . 'wedding.html', W::computePage($params));
        }
        // inter-aspects
        $couples = MFC::computeCouples($params['experience']['has-wedding']);
        foreach($couples as $couple){
            $member1 = $couple[0];
            $member2 = $couple[1];
// TODO put nex line in MFC::coupleLabel()
            $outFile = $outdir . DS . strToLower(MFC::LABELS[$member1]) . '-' . strToLower(MFC::LABELS[$member2]) . '.html';
            fileSystem::saveFile($outFile, interaspects::computePage($params, $member1, $member2));
//exit;
        }
    }
    
}// end class
