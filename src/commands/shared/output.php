<?php
/******************************************************************************

    Generates the html pages to visualize the results of a given study.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:55+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use tiglib\filesystem\mkdir;

class output implements ICommand {
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        /* 
        $usage = "Usage of this command: php run-observe <study> output [<page>]\n"
            . "<split> can be:\n  - " . implode("\n  - ", $studyConfig['splits']) . "\n";
        if(count($params) != 1){
            return "MISSING PARAMETER split.\n$usage";
        }
        */
$page = 'index';
        switch($page){
        	case 'index': 
                self::generateIndex($studyConfig);
        	break;
        }
        return '';
    }
    
    /**
        @param  $
    **/
    private static function generateIndex(array &$studyConfig): void {
        $res = '';
        $V = [
            'path-to-root' => '../..',
            'date' => new \Datetime('now')->format('Y-m-d h:i:s'),
            'title' => $studyConfig['output']['title'],
            'subtitle' => $studyConfig['output']['subtitle'] ?? '',
            'description' => $studyConfig['output']['description'] ?? '',
            'intro' => $studyConfig['output']['intro'] ?? '',
        ];
        $res .= self::header($V);
        $V = [
            'dates' => $studyConfig['dates'],
            'planets' => $studyConfig['planets'],
        ];
        $res .= self::template('index.html', $V);
        $res .= self::footer($V);
        mkdir::execute($studyConfig['out-dir'], 0755, true);
        $outFilename = $studyConfig['out-dir'] . DS . 'index.html';
        file_put_contents($outFilename, $res);
        echo "Generated $outFilename\n";
    }
    
    /**
        Generates the beginning of a page
        @param  $V View variable
    **/
    private static function header(array $V): string {
        return self::template('header.html', $V);
    }
    
    /**
        Generates the beginning of a page
        @param  $V View variable
    **/
    private static function footer(array $V): string {
        return self::template('footer.html', $V);
    }
    
    
    /**
        @param  $template, relative to observe root directory
        @param  $V View variable
    **/
    private static function template(string $template, array $V): string {
        $filename = 'src/view/' . $template;
        ob_start();
        include $filename;
        $res = ob_get_contents();
        ob_end_clean();
        return $res;
    }
    
} // end class
