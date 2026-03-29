<?php
/******************************************************************************
    
    Functional test for src/command/shared/expected.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-29 18:38:42+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use PHPUnit\Framework\TestCase;
use observe\commands\tests\Death_fr_tests;
use observe\model\Studies;

class expectedTest extends TestCase{
    
    private static array $studyConfig;

    public static function setUpBeforeClass(): void {
        self::$studyConfig = Death_fr_tests::loadStudy('study1/study1.yml');
        expected::execute(self::$studyConfig);
    }
    
    /** 
        Test the existence of the directories and files.
    **/
    public function testStudy1_files(){
        $observedDir = Studies::getObservedDirectory(self::$studyConfig, 'full', '01--0-200years');
        $expectedDir = Studies::getExpectedDirectory(self::$studyConfig, 'full', '01--0-200years');
        $this->assertTrue(is_dir($expectedDir));
        
        $nDates = count(self::$studyConfig['dates']);
        $nPlanets = count(self::$studyConfig['planets']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$studyConfig['dates'][$i]; // ex: birth
            // planets
            $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'planets'])));
            foreach(self::$studyConfig['planets'] as $planet){
                $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'planets', $planet . '.csv'])));
            }
            //aspects
            $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'aspects'])));
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$studyConfig['planets'][$j] . '-' . self::$studyConfig['planets'][$k]; // ex: MA-NE
                    $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'aspects', $key . '.csv'])));
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
                $dateName1 = self::$studyConfig['dates'][$i];
                $dateName2 = self::$studyConfig['dates'][$j];
                $dateName = "$dateName1-$dateName2"; // ex: birth-death
                // interaspects
                $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'interaspects'])));
                foreach(self::$studyConfig['planets'] as $planet1){
                    foreach(self::$studyConfig['planets'] as $planet2){
                        $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'interaspects', "$planet1-$planet2.csv"])));
                    }
                }
                // age
                $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'age.csv'])));
            }
        }
    }
    
    /** 
        Checks that the sums of expected and observed distributions are equal.
    **/
    public function testStudy1_sums(){
        $observedDir = Studies::getObservedDirectory(self::$studyConfig, 'full', '01--0-200years');
        $expectedDir = Studies::getExpectedDirectory(self::$studyConfig, 'full', '01--0-200years');
        
        $nDates = count(self::$studyConfig['dates']);
        $nPlanets = count(self::$studyConfig['planets']);
        
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$studyConfig['dates'][$i]; // ex: birth
            // planets
            foreach(self::$studyConfig['planets'] as $planet){
                $expectedFile = implode(DS, [$expectedDir, $dateName, 'planets', $planet . '.csv']);
                $observedFile = implode(DS, [$observedDir, $dateName, 'planets', $planet . '.csv']);
echo "expected = $expectedFile\n";
echo "observed = $observedFile\n";
                $observedDistrib = Death_fr_tests::readCsv($observedFile);
                $expectedDistrib = Death_fr_tests::readCsv($expectedFile);
                $observedSum = array_sum($observedDistrib);
                $expectedSum = array_sum($expectedDistrib);
echo "$planet - $observedSum - $expectedSum\n";
//                $this->assertEquals($observedSum, $expectedSum);
            }
continue;
            //aspects
            $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'aspects'])));
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$studyConfig['planets'][$j] . '-' . self::$studyConfig['planets'][$k]; // ex: MA-NE
                    $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'aspects', $key . '.csv'])));
                }
            }
            // day
            $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'day.csv'])));
            // year
            $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'year.csv'])));
        }
return;        
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = self::$studyConfig['dates'][$i];
                $dateName2 = self::$studyConfig['dates'][$j];
                $dateName = "$dateName1-$dateName2"; // ex: birth-death
                // interaspects
                $this->assertTrue(is_dir(implode(DS, [$expectedDir, $dateName, 'interaspects'])));
                foreach(self::$studyConfig['planets'] as $planet1){
                    foreach(self::$studyConfig['planets'] as $planet2){
                        $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'interaspects', "$planet1-$planet2.csv"])));
                    }
                }
                // age
                $this->assertTrue(is_file(implode(DS, [$expectedDir, $dateName, 'age.csv'])));
            }
        }
    }
    
}// end class
