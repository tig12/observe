<?php
/******************************************************************************
    
    Auxiliary code for tests on death-fr study.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-27 06:32:30+01:00, Thierry Graff : Creation
********************************************************************************/

use observe\model\Studies;
use observe\commands\death_fr\Death_fr;
/** 
    Initializations for tests on death-fr study.
    @param  $path   Path to the study file to load, relative to current directory.
                    ex: "study1/study1.yml"
**/
function load_death_fr_study(string $path): array {
    $yamlStudyFile = implode(DS, [__DIR__, 'study1', 'study1.yml']);
    $studyConfig = yaml_parse_file($yamlStudyFile);
    Studies::initializeStudy($studyConfig);
    Death_fr::setSqlitePersonPath($studyConfig['sqlite-death-fr']);
    return $studyConfig;
}

/** 
    Load a distribution from a csv file
**/
function readCsv($filename, $delimiter=';'){
    $res = [];
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 0, $delimiter, escape: '')) !== false){
            if(count($data) == 1 && $data[0] == ''){
                continue; // skip empty lines
            }
            $res[$data[0]] = (int)$data[1];
        }
        fclose($handle);
    }
    return $res;
}
