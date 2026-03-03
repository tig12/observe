<?php
/******************************************************************************
    Computes the distribution of aspects between planets at birth and planets at death
    
    @license    GPL
    @history    2026-02-17 00:44:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\shared\astro\sqlitePlanets;
use observe\shared\astro\aspects as aspectUtils;
use observe\shared\distrib\degrees as degreeUtils;
use observe\shared\distrib\addDistrib;
use observe\shared\distrib\csvDistrib;
use observe\shared\fileSystem;
use tiglib\patterns\command\Command;
use tiglib\filesystem\yieldFile;

class aspects implements Command {
    
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['split'])){
            echo "Missing parameter 'split' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            echo "Possible values:\n  - " . implode("\n  - ", DeathFr::$POSSIBLE_SPLITS) . "\n";
            return;
        }
        $split = $params['split'];
        if(($msg = DeathFr::checkParam_split($split)) !== true){
            echo $msg;
            return;
        }
        //
        if(!isset($params['out-subdir'])){
            echo "Missing parameter 'out-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $outDir = DeathFr::$WORKING_DIR . DS . $params['out-subdir'];
        if(!is_dir($outDir)){
            // Not created to avoid mistakes
            echo "Directory $outDir does not exist. Create it before executing this command\n";
            return;
        }
        //
        if(!isset($params['in-subdir'])){
            echo "Missing parameter 'in-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $dataDir = DeathFr::$WORKING_DIR . DS . $params['in-subdir'];
        if(!is_dir($dataDir)){
            // Not created to avoid mistakes
            echo "Directory $dataDir does not exist - Check the value in " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        //
        // Prepare
        //
        // planet codes
        $allPlanets = DeathFr::$PLANETS;
        // sqlite database containing the planet positions
        $sqlite_planets = sqlitePlanets::getSqlite();
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planets where day=:day
        $stmt_planets = $sqlite_planets->prepare('select ' . implode(',', $allPlanets) . ' from planets where day=:day');
        //
        // Execute
        //
        $t1 = microtime(true);
        $pName = '#.*' . DS . '(.*?).csv.bz2#';
        $files = glob($dataDir . DS . '*.bz2');
        foreach($files as $file){
            // distributions of each split are stored in a separate directory
            preg_match($pName, $file, $m);
            if(count($m) != 2){
                throw new \Exception("Unable to compute directory name for distributions of $file");
            }
            $outSubdir = $outDir . DS . $m[1];
            fileSystem::mkdir($outSubdir);
            //
            echo "Processing $file\n";
            $planets_birth = [];
            $planets_death = [];
            $distrib = degreeUtils::emptyDoubleDistrib($allPlanets, $allPlanets);
            $i = 0;
            foreach(yieldFile::loop('compress.bzip2://' . $file) as $line){
                $i++;
                [$bday, $dday] = explode(';', trim($line));
                $stmt_planets->execute([':day' => $bday]);
                $planets_birth[] = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
                $stmt_planets->execute([':day' => $dday]);
                $planets_death[] = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
                if($i % 10000 == 0){
                    // intermediate computations to flush memory
                    echo "  line $i\n";
                    $aspects = aspectUtils::computeDouble($planets_birth, $planets_death, $allPlanets, $allPlanets);
                    $newDistrib = degreeUtils::computeDistrib($aspects);
                    $distrib = addDistrib::compute($distrib, $newDistrib);
                    unset($aspects);
                    $planets_birth = [];
                    $planets_death = [];
                }
            }
            // computes angles between birth and death = planet death - planet birth
            $aspects = aspectUtils::computeDouble($planets_birth, $planets_death, $allPlanets, $allPlanets);
            $newDistrib = degreeUtils::computeDistrib($aspects);
            $distrib = addDistrib::compute($distrib, $newDistrib);
            unset($aspects);
            // store result
            foreach($distrib as $key => $values){
                $outFile = $outSubdir . DS . $key . '.csv';
                $contents = csvDistrib::distrib2csv($values);
                fileSystem::saveFile($outFile, $contents);
            }
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "Execution time: $dt s\n";
    }
/* 
all                     : Execution time: 8774.392 s  - 2 h 25
09--50years-150years    : Execution time: 10497.907 s - 3 h
*/
    
} // end class
                                                                                                                               