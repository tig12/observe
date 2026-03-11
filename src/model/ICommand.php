<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    , Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

interface ICommand {
    
    /** 
        @param  $studyFile  Associative array containing the contents of a yaml command file
        @param  $params     Regular array of parameters passed to run-observe.php when calling the command.
    **/
    public static function execute(array $studyFile, array $params): string;
    
} // end class

