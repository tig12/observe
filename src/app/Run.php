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
        $usage = "Usage: php $scriptName <study> <commmand> [args]\n"
               . "   or: php $scriptName prepare planets\n"
               . "Possible values for <study> : \n    - "
               . implode("\n    - ", $possibleStudies)
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
        $allSlugs = Studies::getAllStudySlugs();
        if(!in_array($studySlug, $allSlugs)){
            $res['message'] = "INVALID STUDY: \"$studySlug\"\n"
                . "Possible studies: \"" . implode('", "', $allSlugs) . "\"\n";
            return $res;
        }
        // ok, valid study
        $res['study-slug'] = $studySlug;
        $res['command'] = $command;
        $res['params'] = array_slice($argv, 2);
        return $res;
    }
    
} // end class
