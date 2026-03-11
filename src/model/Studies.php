<?php
/******************************************************************************
    

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 14:59:09+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

use tiglib\filesystem\globRecursive;

class Studies {
    
    const AVAILABLE_COMMANDS = [
        'split',
        'distrib',
        'control',
        'chi2',
        'output',
    ];
    
    /** 
        Associative array
            Keys: slugs of the studies
            Values: Contents of the corresponding yaml files located in studies/
    **/
    private static $studyFiles = [];
    
    /**
        Returns the slugs of all available studies.
        The slugs come from yaml files stored in study file, in studies/
    **/
    public static function getAllStudySlugs(): array {
        $files = globRecursive::execute('studies/*.yml');
        foreach($files as $file){
            $studyFileContents = yaml_parse_file($file);
            // At this step, doesn't check if the yaml file is valid
            if(isset($studyFileContents['slug'])){
                self::$studyFiles[$studyFileContents['slug']] = $studyFileContents;
                $res[] = $studyFileContents['slug'];
            }
        }
        return array_keys(self::$studyFiles);
    }
    
    /**
        Conductor of command execution.
        Parameter $studySlug is considered as valid, already checked by observe\app\Run::parseOutput().
        @return Error message if problem, empty message if ok.
    **/
    public static function runCommand(string $studySlug, string $command, $params=[]): string {
        
        if(!in_array($command, self::AVAILABLE_COMMANDS)){
            return "INVALID COMMAND: \"$command\"\nAvailable commands:\n    - "
                . implode("\n    - ", self::AVAILABLE_COMMANDS) . "\n";
        }
        
        $studyNamespace = 'observe\\studies\\' . str_replace('-', '_', $studySlug);
        $sharedNamespace = 'observe\\studies\\shared';
        
        // Here we cheat because we know that this function is called after self::getAllStudySlugs()
        // then self::self::$studyFiles is already computed
        $studyFile = self::$studyFiles[$studySlug];
        
        switch($command){
            // commands with implementation specific to each study
        	case 'split': 
        	case 'control': 
        	    $class = $studyNamespace . '\\' . $command;
        	    return $class::execute($studyFile, $params);
        	break;
        	// commands with implementation shared by all studies
            default:
        	    $class = $sharedNamespace . '\\' . $command;
        	    return $class::execute($studyFile, $params);
        	break;
        }
        // No return as normally here is never reached
    }
    
    /** 
        Returns the contents of a study file.
        @return Error message if problem, empty message if ok.
    **/
    private static function computeStudyFile(string $studySlug): array {
        // Here we cheat because we know that this function is called after self::getAllStudySlugs()
        // which cached the study files it read
        return self::$studyFiles[$studySlug];
    }
    
    /**
        @param  $studyConfig    Contains the contents of a yaml study file
        @return Error message if problem, empty message if ok.
    **/
    private static function checkStudyFile(array $studyFileContents): string {
        if(!isset($studyFileContents['slug'])){
            return "Missing entry \"slug\"";
        }
        if(!isset($studyFileContents['working-dir'])){
            return "Missing entry \"working-dir\"";
        }
        if(!isset($studyFileContents['out-dir'])){
            return "Missing entry \"out-dir\"";
        }
        if(!isset($studyFileContents['planets'])){
            return "Missing entry \"planets\"";
        }
        if(!isset($studyFileContents['splits'])){
            return "Missing entry \"splits\"";
        }
        if(!isset($studyFileContents['n-controls'])){
            return "Missing entry \"n-controls\"";
        }
        return '';
    }

} // end class
