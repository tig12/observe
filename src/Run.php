<?php
/********************************************************************************
    Auxiliary code for run-distrib.php, distrib CLI frontend.
    
    @license    GPL
    @history    2020-12-15 21:42:03+01:00, Thierry Graff : creation
********************************************************************************/
namespace distrib;

use tiglib\filesystem\globRecursive;

class Run{
    
    /** 
        Returns the available commands = relative paths of YAML files located in commands/
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
        This gives the list of possible strings which can be used as first argument to run-distrib.php
    **/
    public static function getCommands(){
        $res = [];
        $tmp = globRecursive::execute(self::commandsDir() . DS . '*.yml');
        foreach($tmp as $elt){
            if(!\str_ends_with($elt, '.yml')){
                continue;
            }
            $res[] = self::file2command($elt);
        }
        return $res;
    }
    
    
    
    /**  Returns the directory containing the commands **/
    public static function commandsDir(){
        return dirname(__DIR__) . DS . 'commands';
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
