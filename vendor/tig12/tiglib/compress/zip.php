<?php
/******************************************************************************
    Utilities to manipulate zip files.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-01 16:06:08+02:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\compress;

class zip {
    
    /**
        Adds a single file to a zip archive and stores it on disk.
        @param  $inFile     Filename of the uncompressed file to zip.
        @param  $outFile    Filename of the resulting zip file.
    **/
    public static function zipFile(string $inFile, $outFile): void {
        $zip = new \ZipArchive();
        if($zip->open($outFile, \ZipArchive::CREATE) === true) {
            $zip->addFile($inFile, basename($inFile));
            $zip->close(); // here, writes on disk
        }
    }
    
} // end class
