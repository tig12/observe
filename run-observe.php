<?php
/********************************************************************************
    CLI (command line interface) of Observe program.
    
    usage : php run-observe.php
    and follow error message
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2020-12-15 21:38:32+01:00, Thierry Graff : creation
********************************************************************************/

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'src', 'app', 'init.php']);

use observe\app\Run;
use observe\model\Studies;
use observe\app\ObserveException;
use observe\commands\prepareAstro;

$input = Run::parseInput($argv);
print_r($input); exit;

if($input['message'] != ''){
    die($input['message']);
}

//
// Run
//
try{
    if($input['study-slug'] == 'prepare' && $input['command'] == 'planets'){
        $msg = prepareAstro::execute($input['params']);
    }
    else {
        $msg = Studies::runCommand($input['study-slug'], $input['command'], $input['params']);
    }
    echo $msg; // $msg is empty if execution is ok
}
catch(ObserveException $e){
    echo "ERROR: " . $e->getMessage() . "\n";
}
catch(Exception $e){
    echo $e->getTraceAsString() . "\n";
    echo 'EXCEPTION: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
}
