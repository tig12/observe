<?php
/******************************************************************************
    

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 14:59:09+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

use tiglib\filesystem\globRecursive;
use tigeph\model\IAA;

class Studies {
    
    /** All commands that can be run on a study **/
    const AVAILABLE_COMMANDS = [
        'init',
        'split',
        'observed',
        'control',
        'expected',
        'chi2',
        'output',
    ];
    
    /** 
        Associative array
            Keys: slugs of the studies
            Values: Contents of the corresponding yaml files located in studies/
    **/
    private static array $studyConfigs = [];
    
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
        
        $studyDir = str_replace('-', '_', $studySlug); // === WARNING === Here, use of an implicit convention
        $studyNamespace = 'observe\\commands\\' . $studyDir;
        $sharedNamespace = 'observe\\commands\\shared';
        
        // Here we cheat because we know that current function is called after self::getAllStudySlugs()
        // then self::self::$studyConfigs is already computed
        $studyConfig = self::$studyConfigs[$studySlug];
        if(($msg = self::checkStudyFile($studyConfig)) != ''){
            return "ERROR in study file {$studyConfig['study-file']}:\n$msg\n";
        }
        
        // Before calling the command, handle the computations specific to each study:
        // call method init() of a class implementing IStudy located in the package specific to the command
        if(($msg = self::initializeStudy($studyDir, $studyNamespace, $studyConfig)) != ''){
            return "$msg\n";
        }
        switch($command){
            // commands with implementation specific to each study
        	case 'init': 
        	case 'split': 
        	case 'control': 
        	    $class = $studyNamespace . '\\' . $command;
        	    return $class::execute($studyConfig, $params);
        	break;
        	// commands with implementation shared by all studies
            default:
        	    $class = $sharedNamespace . '\\' . $command;
        	    return $class::execute($studyConfig, $params);
        	break;
        }
        // No return as normally here is never reached
    }
    
    /** Returns the directory containing the intermediate files of a given split of a study. **/
    public static function getSplitDirectory(array &$studyConfig, string $split): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split;
    }
    
    /** Returns the directory containing all the controls of a study. **/
    public static function getControlsDirectory(array &$studyConfig): string {
        return $studyConfig['working-dir'] . DS . 'controls';
    }
    
    /** Function written for phpunit. **/
    public static function getStudyConfig(string $studySlug) {
        if(count(self::$studyConfigs) == 0){
            // call getAllStudySlugs() to have self::$studyConfigs computed
            $allSlugs = self::getAllStudySlugs();
        }
        return self::$studyConfigs[$studySlug];
    }
    
    
    /**
        Returns the slugs of all available studies.
        The slugs come from yaml files stored in study file, in studies/
    **/
    public static function getAllStudySlugs(): array {
        $files = globRecursive::execute('studies/*.yml');
        foreach($files as $file){
            $studyConfig = yaml_parse_file($file);
            // At this step, doesn't check if the yaml file is valid
            if(isset($studyConfig['slug'])){
                $slug = $studyConfig['slug'];
                //
                // HERE store the contents of the yaml in self::$studyFile
                // (with a supplementary entry: 'study-file')
                //
                self::$studyConfigs[$slug] = [...$studyConfig, ...['study-file' => $file]];
                $res[] = $slug;
            }
        }
        return array_keys(self::$studyConfigs);
    }
    
    /**
        @param  $studyConfig    Contains the contents of a yaml study file
        @return Error message if problem, empty message if ok.
    **/
    private static function checkStudyFile(array $studyConfig): string {
        if(!isset($studyConfig['slug'])){
            return "Missing entry \"slug\"";
        }
        //
        if(!isset($studyConfig['working-dir'])){
            return "Missing entry \"working-dir\"";
        }
        if(!is_dir($studyConfig['working-dir'])){
            return "Working directory {$studyConfig['working-dir']} does not exist. Create it before executing this command";
        }
        //
        if(!isset($studyConfig['out-dir'])){
            return "Missing entry \"out-dir\"";
        }
        if(!is_dir($studyConfig['out-dir'])){
            return "Output directory {$studyConfig['out-dir']} does not exist. Create it before executing this command";
        }
        //
        if(!isset($studyConfig['planets'])){
            return "Missing entry \"planets\"";
        }
        if(($msg = IAA::checkCodes($studyConfig['planets'])) != ''){
            return $msg;
        }
        //
        if(!isset($studyConfig['splits'])){
            return "Missing entry \"splits\"";
        }
        //
// TODO check entry 'dates'
        return '';
    }
    
    /**
        Finds a class implementing IStudy, and executes its method init().
        @return Error message if problem, empty message if ok.
    **/
    private static function initializeStudy(string $studyDir, string $studyNamespace, array &$studyConfig): string {
        $files = glob(implode(DS, ['src', 'commands', $studyDir, '*.php']));
        $classes = [];
        foreach($files as $file){
            $basename = basename($file, '.php');
            try{
                $classpath = $studyNamespace . '\\' . $basename;
                $class = new \ReflectionClass($classpath);
                if($class->implementsInterface("observe\\model\\IStudy")){
                    $classes[] = $class;
                }
            }
            catch(\Exception $e){
                // silently ignore php files present in the directory, but containing errors
                // echo "ERR new \\ReflectionClass($baseClasspath) \n" . $e->getMessage() . "\n";
            }
        }
        if(count($classes) == 0){
            return "The namespace $studyNamespace doesn't contain a class implementing interface observe\\model\\IStudy";
        }
        if(count($classes) > 1){
            return "The namespace $studyNamespace doesn't contain more than one class implementing interface observe\\model\\IStudy";
        }
        // ok, one class implements IStudy, call its method init();
        $method = new \ReflectionMethod($classes[0]->name, 'init');
        // use invokeArgs() instead of invoke() to pass $studyConfig by reference
        $method->invokeArgs(null, [&$studyConfig]);
        return '';
    }
    
} // end class
