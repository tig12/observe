<?php
/******************************************************************************
    
    Functional test for src/command/expected.php

    Uses study1 - see config/test/study1-README 
    
    @pre        This test needs that steps init, import, observed and control are performed:
                php run-observe.php study1 init
                phpunit test/functional/studies/death_fr/importTest.php
                phpunit test/functional/commands/observedTest.php
                phpunit test/functional/studies/death_fr/controlTest.php
                or
                php run-observe.php study1 init
                php run-observe.php study1 import
                php run-observe.php study1 observed
                php run-observe.php study1 control 1-3   # or other quantities
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-29 18:38:42+01:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use observe\studies\death_fr\Death_fr;
//use observe\commands\import;
//use observe\commands\observed;
//use observe\studies\death_fr\control;
use observe\commands\expected;

class expectedTest extends TestCase{
    
    private static IStudy $study;

    public static function setUpBeforeClass(): void {
        self::$study = new Death_fr('study1');
        
        // uncomment next lines to include previous steps in this test
        // import::execute(self::$study, []);
        // observed::execute(self::$study, []);
        // control::execute(self::$study, ['1-3']);
        
        expected::execute(self::$study, []);
    }
    
    /** 
        Test the existence of the directories and files.
    **/
    public function test_files(){
        $observedDir = self::$study->getObservedDirectory();
        $expectedDir = self::$study->getExpectedDirectory();
        $this->assertTrue(is_dir($expectedDir));
        
        $nDates = count(self::$study->config['dates']);
        $nPlanets = count(self::$study->config['planets']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$study->config['dates'][$i]; // ex: birth
            // planets
            $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'positions'])));
            foreach(self::$study->config['planets'] as $planet){
                $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'positions', $planet . '.csv'])));
            }
            //aspects
            $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'aspects'])));
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$study->config['planets'][$j] . '-' . self::$study->config['planets'][$k]; // ex: MA-NE
                    $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'aspects', 'dim1', $key . '.csv'])));
                }
            }
            // day
            $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'day.csv'])));
            // year
            $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'year.csv'])));
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = self::$study->config['dates'][$i];
                $dateName2 = self::$study->config['dates'][$j];
                $dateName = "$dateName1-$dateName2"; // ex: birth-death
                // interaspects
                $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'interaspects', 'dim1'])));
                foreach(self::$study->config['planets'] as $planet1){
                    foreach(self::$study->config['planets'] as $planet2){
                        $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'interaspects', 'dim1', "$planet1-$planet2.csv"])));
                    }
                }
                // age
                $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'age', 'dim1', 'age-M.csv'])));
                $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'age', 'dim1', 'age-Y.csv'])));
            } // end loop on $j
        } // end loop on $i
    }
    
    /** 
        Checks that the sums of expected and observed distributions are equal.
    **/
    public function test_sums(){
        $observedDir = self::$study->getObservedDirectory();
        $expectedDir = self::$study->getExpectedDirectory();
        
        $nDates = count(self::$study->config['dates']);
        $nPlanets = count(self::$study->config['planets']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$study->config['dates'][$i]; // ex: birth
            // planets
            foreach(self::$study->config['planets'] as $planet){
                $expectedFile = implode(DS, [$expectedDir, $dateName, 'positions', $planet . '.csv']);
                $observedFile = implode(DS, [$observedDir, $dateName, 'positions', $planet . '.csv']);
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
                $observedSum = round(array_sum($observedDistrib));
                $expectedSum = round(array_sum($expectedDistrib));
                $this->assertEquals($observedSum, $expectedSum);
            }
            //aspects
            $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'aspects', 'dim1'])));
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$study->config['planets'][$j] . '-' . self::$study->config['planets'][$k]; // ex: MA-NE
                    $expectedFile = implode(DS, [$expectedDir, $dateName, 'aspects', 'dim1', $key . '.csv']);
                    $observedFile = implode(DS, [$observedDir, $dateName, 'aspects', 'dim1', $key . '.csv']);
                    $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                    $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
                    $observedSum = round(array_sum($observedDistrib));
                    $expectedSum = round(array_sum($expectedDistrib));
                    $this->assertEquals($observedSum, $expectedSum);
                }
            }
            // day
            $expectedFile = implode(DS, [$expectedDir, $dateName, 'day.csv']);
            $observedFile = implode(DS, [$observedDir, $dateName, 'day.csv']);
            $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
            $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
            $observedSum = round(array_sum($observedDistrib));
            $expectedSum = round(array_sum($expectedDistrib));
            $this->assertEquals($observedSum, $expectedSum);
            // year
            $expectedFile = implode(DS, [$expectedDir, $dateName, 'year.csv']);
            $observedFile = implode(DS, [$observedDir, $dateName, 'year.csv']);
            $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
            $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
            $observedSum = round(array_sum($observedDistrib));
            $expectedSum = round(array_sum($expectedDistrib));
            $this->assertEquals($observedSum, $expectedSum);
        } // end loop on $i
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = self::$study->config['dates'][$i];
                $dateName2 = self::$study->config['dates'][$j];
                $dateName = "$dateName1-$dateName2"; // ex: birth-death
                // interaspects
                $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'interaspects', 'dim1'])));
                foreach(self::$study->config['planets'] as $planet1){
                    foreach(self::$study->config['planets'] as $planet2){
                        $expectedFile = implode(DS, [$expectedDir, $dateName, 'interaspects', 'dim1', "$planet1-$planet2.csv"]);
                        $observedFile = implode(DS, [$observedDir, $dateName, 'interaspects', 'dim1', "$planet1-$planet2.csv"]);
                        $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                        $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
                        $observedSum = round(array_sum($observedDistrib));
                        $expectedSum = round(array_sum($expectedDistrib));
                        $this->assertEquals($observedSum, $expectedSum);
                    }
                }
                // age M
                $expectedFile = implode(DS, [$expectedDir, $dateName, 'age', 'dim1', 'age-M.csv']);
                $observedFile = implode(DS, [$observedDir, $dateName, 'age', 'dim1', 'age-M.csv']);
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
                $observedSum = round(array_sum($observedDistrib));
                $expectedSum = round(array_sum($expectedDistrib));
                $this->assertEquals($observedSum, $expectedSum);
                // age Y
                $expectedFile = implode(DS, [$expectedDir, $dateName, 'age', 'dim1', 'age-Y.csv']);
                $observedFile = implode(DS, [$observedDir, $dateName, 'age', 'dim1', 'age-Y.csv']);
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $expectedDistrib = CsvDistrib::csv2distrib_dim1($expectedFile, Observe::CSV_SEP, 'float');
                $observedSum = round(array_sum($observedDistrib));
                $expectedSum = round(array_sum($expectedDistrib));
                $this->assertEquals($observedSum, $expectedSum);
            } // end loop on $j
        } // end loop on $i
    }
    
}// end class
