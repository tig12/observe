<?php
/********************************************************************************
    Holds config.yml information
    
    Config values available via Config::$data
    
    @license    GPL
    @history    2020-12-15 21:21:43+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\app;

class Config{
    
    /**
        Associative array containing config.yml
    **/
    public static $data = null;
    
    
    // ******************************************************
    public static function init(){
        $filename = dirname(dirname(__DIR__)) . DS . 'config.yml';
        if(!is_file($filename)){    
            echo "Unable to read configuration file : $filename.\n";
            echo "Create this file and try again.\n";
            exit;
        }
        self::$data = @yaml_parse(file_get_contents($filename));
        if(self::$data === false){
            echo "Unable to read configuration file.\n";
            echo "Check syntax and try again\n";
            exit;
        }
    }
    
    
}// end class

