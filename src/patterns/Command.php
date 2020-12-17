<?php
/******************************************************************************
    Interface definition for Command design pattern
    
    @license    GPL
    @history    2020-12-16 18:16:31+01:00, Thierry Graff : Creation
********************************************************************************/
namespace distrib\patterns;

interface Command {
    
    /** 
        Do something
        @return report : string describing the result of execution.
    **/
    //public static function execute($params=[]): string;
    public static function execute($params=[]);
    
} // end interface
