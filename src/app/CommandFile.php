<?php
/********************************************************************************
    Operations on a YAML command file.
    
    @license    GPL
    @history    2020-12-16 16:58:59+01:00, Thierry Graff : creation
********************************************************************************/
namespace observe\app;

class CommandFile {
    
    /** Array reflecting the contents of the yaml file **/
    private $data;
    
    /** String corresponding to this command file **/
    private $commandFileString;
    
    /** 
        Builds a new Command object
        @param $cmdFileStr String designating a command file.
               Ex: $cmdFileStr = 'test/toto' (corresponds to command file commands/test/toto.yml)
    **/
    public function __construct($cmdFileStr){
        $file = Run::command2file($cmdFileStr);
        if(!\file_exists($file)){
            throw new ObserveException("The command '$cmdFileStr' does not correspond to an existing file");
        }
        $this->commandFileString = $cmdFileStr;
        // TODO  check if parse ok
        $this->data = \yaml_parse(file_get_contents($file));
    }
    
    /**
        Checks if a string corresponds to a valid command
    **/
    public function commandExists($str){
        return isset($this->data[$str]);
    }
    
    /**
        Returns an array of all commands of this command file
        = all main keys of command file, except "variables"
    **/
    public function getAllCommands(){
        $tmp = array_keys($this->data);
        if (($key = array_search("variables", $tmp)) !== false) {
            unset($tmp[$key]);
        }
        return $tmp;
    }
    
    /**
        Executes one command
    **/
    public function executeCommand($commandStr){
        if(!$this->commandExists($commandStr)){
            throw new ObserveException("The command '$commandStr' does not exist in command file " . $this->$commandFileString . '.');
        }
        $command =& $this->data[$commandStr];
        if(!isset($command['command'])){
            throw new ObserveException("Invalid command '$commandStr': key 'command' is missing.");
        }
        // build class implementing Command
        // classname : "3p.prepare' gives "observe\commands\3p\prepare"
        $classname = 'observe\\commands\\' . str_replace('.', '\\', $command['command']);
        if(!class_exists($classname)){
            throw new ObserveException("Invalid key 'command' in command '$commandStr' : class $classname does not exist.");
        }
        // TODO check that class implements Command
        //$class = new \ReflectionClass($classname);
        $method = new \ReflectionMethod("$classname::execute");
        // method parameters = current command except 'command' entry
        array_shift($command);
        $method->invoke(null, $command);
    }
    
                                                                                                                                          
    
}// end class
