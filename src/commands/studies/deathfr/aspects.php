<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-17 00:44:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;
use observe\app\Config;
use tigeph\model\IAA;
use tiglib\math\mod360;

class aspects implements Command {
    
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['work-dir'])){
            echo "Missing parameter 'work-dir' in command file commands/death-fr/death-fr.yml\n";
            return;
        }
        if(!isset($params['out-subdir'])){
            echo "Missing parameter 'out-subdir' in command file commands/death-fr/death-fr.yml\n";
            return;
        }
        $outDir = $params['work-dir'] . DS . $params['out-subdir'];
        if(!is_dir($outDir)){
            echo "Directory $outDir does not exist. Create it before executing this command\n";
            return;
        }
        // sqlite database containing the planet positions
        if(!is_file(Config::$data['sqlite-planets'])){
            echo 'Sqlite database ' . Config::$data['sqlite-planets'] . "does not exist\n"
                . "You first need to create it using php run-observe.php prepare planets <date range>\n";
                return;
        }
        $sqlite_planets = new \PDO('sqlite:' . Config::$data['sqlite-planets']);
        // sqlite database containing data coming from data.gouv.fr
        if(!isset($params['sqlite-death-fr'])){
            echo "Missing parameter 'sqlite-death-fr' in command file commands/death-fr/death-fr.yml\n";
            return;
        }
        if(!is_file($params['sqlite-death-fr'])){
            echo 'Sqlite database ' . $params['sqlite-death-fr'] . "does not exist\n"
                . "You first need to create it (using g5 program)\n";
                return;
        }
        $sqlite_persons = new \PDO('sqlite:' . $params['sqlite-death-fr']);
        // planet codes
        if(!isset($params['planets'])){
            echo "MISSING 'planets' PARAMETER IN commands/prepare.yml\n";
            return;
        }
        $msg = IAA::checkCodes($params['planets']);
        if($msg != ''){
            echo $msg . "\n";
            return;
        }
        $planets = $params['planets'];
        //
        // Prepare
        //
//        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person");
        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person limit 10");
        $stmt_planets = $sqlite_planets->prepare("select * from planets where day=:day");
        // $distrib = assoc array
        // keys: ex [SU-ME] ; planet code at birth - planet code at death
        // values : regular array of 360 elements containing the distribution
        $distrib = [];
        $nPlanets = count($planets);
        for($i=0; $i < $nPlanets; $i++){
            for($j=0; $j < $nPlanets; $j++){
                $key = $planets[$i] . '-' . $planets[$j];
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
            foreach($planets as $planet_birth){
                foreach($planets as $planet_death){
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
                                                                                                                               