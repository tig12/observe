<?php
/********************************************************************************
    Auxiliary code for run-observe.php, observe CLI frontend.
    
    @license    GPL
    @history    2020-12-15 21:42:03+01:00, Thierry Graff : creation
********************************************************************************/
namespace observe\app;

use tiglib\filesystem\globRecursive;

class Run{
    
    /** 
        Returns the available command files = relative paths of YAML files located in commands/
        
        Possible values for CLI argument 1
        
        Ex : if commands/ contains
            commands
                ├── test
                │   └── toto.yml
                └── titi.yml
        It will return :
            Array(
                [0] => titi
                [1] => test/toto
            )
        This gives the list of possible strings which can be used as first argument to run-observe.php
    **/
    public static function getCommandFiles(){
        $res = [];
        $tmp = globRecursive::execute(self::commandsDir() . DS . '*.yml');
        foreach($tmp as $elt){
            // TODO keep paths ending by yml and not starting with z.
            if(!\str_ends_with($elt, '.yml')){
                continue;
            }
            $res[] = self::file2command($elt);
        }
        return $res;
    }
    
    /**  Returns the directory containing the command files **/
    private static function commandsDir(){
        return dirname(dirname(__DIR__)) . DS . 'commands';
    }
    
    /**
        Computes the file of a command string
        Ex : $cmdStr = 'test/toto' returns '/path/to/commands/test/toto.yml'
    **/
    public static function command2file($cmdStr){
        return self::commandsDir() . DS . $cmdStr . '.yml';
    }
    
    /**
        Computes command string from the path of a file
        Ex : $path = '/path/to/commands/test/toto.yml' returns 'test/toto'
    **/
    public static function file2command($path){
        $path = str_replace(self::commandsDir() . DS, '', $path);
        return str_replace('.yml', '', $path);
    }
    
}// end class
