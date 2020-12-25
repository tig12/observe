<?php
/********************************************************************************
    Operations on a YAML command file.
    
    @license    GPL
    @history    2020-12-16 16:58:59+01:00, Thierry Graff : creation
********************************************************************************/
namespace observe;

class CommandFile {
    
    /** Array reflecting the contents of the yaml file **/
    private $data;
    
    /** String corresponding to this command file **/
    private $commandString;
    
    /** 
        Builds a new Command object
        @param $cmdStr String like 'test/toto'
    **/
    public function __construct($cmdStr){
        $file = Run::command2file($cmdStr);
        if(!\file_exists($file)){
            throw new ObserveException("The command '$cmdStr' does not correspond to an existing file");
        }
        $this->commandString = $cmdStr;
        // TODO  check if parse ok
        $this->data = \yaml_parse(file_get_contents($file));
    }
    
    /**
        Checks if a string corresponds to a valid step
    **/
    public function stepExists($str){
        return isset($this->data[$str]);
    }
    
    /**
        Returns an array of all steps of this command file
    **/
    public function getAllSteps(){
        return array_keys($this->data);
    }
    
    /**
        Executes one step
    **/
    public function executeStep($stepStr){
        if(!$this->stepExists($stepStr)){
            throw new ObserveException("The step '$stepStr' does not exist in command " . $this->commandString);
        }
        $step =& $this->data[$stepStr];
        if(!isset($step['command'])){
            throw new ObserveException("Invalid step '$stepStr': key 'command' is missing");
        }
        // build class implementing Command
        $classname = 'observe\\commands\\' . $step['command'];
        if(!class_exists($classname)){
            throw new ObserveException("Invalid key 'command' in step '$stepStr' : class $classname does not exist");
        }
        // TODO check that class implements Command
        //$class = new \ReflectionClass($classname);
        $method = new \ReflectionMethod("$classname::execute");
        // method parameters = current step except 'command' entry
        array_shift($step);
        $method->invoke(null, $step);
    }
    
                                                                                                                                          
    
}// end class
