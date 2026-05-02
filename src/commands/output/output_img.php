<?php
/******************************************************************************

    Code to generate the images to visualize distributions.
    Called by commands/output.php

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-10 14:57:52+01:00, Thierry Graff : Refactor
    @history    2026-04-09 10:27:15+01:00, Thierry Graff : Creation from a split of output.php
********************************************************************************/

namespace observe\commands\output;

use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use observe\model\distrib\StatsDistrib;

use tigdraw\barCurve;
use tigdraw\table;
use tigdraw\util\xlegend;
use tiglib\stats\chi2Table;
use tiglib\stats\chi2;
use tiglib\filesystem\mkdir;
use tiglib\filesystem\file_put_contents;
use tigeph\model\IAA;

class output_img {
    
    const array POSSIBLE_WHAT = [
        'all'       => 'Generate all images for the output',
        'distrib1'  => 'Generate images for distributions of type distrib1',
        'distrib2'  => 'Generate images for distributions of type distrib2',
        'dim2'      => 'Generate images of tables for dim2 distributions',
    ];
    
    /**
        Called by output::execute()
        
        @param  $params are parameters passed to command output, so $params[0] = 'img'.
        @return Error message or empty string if ok.
    **/
    public static function execute(IStudy $study, array $params): string {
        //
        // Parameter check
        //
        if(!in_array($params[1], array_keys(self::POSSIBLE_WHAT))){
            return "INVALID PARAMETER object: \"{$params[1]}\".";
        }
        $what = $params[1];
        //
        // Execution
        //
        //
        // ****** WARNING Temporary code ******
        $echoMessage = true; // concerns filename generation
        // ****** END WARNING ******
        
        switch($what){
            case 'distrib1':    self::generateDistrib1($study, $echoMessage); break;
            case 'distrib2':    self::generateDistrib2($study, $echoMessage); break;
            case 'dim2':        self::generateDim2($study, $echoMessage); break;
            case 'all':
                self::generateDistrib1($study, $echoMessage);
                self::generateDistrib2($study, $echoMessage);
                self::generateDim2($study, $echoMessage);
            break;
        }
        return '';
    }
    
    /**
        Generates and stores SVG images of distributions of type distrib1.
    **/
    private static function generateDistrib1(IStudy $study, bool $echoMessage): void {
        
        $inDir_obs_base = $study->getObservedDirectory();   // ex: var/studies/death-fr/observed
        $inDir_exp_base = $study->getExpectedDirectory();   // ex: var/studies/death-fr/expected
        
        $stats_obs = StatsDistrib::loadStats($inDir_obs_base);
        
        $outDir_base = $study->getOutputImgDirectory();    // ex: output/studies/death-fr/img
        
        foreach($study->config['dates'] as $dateName){    // ex: $dateName = birth
            $dateNameLabel = ucFirst($dateName);
            $inDir_obs_date = $inDir_obs_base . DS . $dateName;     // ex: var/studies/death-fr/observed/birth
            $inDir_exp_date = $inDir_exp_base . DS . $dateName;     // ex: var/studies/death-fr/expected/birth
            //
            $outDir_date = $outDir_base . DS . $dateName;           // ex: output/studies/death-fr/img/birth
            mkdir::execute($outDir_date);
            //
            // day
            //
            $inFilename_obs = $inDir_obs_date . DS . 'day.csv';     // ex: var/studies/death-fr/observed/birth/day.csv
            $inFilename_exp = $inDir_exp_date . DS . 'day.csv';     // ex: var/studies/death-fr/expected/birth/day.csv
            $distrib_obs = CsvDistrib::csv2distrib_dim1($inFilename_obs);
            $distrib_exp = CsvDistrib::csv2distrib_dim1($inFilename_exp);
            $stats = $stats_obs[$dateName]['day'];
            $svg = barCurve::svg(
                data_bar:       $distrib_obs,
                data_curve:     $distrib_exp,
                title:          $dateNameLabel . ' - Days',
                svg_separate:   true,
                xlegends:       xlegend::month(),
                ylegends:       ['min', 'max', 'mean'],
                //meanLine:       true,
                stats:          $stats,
            );
            $outFilename = $outDir_date . DS . 'day.svg';           // ex: output/studies/death-fr/img/birth/day.svg
            file_put_contents::execute($outFilename, $svg, echoMessage: $echoMessage);
            //
            // year
            //
            // no $distrib_exp for years (because observed and expected distribs can be of different size)
            $inFilename_obs = $inDir_obs_date . DS . 'year.csv';    // ex: var/studies/death-fr/observed/birth/year.csv
            $distrib_obs = CsvDistrib::csv2distrib_dim1($inFilename_obs);
            $stats = $stats_obs[$dateName]['year'];
            $svg = barCurve::svg(
                data_bar:       $distrib_obs,
                title:          $dateNameLabel . ' - Years',
                barW:           3,
                xlegends:       xlegend::step($distrib_obs, 10),
                ylegends:       ['min', 'max', 'mean'],
                stats:          $stats,
            );
            $outFilename = $outDir_date . DS . 'year.svg';          // ex: output/studies/death-fr/img/birth/year.svg
            file_put_contents::execute($outFilename, $svg, echoMessage: $echoMessage);
            //
            // Planet positions
            //
            $nPlanets = count($study->config['planets']);
            $outDir = $outDir_date . DS . 'positions';                // ex: output/studies/death-fr/birth/positions
            mkdir::execute($outDir);
            foreach($study->config['planets'] as $planet){
                $inFilename_obs = $inDir_obs_date . DS . 'positions' . DS . $planet . '.csv';     // ex: var/studies/death-fr/observed/birth/positions/SO.csv
                $inFilename_exp = $inDir_exp_date . DS . 'positions' . DS . $planet . '.csv';     // ex: var/studies/death-fr/expected/birth/positions/SO.csv
                $distrib_obs = CsvDistrib::csv2distrib_dim1($inFilename_obs);
                $distrib_exp = CsvDistrib::csv2distrib_dim1($inFilename_exp);
                $title = ucfirst($dateName) . ' - position of ' . strtolower(IAA::PLANET_NAMES[$planet]);
                $stats = $stats_obs[$dateName]['positions'][$planet];
                $svg = barCurve::svg(
                    data_bar:       $distrib_obs,
                    data_curve:     $distrib_exp,
                    title:          $title,
                    xlegends:       xlegend::angle360(),
                    ylegends:       ['min', 'max', 'mean'],
                    stats:          $stats,
                );
                $outFilename = $outDir . DS . $planet . '.svg';     // ex: output/studies/death-fr/img/birth/positions/SO.svg
                file_put_contents::execute($outFilename, $svg, echoMessage: $echoMessage);
            }
            //
            // Aspects
            //
            $outDir = $outDir_date . DS . 'aspects' . DS . 'dim1'; // ex: output/studies/death-fr/birth/aspects/dim1
            mkdir::execute($outDir);
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $code = $study->config['planets'][$j] . '-' . $study->config['planets'][$k];
                    $inFilename_obs = $inDir_obs_date . DS . 'aspects' . DS . 'dim1' . DS . $code . '.csv';   // ex: var/studies/death-fr/observed/birth/aspects/dim1/SO-MO.csv
                    $inFilename_exp = $inDir_exp_date . DS . 'aspects' . DS . 'dim1' . DS . $code . '.csv';   // ex: var/studies/death-fr/expected/birth/aspects/dim1/SO-MO.csv
                    $distrib_obs = CsvDistrib::csv2distrib_dim1($inFilename_obs);
                    $distrib_exp = CsvDistrib::csv2distrib_dim1($inFilename_exp);
                    $title = ucfirst($dateName) . ' - Aspects '
                        . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$j]])
                        . ' - ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$k]]);
                    $stats = $stats_obs[$dateName]['aspects'][$code];
                    // normal barCurve
                    $svg = barCurve::svg(
                        data_bar:       $distrib_obs,
                        data_curve:     $distrib_exp,
                        title:          $title,
                        xlegends:       xlegend::angle360(),
                        ylegends:       ['min', 'max', 'mean'],
                        stats:          $stats,
                    );
                    $outFilename = $outDir . DS . $code . '.svg'; // ex: output/studies/death-fr/img/birth/aspects/dim1/SO-MO.svg
                    file_put_contents::execute($outFilename, $svg, echoMessage: $echoMessage);
                }
            }
        } // end loop on dates
        echo "Images generated for distributions of type distrib1\n";
    }
    
    /**
        Generates and stores SVG images of distributions of type distrib2.
    **/
    private static function generateDistrib2(IStudy $study, bool $echoMessage): void {
        
        $inDir_obs_base = $study->getObservedDirectory();       // ex: var/studies/death-fr/observed
        $inDir_exp_base = $study->getExpectedDirectory();       // ex: var/studies/death-fr/expected
        $stats_obs = StatsDistrib::loadStats($inDir_obs_base);
        $outDir_base = $study->getOutputImgDirectory();            // ex: output/studies/death-fr/img
        $nPlanets = count($study->config['planets']);
        
        for($i=0; $i < count($study->config['dates']); $i++){
            for($j=$i+1; $j < count($study->config['dates']); $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                $inDir_obs_date = $inDir_obs_base . DS . $dateName;     // ex: var/studies/death-fr/observed/birth-death
                $inDir_exp_date = $inDir_exp_base . DS . $dateName;     // ex: var/studies/death-fr/expected/birth-death
                //
                $outDir_date = $outDir_base . DS . $dateName;           // ex: output/studies/death-fr/img/birth-death
                mkdir::execute($outDir_date);
                //
                // age
                //
                $inFilename_obs = $inDir_obs_date . DS . 'age' . DS . 'dim1' . DS . 'age-Y.csv';     // ex: var/studies/death-fr/observed/birth-death/age/dim1/age-Y.csv
                // no expected distribution for age (observed and expected can have different size)
                $distrib_obs = CsvDistrib::csv2distrib_dim1($inFilename_obs);
                $title = 'Age between ' . $study->config['dates'][$i] . ' and ' . $study->config['dates'][$j] . ' (years)';
                $stats = $stats_obs[$dateName]['age'];
                $svg = barCurve::svg(
                    data_bar:       $distrib_obs,
                    title:          $title,
                    barW:           3,
                    xlegends:       xlegend::minmax_step($distrib_obs, 10),
                    ylegends:       ['min', 'max', 'mean'],
                    meanLine:       true,
                    stats:          $stats,
                );
                $outDir = $outDir_date . DS . 'age' . DS . 'dim1'; // ex: output/studies/death-fr/img/birth-death/age/dim1
                mkdir::execute($outDir);
                $outFilename = $outDir . DS . 'age-Y.svg';    // ex: output/studies/death-fr/img/birth-death/age/dim1/age-Y.svg
                file_put_contents::execute($outFilename, $svg, echoMessage: $echoMessage);
                //
                // Interaspects
                //
                $outDir = $outDir_date . DS . 'interaspects' . DS . 'dim1';    // ex: output/studies/death-fr/img/birth-death/interaspects/dim1
                mkdir::execute($outDir);
                for($k=0; $k < $nPlanets; $k++){
                    for($l=0; $l < $nPlanets; $l++){
                        $code = $study->config['planets'][$k] . '-' . $study->config['planets'][$l];
                        $inFilename_obs = $inDir_obs_date . DS . 'interaspects' . DS . 'dim1' . DS . $code . '.csv';     // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1/SO-SO.csv
                        $inFilename_exp = $inDir_exp_date . DS . 'interaspects' . DS . 'dim1' . DS . $code . '.csv';     // ex: var/studies/death-fr/expected/birth-death/interaspects/dim1/SO-SO.csv
                        $distrib_obs = CsvDistrib::csv2distrib_dim1($inFilename_obs);
                        $distrib_exp = CsvDistrib::csv2distrib_dim1($inFilename_exp);
                        $title = 'Inter-aspects between ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$k]]) . ' (' . $study->config['dates'][0] . ')'
                             . ' and ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$l]]) . ' (' . $study->config['dates'][1] . ')';
                        $stats = $stats_obs[$dateName]['interaspects'][$code];
                        $svg = barCurve::svg(
                            data_bar:       $distrib_obs,
                            data_curve:     $distrib_exp,
                            title:          $title,
                            xlegends:       xlegend::angle360(),
                            ylegends:       ['min', 'max', 'mean'],
                            stats:          $stats,
                        );
                        $outFilename = $outDir . DS . $code . '.svg';   // ex: output/studies/death-fr/img/birth-death/interaspects/SO-SO.svg
                        file_put_contents::execute($outFilename, $svg, echoMessage: $echoMessage);
                    } // end loop on $l
                } // end loop on $k
            } // end loop on $j
        } // end loop on $i
        echo "Images generated for distributions of type distrib2\n";
    }
    
    /**
        Generates and stores SVG images of tables using dim2 structures.
    **/
    private static function generateDim2(IStudy $study, bool $echoMessage): void {
        
        $outDir_base = $study->getOutputImgDirectory();  // ex: output/studies/death-fr/img
        $inDir_base = $study->getObservedDirectory();   // ex: var/studies/death-fr/observed
        $nPlanets = count($study->config['planets']);
        
        $df = 129599; // degree of freedom = 360 * 360 - 1
        //
        // aspects
        //
        for($i=0; $i < count($study->config['dates']); $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
            $inDir_date = $inDir_base . DS . $dateName . DS . 'aspects' . DS . 'dim2';     // ex: var/studies/death-fr/observed/birth/aspects/dim2
            $outDir_date = $outDir_base . DS . $dateName . DS . 'aspects' . DS . 'dim2';   // ex: output/studies/death-fr/img/birth/aspects/dim2
            mkdir::execute($outDir_date);
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $code = $study->config['planets'][$j] . '-' . $study->config['planets'][$k];
                    $inFilename = $inDir_date . DS . $code . '.csv';     // ex: var/studies/death-fr/observed/birth/aspects/dim2/SO-SO.csv
                    $distrib = CsvDistrib::csv2distrib_dim2($inFilename);
                    $stats = chi2Table::compute($distrib);
                    $title = 'Positions of ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$j]]) . ' (' . $study->config['dates'][$i] . ')'
                         . ' and ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$k]]) . ' (' . $study->config['dates'][$i] . ')';
                    $pValue = chi2::chi2Proba($stats['chi2'], $df);
                    $img = table::image(
                        data: $stats['diff_percent'],
                        params: [
                            'title'     => $title,
                            'x-title'   => IAA::PLANET_NAMES[$study->config['planets'][$k]],
                            'x-legends' => xlegend::angle360(),
                            'y-title'   => IAA::PLANET_NAMES[$study->config['planets'][$j]],
                            'y-legends' => xlegend::angle360(),
                            'bottom-legend1' => 'chi2 = ' . round($stats['chi2'], 2),
                            'bottom-legend2' => 'p-value = ' . $pValue,
                        ],
                    );
                    $outFilename = $outDir_date . DS . $code . '.png';   // ex: output/studies/death-fr/img/birth-death/interaspects/dim2/SO-SO.png
                    imagepng($img, $outFilename);
                } // end loop on $k
            } // end loop on $j
        }
        //
        // interaspects
        //
        for($i=0; $i < count($study->config['dates']); $i++){
            for($j=$i+1; $j < count($study->config['dates']); $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j];        // ex: birth-death
                $inDir_date = $inDir_base . DS . $dateName . DS . 'interaspects' . DS . 'dim2';     // ex: var/studies/death-fr/observed/birth-death/interaspects/dim2
                $outDir_date = $outDir_base . DS . $dateName . DS . 'interaspects' . DS . 'dim2';   // ex: output/studies/death-fr/img/birth-death/interaspects/dim2
                mkdir::execute($outDir_date);
                for($k=0; $k < $nPlanets; $k++){
                    for($l=0; $l < $nPlanets; $l++){
                        $code = $study->config['planets'][$k] . '-' . $study->config['planets'][$l];
                        $inFilename = $inDir_date . DS . $code . '.csv';     // ex: var/studies/death-fr/observed/birth-death/interaspects/dim2/SO-SO.csv
                        $distrib = CsvDistrib::csv2distrib_dim2($inFilename);
                        $stats = chi2Table::compute($distrib);
                        $title = 'Positions of ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$k]]) . ' (' . $study->config['dates'][0] . ')'
                             . ' and ' . strtolower(IAA::PLANET_NAMES[$study->config['planets'][$l]]) . ' (' . $study->config['dates'][1] . ')';
                        $pValue = chi2::chi2Proba($stats['chi2'], $df);
                        $img = table::image(
                            data: $stats['diff_percent'],
                            params: [
                                'title'     => $title,
                                'x-title'   => ucFirst($study->config['dates'][$j]),
                                'x-legends' => xlegend::angle360(),
                                'y-title'   => ucFirst($study->config['dates'][$i]),
                                'y-legends' => xlegend::angle360(),
                                'bottom-legend1' => 'chi2 = ' . round($stats['chi2'], 2),
                                'bottom-legend2' => 'p-value = ' . $pValue,
                            ],
                        );
                        $outFilename = $outDir_date . DS . $code . '.png';   // ex: output/studies/death-fr/img/birth-death/interaspects/dim2/SO-SO.png
                        imagepng($img, $outFilename);
                    } // end loop on $l
                } // end loop on $k
            } // end loop on $j
        } // end loop on $i
        echo "Images generated for distributions of type dim2\n";
    }
    
} // end class
