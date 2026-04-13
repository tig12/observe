<?php
/******************************************************************************
    
    

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 14:59:09+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

use tiglib\filesystem\globRecursive;
use tiglib\filesystem\mkdir;
use tigeph\model\IAA;

class Studies {
    
    /** All commands that can be run on a study **/
    const AVAILABLE_COMMANDS = [
        'init',
        'split',
        'observed',
        'control',
        'expected',
        'stats',
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
        
        $studyNamespace = 'observe\\commands\\' . self::getStudyNamespace($studySlug);
        $sharedNamespace = 'observe\\commands\\shared';
        
        // Here we cheat because we know that current function is called after self::getAllStudySlugs()
        // (called in observe\app\Run::parseOutput())
        // then self::self::$studyConfigs is already computed
        $studyConfig = self::$studyConfigs[$studySlug];
        $studyConfig['__command__'] = $command;
        if(($msg = self::checkStudyFile($studyConfig)) != ''){
            return "ERROR in study file {$studyConfig['__study-file__']}:\n$msg\n";
        }
        
        // Before calling the command, handle the computations specific to each study:
        // call method init() of a class implementing IStudy located in the package specific to the command
        if(($msg = self::initializeStudy($studyConfig)) != ''){
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
    
    /**
        Returns the fqcn (fully qualified class name) of a class implementing IStudy.
        Based on a convention: the namespace relative to a study and the name of the class implementing IStudy is built from the class slug.
        For example, for study slug death-fr:
        - the namespace is observe\commands\death_fr
        - the class implementing Istudy is observe\commands\death_fr\Death_fr
    **/
    public static function getStudyClasspath(string $studySlug): string {
        $namespace = self::getStudyNamespace($studySlug);
        return 'observe\\commands\\' . $namespace . '\\' . ucfirst($namespace); // ex: observe\commands\death_fr\Death_fr
    }
    
    /** Returns the namespace containing a class. Not fully qualified. **/
    public static function getStudyNamespace(string $studySlug): string {
        return str_replace('-', '_', $studySlug); // ex: death_fr
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
        Fills self::$studyConfigs with all available study configs (here, "study config" = content of a yaml study file).
        Completes each element of self::$studyConfigs with a key "__study-file__".
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
                // (with a supplementary entry: '__study-file__')
                //
                self::$studyConfigs[$slug] = [...$studyConfig, ...['__study-file__' => $file]];
                $res[] = $slug;
            }
        }
        return array_keys(self::$studyConfigs);
    }
    
    /**
        Finds a class implementing IStudy, and executes its method init().
        (public for phpunit)
        @return Error message if problem, empty message if ok.
    **/
    public static function initializeStudy(array &$studyConfig): string {
        try{
            $classpath = self::getStudyClasspath($studyConfig['slug']);
            $class = new \ReflectionClass($classpath);
            if(!$class->implementsInterface("observe\\model\\IStudy")){
                return "The class $classpath doesn't implement interface observe\\model\\IStudy";
            }
        }
        catch(\ReflectionException $e){
            return "The class $classpath doesn't exist";
        }
        $method = new \ReflectionMethod($class->name, 'init');
        // use invokeArgs() instead of invoke() to pass $studyConfig by reference
        $method->invokeArgs(null, [&$studyConfig]);
        return '';
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
            mkdir::execute($studyConfig['working-dir']);
            // return "Working directory {$studyConfig['working-dir']} does not exist. Create it before executing this command";
        }
        //
        if(!isset($studyConfig['out-dir'])){
            return "Missing entry \"out-dir\"";
        }
        if(!is_dir($studyConfig['out-dir'])){
            mkdir::execute($studyConfig['out-dir']);
            // return "Output directory {$studyConfig['out-dir']} does not exist. Create it before executing this command";
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
        Returns the directory containing the observed distributions of a subgroup of a given split of a study.
    **/
    public static function getObservedDirectory(array &$studyConfig, string $split, string $subgroup): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split . DS . $subgroup . DS . 'observed';
    }
    
    /**
        Returns the directory containing the expected distributions of a subgroup of a given split of a study.
    **/
    public static function getExpectedDirectory(array &$studyConfig, string $split, string $subgroup): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split . DS . $subgroup . DS . 'expected';
    }
    
    /**
        Returns the directory containing all the controls of a study.
    **/
    public static function getControlsDirectory(array &$studyConfig): string {
        return $studyConfig['working-dir'] . DS . 'controls';
    }
    
    /**
        Returns the directory containing the intermediate files of a given split of a study.
    **/
    public static function getSplitDirectory(array &$studyConfig, string $split): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split;
    }
    
    /**
        Returns the names of the subgroups of a split = the names of the directories of a split
        ex:
            var/studies/death-fr/split-age/
                ├── 01--0-2days
                ├── 02--2days-2months
                ├── 03--2months-6months
                ├── 04--6months-2years
                ├── 05--2years-5years
                ├── 06--5years-20years
                ├── 07--20years-50years
                ├── 08--50years-90years
                └── 09--90years-200years
    **/
    public static function getSplitSubgroups(array &$studyConfig, string $split): array {
        return self::getStudyClasspath($studyConfig['slug'])::getSplitSubgroups($split);
    }
    
    /**
        Returns the directory containing the intermediate files of a given split of a study.
    **/
    public static function getSubgroupDirectory(array &$studyConfig, string $split, string $subgroup): string {
        return self::getSplitDirectory($studyConfig, $split) . DS . $subgroup;
    }
    
} // end class
