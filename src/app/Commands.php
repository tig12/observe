<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff

    @history    2026-04-22 14:36:03+02:00, Thierry Graff : Creation from a split of Studies.php
********************************************************************************/

namespace observe\app;

use observe\model\Studies;
use observe\model\IStudy;
use tiglib\filesystem\globRecursive;

use observe\studies\death_fr\Death_fr;


class Commands {
    
    /**
        Conductor of command execution.
        Parameters $studySlug and $command are considered as valid, already checked by Run::parseInput().
        @return Error message if problem, empty message if ok.
    **/
    public static function runCommand(string $studySlug, string $command, $params=[]): string {
        
        $fqcn = Studies::getStudyClasspath($studySlug);
        $study = new $fqcn($studySlug);
        
        //
        if(in_array($command, [
                'init',
                'import',
                'control'
        ])){
            // commands that must be implemented by each study
            $namespace = 'observe\\studies\\' . Studies::getStudyNamespace($studySlug);
        }
        else{
            // Same implementation for all studies
            $namespace = 'observe\\commands';
        }
        $fqcn = $namespace . '\\' . $command;
        //
        $msg = $fqcn::execute($study, $params);
        return $msg;
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
