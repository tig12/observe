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
require_once implode(DS, [__DIR__, 'src', 'app', 'init.php']);

use observe\app\Run;
use observe\app\ObserveException;
use observe\app\CommandFile;

//echo "\n<pre>"; print_r($argv); echo "</pre>\n"; exit;

//
// parameter checking
//
$commandFiles = Run::getCommandFiles();
$commandFiles_str = implode(", ", $commandFiles);

$USAGE = <<<USAGE
-------                                                                                               
Usage: 
    php {$argv[0]} <command> <step>
Example:
    php {$argv[0]} test/toto
-------

USAGE;

if($argc == 1){
    echo "WRONG USAGE - {$argv[0]} needs 2 arguments\n";
    echo "Possible values for argument1:\n     $commandFiles_str\n";
    echo $USAGE;
    exit;
}

//
// --- $argv[1] : command file ---
//
if(!in_array($argv[1], $commandFiles)){
    echo "WRONG USAGE - INVALID COMMAND FILE: {$argv[1]}\n";
    echo "Possible values for argument1:\n     $commandFiles_str\n";
    echo $USAGE;
    exit;
}

// here, $argv[1] is valid
$cmdFile = new CommandFile($argv[1]);

if($argc == 2){
    echo "WRONG USAGE - {$argv[0]} needs 2 arguments\n";
    echo "Possible values for argument2: " . implode(', ', $cmdFile->getAllCommands())  . "\n";
    echo $USAGE;
    exit;
}

//
// --- $argv[2] : command ---
//
if(!$cmdFile->commandExists($argv[2])){
    echo "WRONG USAGE - INVALID COMMAND: {$argv[2]}\n";
    echo "Possible values for argument2: " . implode(', ', $cmdFile->getAllCommands())  . "\n";
    echo $USAGE;
    exit;
}
// here, $argv[2] is valid

//
// --- run ---
//
try{
    $cmdFile-> executeCommand($argv[2]);
}
catch(observe\app\ObserveException $e){
    echo "ERROR: " . $e->getMessage() . "\n";
}
catch(Exception $e){
    echo $e->getTraceAsString() . "\n";
    echo 'Exception: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
}
