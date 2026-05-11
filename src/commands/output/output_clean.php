<?php
/******************************************************************************

    Deletes contents in the subdirectory of a study in output/studies
    Called by commands/output.php
    
    Example of call: php run-observe.php death-fr output clean img

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-01 11:23:55+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\output;

use observe\model\IStudy;
use observe\app\Params;
use tiglib\filesystem\rrmdir;

class output_clean {
    
    const array POSSIBLE_WHAT = [
        'all'           => 'Delete all images and pages of the output',
        'page'          => 'Delete all html pages of the output',
        'img'           => 'Delete all images of the output',
    ];

    /**
        Called by output::execute()
        
        @param  $params are parameters passed to command output, so $params[0] = 'clean'.
        @return Error message or empty string if ok.
    **/
    public static function execute(IStudy $study, array $params): string {
        if(!in_array($params[1], array_keys(self::POSSIBLE_WHAT))){
            return "INVALID PARAMETER object: \"{$params[1]}\".";
        }
        $what = $params[1];
        
        $dir = $study->getOutputDirectory();
        $answer = Params::answerYN("WARNING: This operation will permanently delete contents of $dir.\n");
        if($answer !== true) {
            return '';
        }
        
        switch($what){
            case 'page':    self::cleanPages($study); break;
            case 'img':     self::cleanImages($study); break;
            case 'all':
                self::cleanPages($study);
                self::cleanImages($study);
            break;
        }
        return '';
    }
    
    private static function cleanPages(IStudy $study): void {
        $files = glob($study->getOutputDirectory() . DS . '*.html');
        foreach($files as $file){
            echo "Deleting $file\n";
            unlink($file);
        }
    }
    
    private static function cleanImages(IStudy $study): void {
        $dir = $study->getOutputImgDirectory();
        echo "Deleting $dir\n";
        rrmdir::execute($dir);
    }
    
} // end class
