<?php
/******************************************************************************
    Like file_put_contents() but echoes a message when the file is written on disk.
    
    @license    GPL
    @history    2026-04-09 07:50:10+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\filesystem;

class file_put_contents {
    
   public static function execute(string $filename, string $contents, bool $echoMessage=true): void {
        file_put_contents($filename, $contents);
        if($echoMessage){
            echo "Wrote file $filename\n";
        }
   }    

}// end class
