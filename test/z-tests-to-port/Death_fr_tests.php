<?php
/******************************************************************************
    
    Auxiliary code for tests on death-fr study.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-27 06:32:30+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\tests;

use observe\model\Studies;
use observe\commands\death_fr\Death_fr;

class Death_fr_tests {

    /** 
        Initializations for tests on study with slug = "death-fr".
        @param  $path   Path to the study file to load, relative to current directory.
                        ex: "study1/study1.yml"
    **/
    public static function loadStudy(string $path): array {
        $yamlStudyFile = implode(DS, [__DIR__, 'study1', 'study1.yml']);
        $studyConfig = yaml_parse_file($yamlStudyFile);
        Studies::initializeStudy($studyConfig);
        Death_fr::setSqlitePersonPath($studyConfig['sqlite-death-fr']);
        return $studyConfig;
    }
    
    /** 
        Load a distribution from a csv file
        @param  $format "int" or "float"
    **/
    public static function readCsv($filename, $delimiter=';', $format='int'){
        $res = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, $delimiter, escape: '')) !== false){
                if(count($data) == 1 && $data[0] == ''){
                    continue; // skip empty lines
                }
                if($format == 'int'){
                    $res[$data[0]] = (int)$data[1];
                }
                else{
                    $res[$data[0]] = (float)$data[1];
                }
            }
            fclose($handle);
        }
        return $res;
    }

} // end class
