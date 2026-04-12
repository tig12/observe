<?php
/******************************************************************************

    Generates the html pages to visualize the results of a given study.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:55+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\commands\shared\output\output_page;
use observe\commands\shared\output\output_img;

use observe\model\Studies;
use observe\model\distrib\StatsDistrib;
use observe\model\distrib\CsvDistrib;
use observe\model\draw\xlegend;

use tiglib\filesystem\mkdir;
use tiglib\filesystem\file_put_contents;
use tigdraw\bar;
use tigstats\center;
use tigstats\dispersion;
class output implements ICommand {
    
    const array POSSIBLE_WHAT = [
        'all'           => 'Generate pages "index", "distrib1" and "distrib2"',
        'index'         => 'Generate home page of the study output',
        'distrib1'      => 'Generate distributions of type distrib1',
        'distrib2'      => 'Generate distributions of type distrib1',
    ];
    
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> output <what>\n<what> can be:\n";
        foreach(self::POSSIBLE_WHAT as $k => $v){
            $usage .= str_pad("    $k:", 17) . "$v\n";
        }
        if(count($params) != 1){
            return "WRONG NUMBER OF ARGUMENTS.\n$usage";
        }
        $what = $params[0];
        if(!in_array($what, array_keys(self::POSSIBLE_WHAT))){
            return "INVALID VALUE for argument <what>: \"$what\".\n$usage";
        }
        // ****** WARNING Temporary code ******
        // pass in $params
        $do_img = true;
        $do_page = true;
        // ****** WARNING Temporary code ******
        // arguments $split and $subgroup shouldn't be hard-coded
        $split = 'full';
        $subgroup = '01--0-200years';
        //
        // Execution
        //
        switch($what){
            case 'index':  self::generateIndex($studyConfig); break;
            case 'distrib1':  self::generateDistrib1($studyConfig, $split, $subgroup, $do_page, $do_img); break;
            case 'distrib2':  self::generateDistrib2($studyConfig, $split, $subgroup, $do_page, $do_img); break;
            case 'all':
                self::generateIndex($studyConfig, $split, $subgroup, $do_page, $do_img);
                self::generateDistrib1($studyConfig, $split, $subgroup, $do_page, $do_img);
                self::generateDistrib2($studyConfig, $split, $subgroup, $do_page, $do_img);
            break;
        }
        return'';
    }
    
    /** 
        Generates and stores the home page of a given study.
    **/
    private static function generateIndex(array &$studyConfig): void {
        $res = '';
        //
        $V = [
            'path-to-root' => '../..',
            'date' => new \Datetime('now')->format('Y-m-d h:i:s'),
            'title' => $studyConfig['output']['title'],
            'subtitle' => $studyConfig['output']['subtitle'] ?? '',
            'description' => $studyConfig['output']['description'] ?? '',
            'intro' => $studyConfig['output']['intro'] ?? '',
        ];
        $res .= output_page::header($V);
        //
        $V = [
            'dates' => $studyConfig['dates'],
            'planets' => $studyConfig['planets'],
        ];
        $res .= output_page::template('index.html', $V);
        //
        $res .= output_page::footer($V);
        mkdir::execute($studyConfig['out-dir'], 0755, true);
        $outFilename = $studyConfig['out-dir'] . DS . 'index.html';
        file_put_contents::execute($outFilename, $res);
    }
    
    /** 
        Generates and stores the pages of type distrib1 (distributions of a single date).
        Ex: birth.html and death.html
        
    **/
    private static function generateDistrib1(array &$studyConfig, string $split, string $subgroup, bool $do_page, bool $do_img): void {
        
        $inDir_base = Studies::getObservedDirectory($studyConfig, $split, $subgroup);
        $outDir_base = $studyConfig['out-dir'];         // ex: output/studies/death-fr
        $outDir_base_img = $outDir_base . DS . 'img';   // ex: output/studies/death-fr/img
        
        $statsDistribs = StatsDistrib::loadStats($studyConfig, $split, $subgroup);
        
        foreach($studyConfig['dates'] as $dateName){    // ex: $dateName = birth
            $dateNameLabel = ucFirst($dateName);
            $inDir_date = $inDir_base . DS . $dateName;             // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth
            $outDir_date_img = $outDir_base_img . DS . $dateName;   // ex: output/studies/death-fr/img/birth
            mkdir::execute($outDir_date_img);
            //
            // day
            //
            $inFilename = $inDir_date . DS . 'day.csv';     // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/day.csv
            $distrib = CsvDistrib::csv2distrib($inFilename, false);
            $stats = $statsDistribs[$dateName]['day'];            
            $outFilename = $outDir_date_img . DS . 'day.svg';   // ex: output/studies/death-fr/img/birth/day.svg
            $svg = bar::svg(
                data:           $distrib,
                title:          $dateNameLabel . ' - Days',
                svg_separate:   true,
                barW:           2,
                xlegends:       xlegend::month(),
                ylegends:       ['min', 'max', 'mean'],
                ylegendsRound:  1,
                //meanLine:       true,
                stats:          $stats,
            );
            file_put_contents::execute($outFilename, $svg, echoMessage: true);
exit;            
            //
            if($do_page){
                $res = '';
                $V = [
                    'path-to-root' => '../..',
                    'date' => new \Datetime('now')->format('Y-m-d h:i:s'),
                    'title' => ucFirst($dateName),
                    'subtitle' => $studyConfig['output']['title'] ?? '',
                    'description' => '',
                    'intro' => '',
                ];
                $res .= output_page::header($V);
                //
                $V = [
                    'date-name' => $dateName,
                    'planets' => $studyConfig['planets'],
                ];
                $res .= output_page::template('distrib1.html', $V);
                //
                $res .= output_page::footer($V);
                mkdir::execute($studyConfig['out-dir']);
                $outFilename = $studyConfig['out-dir'] . DS . $dateName . '.html';
                file_put_contents::execute($outFilename, $res);
            }
        } // end foreach($studyConfig['dates']
    }
    
    
} // end class
