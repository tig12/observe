<?php
/********************************************************************************
    Auxiliary code for run-observe.php.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2020-12-15 21:42:03+01:00, Thierry Graff : creation
    @history    2026-03-11 14:46:44+01:00, Thierry Graff : new version
********************************************************************************/
namespace observe\app;

use observe\model\Studies;
use observe\model\IStudy;
use tiglib\filesystem\globRecursive;

class Run {

    /**
        Computes the parameters passed to run-observe.php
        Returns an associative array with the following keys:
            - 'message':    string ; error message, or empty string if no error.
            - 'study-slug': string ; slug of the study
            - 'command':    string ; command to run
            - 'params':     array ; optional array of parameters to pass to the command
            
        @param  $argv   Array of parameters passed to run-observe.php
    **/
    public static function parseInput(array $argv): array {
        $scriptName = array_shift($argv);
        $possibleStudies = Studies::getAllStudySlugs();
        $possibleCommands = self::getAvailableCommands();
        $usage = "Usage: php $scriptName <study> <commmand> [args]\n"
               . "   or: php $scriptName prepare planets\n"
               . "Possible values for <study> : \n    - "
               . implode("\n    - ", $possibleStudies) . "\n"
               . "Possible values for <command> : \n    - "
               . implode("\n    - ", $possibleCommands) . "\n"
               . "\n";
        $res = [
            'study-slug'    => '',
            'command'       => '',
            'params'        => [],
            'message'       => '',
        ];
        if(count($argv) < 2){
            $res['message'] = "INVALID CALL: $scriptName needs at least two arguments\n" . $usage;
            return $res;
        }
        //
        $studySlug = $argv[0];
        $command = $argv[1];
        //
        // Particular case of prepare planets, not related to a specific study
        //
        if($studySlug == 'prepare' && $command == 'planets'){
            $res['study-slug'] = $studySlug;
            $res['command'] = $command;
            $res['params'] = array_slice($argv, 2);
            return $res;
        }
        //
        // Normal case, $studySlug should correspond to an existing study slug
        //
        if(!in_array($studySlug, $possibleStudies)){
            $res['message'] = "INVALID STUDY: \"$studySlug\"\n" . $usage;
            return $res;
        }
        if(!in_array($command, $possibleCommands)){
            $res['message'] = "INVALID COMMAND: \"$command\"\n" . $usage;
            return $res;
        }
        // ok, valid study and valid command
        $res['study-slug'] = $studySlug;
        $res['command'] = $command;
        $res['params'] = array_slice($argv, 2);
        return $res;
    }
    
    /**
        Conductor of command execution.
        Parameters $studySlug and $command are considered as valid, already checked by Run::parseInput().
        @return Error message if problem, empty message if ok.
    **/
    public static function runCommand(string $studySlug, string $command, $params=[]): string {
        $studyConfig = Studies::getStudyConfig($studySlug);
        $fqcn_study = $studyConfig['fqcn'];
        $study = new $fqcn_study($studySlug);
        return $study->$command($params);
    }
    
    /**
        Returns the classes that implement ICommand in directories
            src/commands
            src/studies
    **/
    public static function getAvailableCommands(): array {
        $files = array_merge(
            globRecursive::compute('src/commands/*.php'),
            globRecursive::compute('src/studies/*.php'),
        );
        $res = [];
        foreach($files as $file){
            $fqcn = strtr($file,[
                'src' . DS  => 'observe\\',
                '.php'      => '',
                DS          => '\\',
            ]);
            try{
                $class = new \ReflectionClass($fqcn);
            }
            catch(\ReflectionException $e){
                continue;
            }
            if(!$class->implementsInterface("observe\\app\\ICommand")){
                continue;
            }
            $res[] = basename($file, '.php');
        }
        sort($res);
        return array_unique($res);
    }
    
} // end class
