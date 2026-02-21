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
    
    /**
        String corresponding to this command file.
        Ex: $commandFileString = 'test/toto' (corresponds to command file commands/test/toto.yml)
    **/
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
        Retruns $this->data
    **/
    public function getData(){
        return $this->data;
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
        @param  $commandStr Main entry within a command file.
                            Indicates which command in a command file should be executed.
                            Ex: if a yaml command file contains:
                                planets:
                                  command: prepareAstro
                                  # (other params)
                                distrib:
                                  command: computeDistrib
                                  # (other params)
                            Then $commandStr can be "planets" or "distrib"
        @param  $otherParams Optional parameters passed to method execute() of the command.
    **/
    public function executeCommand($commandStr, $optionalParams = []){
        if(!$this->commandExists($commandStr)){
            throw new ObserveException("The command '$commandStr' does not exist in command file " . $this->$commandFileString . '.');
        }
        // $command contains the parameters of the command within the command file.
        // ex: if $commandStr is "planets" and the command file contains:
        // planets:
        //   command: prepareAstro
        //   tmp-dir: *tmp-dir
        //   engine: meeus1
        // Then $command is this array: ['command' => 'prepareAstro', 'tmp-dir' => 'var/tmp/planets', 'engine' => 'meeus1']
        $command =& $this->data[$commandStr];
        if(!isset($command['command'])){
            throw new ObserveException("Invalid command '$commandStr': key 'command' is missing.");
        }
        // build class implementing Command
        // classname : "p3.prepare' gives "observe\commands\p3\prepare"
        $classname = 'observe\\commands\\' . str_replace('.', '\\', $command['command']);
        if(!class_exists($classname)){
            throw new ObserveException("Invalid key 'command' in command '$commandStr' : class $classname does not exist.");
        }
        // TODO check that class implements Command
        //$class = new \ReflectionClass($classname);
        $method = new \ReflectionMethod("$classname::execute");
        // method parameters = current command except 'command' entry
        unset($command['command']);
        // Add optional parameters
        $command = array_merge($command, [Observe::PARAM_OPTIONAL_STRING => $optionalParams]);
        $method->invoke(null, $command);
    }
    
                                                                                                                                          
    
}// end class
