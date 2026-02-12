<?php
/********************************************************************************
    General constants of Observe program
    
    @license    GPL
    @history    2020-12-17 16:02:16+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\app;

class Observe{
    
    /** Separator used in csv files **/
    const CSV_SEP = ';';    
    
    /** 
        String used to pass optional parameters to a command, see CommanfFile::executeCommand().
    **/
    const PARAM_OPTIONAL_STRING = '__OPTIONAL__';
    
}// end class

