<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-17 00:44:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;
use observe\shared\astro\sqlitePlanets;
use observe\shared\astro\aspects as aspectUtils;
use observe\shared\distrib\degrees as degreeUtils;
use tiglib\math\mod360;

class aspects implements Command {
    
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['split'])){
            echo "Missing parameter 'split' in command file commands/death-fr/death-fr.yml\n";
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
            echo "Directory $dataDir does not exist - Check the value in " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        // sqlite database containing the planet positions
        $sqlite_planets = sqlitePlanets::getSqlite();
        // planet codes
        $allPlanets = DeathFr::$PLANETS;
        //
        // Prepare
        //
$nPlanets = count($allPlanets);
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planets where day=:day
        $stmt_planets = $sqlite_planets->prepare('select ' . implode(',', $allPlanets) . ' from planets where day=:day');
        
        $filename = $dataDir . DS . '04--1year-2years.csv.bz2';
        $handle = fopen('compress.bzip2://' . $filename, 'r');
        if ($handle === false) {
            die('Unable to open file');
        }
        //
        // Execute
        //
        $planets_birth = [];
        $planets_death = [];
        while(($line = fgets($handle)) !== false) {
            [$bday, $dday] = explode(';', trim($line));
            /* 
            $stmt_planets->execute([':day' => $bday]);
            $planets_birth[] = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
            $stmt_planets->execute([':day' => $dday]);
            $planets_death[] = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
            */
            $stmt_planets->execute([':day' => $bday]);
            $tmp_birth = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
            $stmt_planets->execute([':day' => $dday]);
            $tmp_death = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
//if(count($tmp_birth) != $nPlanets || count($tmp_death) != $nPlanets){
if(!is_array($tmp_birth) || !is_array($tmp_death)){
    echo "'''$bday, $dday'''\n";
    print_r($tmp_birth); echo "\n";
    print_r($tmp_death); echo "\n";
    exit;
}
        }
        if(!feof($handle)){
            echo "Error: unexpected fgets() failure\n";
        }
        fclose($handle);
exit;
        // Here, we compute the angles between birth and death = death - birth => param 1 = death and param 2 = birth
        $angles = aspectUtils::computeDouble($planets_death, $planets_birth, $allPlanets, $allPlanets);
        //$distrib = degreeUtils::computeDistrib($angles);
//print_r($angles);
exit;






//        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person");
        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person limit 10");
        $stmt_planets = $sqlite_planets->prepare("select * from planets where day=:day");
        // $distrib = assoc array
        // keys: ex [SU-ME] ; planet code at birth - planet code at death
        // values : regular array of 360 elements containing the distribution
        $distrib = [];
        $nPlanets = count($allPlanets);
        for($i=0; $i < $nPlanets; $i++){
            for($j=0; $j < $nPlanets; $j++){
                $key = $allPlanets[$i] . '-' . $allPlanets[$j];
                $distrib[$key] = array_fill(0, 360, 0); // regular array, from idx = 0 to 359
            }
        }
        //
        // Compute
        //
        $stmt_persons->execute();
        foreach($stmt_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
            $stmt_planets->execute([':day' => $person['bday']]);
            $planets_birth = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
            $stmt_planets->execute([':day' => $person['dday']]);
            $planets_death = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
// TODO See if observe\shared\astro\aspects::computeDouble() could be used instead (avoid code repetition)
            foreach($allPlanets as $planet_birth){
                foreach($allPlanets as $planet_death){
                    $key = $planet_birth . '-' . $planet_death;
//echo "$key\n";
                    $angle = floor(mod360::compute($planets_birth[$planet_birth] - $planets_death[$planet_death]));
                    $distrib[$key][$angle]++;
// echo $planets_birth[$planet_birth] . "\n";
// echo $planets_death[$planet_death] . "\n";
// echo "$angle\n";
break;
                }
break;
            }
//print_r($distrib['SO-SO']); exit;
        }
        //
        // Store result
        //
        $keys = [];
        foreach($planets as $p1){
            foreach($planets as $p2){
                $keys[] = "$p1-$p2";
            }
        }
        $res = implode(';', $keys) . "\n";
        for($i=0; $i < 360; $i++){
            $line = [];
            foreach($planets as $p1){
                foreach($planets as $p2){
                    $line[] = $distrib["$p1-$p2"][$i];
                }
            }
            $res .= implode(';', $line) . "\n";
        }
echo "$res\n"; exit;
    }
    
} // end class
                                                                                                                               