<?php
/********************************************************************************
    CLI (command line interface) of Observe program
    
    usage : php run-observe.php
    and follow error message
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2020-12-15 21:38:32+01:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

//require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';
require_once implode(DS, [__DIR__, 'src', 'init', 'init.php']);

use observe\Run;
use observe\ObserveException;
use observe\CommandFile;

//
// parameter checking
//
$cmmands = Run::getCommands();
$cmmands_str = implode(", ", $cmmands);

$USAGE = <<<USAGE
-------                                                                                               
Usage : 
    php {$argv[0]} <command> <step>
Example :
    php {$argv[0]} test/toto
-------

USAGE;

// check arg1
if($argc < 3){
    echo "WRONG USAGE - run-observes.php needs at least 2 arguments\n";
    echo $USAGE;
    echo "Possible values for argument1 : $cmmands_str\n";
    exit;
}
else{
    if(!in_array($argv[1], $cmmands)){
        echo "WRONG USAGE - INVALID COMMAND : {$argv[1]}\n";
        echo $USAGE;
        echo "Possible values for argument1 : $cmmands_str\n";
        exit;
    }
}

// here, $argv[1] is valid
$cmd = new CommandFile($argv[1]);

// check arg2
if(!$cmd->stepExists($argv[2])){
    
    echo "WRONG USAGE - INVALID STEP : {$argv[2]}\n";
    echo $USAGE;
    echo "Possible values for argument2 : " . implode(', ', $cmd->getAllSteps())  . "\n";
    exit;
}

// here, $argv[2] is valid
//
// run
//
try{
    $cmd-> executeStep($argv[2]);
}
catch(ObserveException $e){
    echo "ERROR: " . $e->getMessage() . "\n";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
