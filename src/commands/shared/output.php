<?php
/******************************************************************************

    Generates the html pages to visualize the results of a given study.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:55+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use observe\model\distrib\CsvDistrib;
use tiglib\filesystem\mkdir;
use tigeph\model\IAA;
use tigdraw\bar;

class output implements ICommand {
    
    const array POSSIBLE_ACTIONS = [
        'page'          => 'Generate html page(s) of the output',
        'img'           => 'Generate images included in html pages',
    ];
    
    const array POSSIBLE_PAGES = [
        'all'           => 'Generate pages "index", "distrib1" and "distrib2"',
        'index'         => 'Generate home page of the study output',
        'distrib1'      => 'Generate distributions of type distrib1',
        'distrib2'      => 'Generate distributions of type distrib1',
    ];
    
    const array POSSIBLE_IMG = [
        'all'           => 'Generate all images for the output',
        'distrib1'      => 'Generate images for distributions of type distrib1',
        'distrib2'      => 'Generate images for distributions of type distrib2',
    ];
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> output <action> <object>\n"
            . "<action> can be:\n";
            foreach(self::POSSIBLE_ACTIONS as $k => $v){
                $usage .= str_pad("    $k:", 17) . "$v\n";
            }
            $usage .= "If <action> = \"page\", <object> can be:\n";
            foreach(self::POSSIBLE_PAGES as $k => $v){
                $usage .= str_pad("    $k:", 17) . "$v\n";
            }
            $usage .= "If <action> = \"img\", <object> can be:\n";
            foreach(self::POSSIBLE_IMG as $k => $v){
                $usage .= str_pad("    $k:", 17) . "$v\n";
            }
        if(count($params) != 2){
            return "WRONG NUMBER OF ARGUMENTS.\n$usage";
        }
        if($params[0] == 'page' && !in_array($params[1], array_keys(self::POSSIBLE_PAGES))){
            return "INVALID PARAMETER object: \"{$params[1]}\".\n$usage";
        }
        if($params[0] == 'img' && !in_array($params[1], array_keys(self::POSSIBLE_IMG))){
            return "INVALID PARAMETER object: \"{$params[1]}\".\n$usage";
        }
        //
        // Execution
        //
        if($params[0] == 'page'){
            $page = $params[1];
            switch($page){
                case 'index':  self::generatePageIndex($studyConfig); break;
                case 'distrib1':  self::generatePageDistrib1($studyConfig); break;
                case 'distrib2':  self::generatePageDistrib2($studyConfig); break;
                case 'all':
                    self::generatePageIndex($studyConfig);
                    self::generatePageDistrib1($studyConfig);
                    self::generatePageDistrib2($studyConfig);
                break;
            }
        }
        else{
            $img = $params[1];
            switch($img){
                case 'distrib1':  self::generateImgDistrib1($studyConfig); break;
                case 'distrib2':  self::generateImgDistrib2($studyConfig); break;
                case 'all':
                    self::generateImgDistrib1($studyConfig);
                    self::generateImgDistrib2($studyConfig);
                break;
            }
        }
        return '';
    }
    
    //
    //  Pages
    //
    
    /**
        Computes a html page using variables stored in $V.
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
    
    /** 
        Generates the home page of a given study.
    **/
    private static function generatePageIndex(array &$studyConfig): void {
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
        Generates the pages of type distrib1 (distributions of a single date)
    **/
    private static function generatePageDistrib1(array &$studyConfig): void {
        foreach($studyConfig['dates'] as $dateName){
            $res = '';
            $V = [
                'path-to-root' => '../..',
                'date' => new \Datetime('now')->format('Y-m-d h:i:s'),
                'title' => ucFirst($dateName),
                'subtitle' => $studyConfig['output']['title'] ?? '',
                'description' => '',
                'intro' => '',
            ];
            $res .= self::header($V);
            $V = [
                'planets' => $studyConfig['planets'],
            ];
            $res .= self::template('distrib1.html', $V);
            $res .= self::footer($V);
            mkdir::execute($studyConfig['out-dir'], 0755, true);
            $outFilename = $studyConfig['out-dir'] . DS . $dateName . '.html';
            file_put_contents($outFilename, $res);
            echo "Generated $outFilename\n";
        }
    }
    
    /**
        Generates the beginning of a page
        @param  $V View variable
    **/
    private static function header(array $V): string {
        return self::template('header.html', $V);
    }
    
    /**
        Generates the end of a page
        @param  $V View variable
    **/
    private static function footer(array $V): string {
        return self::template('footer.html', $V);
    }
    
    //
    //  Images
    //
    
    /**
        Generates and stores SVG images of distributions of type distrib1.
    **/
    private static function generateImgDistrib1(array &$studyConfig): void {
        // WARNING: temporary code - arguments $split and $subgroup shouldn't be hard-coded
        // but passed as parameters to this function
        $baseInDir = Studies::getObservedDirectory($studyConfig, 'full', '01--0-200years');
        $baseOutDir = $studyConfig['out-dir'] . DS . 'img';
        $nDates = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i]; // ex: birth
            $inDateDir = $baseInDir . DS . $dateName; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth
            $outDateDir = $baseOutDir . DS . $dateName; // ex: output/studies/death-fr/birth
            // aspects and planets
            foreach(['aspects', 'planets'] as $distribType){
                $inDistribTypeDir = $inDateDir . DS . $distribType; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/aspects
                $outDistribTypeDir = $outDateDir . DS . $distribType; // ex: output/studies/death-fr/birth/aspects
                $inFilenames = glob($inDistribTypeDir . DS . '*.csv');
                foreach($inFilenames as $inFilename){
                    $distrib = CsvDistrib::csv2distrib($inFilename, false);
                    $outFilename = $outDistribTypeDir . DS . basename($inFilename, '.csv') . '.svg';
                    $title = ucfirst($dateName) . ' year';
                    $img_src = str_replace($studyConfig['out-dir'] . DS, '', $outFilename);
echo "$img_src\n";
                    /* [$html_markup, $file_contents] = bar::svg(
                        data:           $distrib,
                        title:          $title,
                        svg_separate:   true,
                        img_src:        $img_src,
                        img_alt:        $title,
                        barW:           8,
                        xlegends:       ['min', 'max'],
                        ylegends:       ['min', 'max', 'mean'],
                        ylegendsRound:  1,
                        meanLine:       true,
                        //stats:          $stats,
                    ); */
                }
            }
            // day and year
            foreach(['day', 'year'] as $distribName){
                $filename = $inDateDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/day.csv
                $distrib = CsvDistrib::csv2distrib($filename, false);
            }
        }
    }
    
    
    
} // end class
