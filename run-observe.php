<?php
/********************************************************************************
    CLI (command line interface) of Observe program.
    
    usage : php run-observe.php
    and follow error message
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2020-12-15 21:38:32+01:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once implode(DS, [__DIR__, 'src', 'app', 'init.php']);

use observe\app\Run;
use observe\model\Studies;

$input = Run::parseInput($argv);

if($input['message'] != ''){
    echo $input['message'];
    exit;
}

$msg = Studies::runCommand($input['study'], $input['command'], $input['params']);
echo $msg; // empty if execution ok
