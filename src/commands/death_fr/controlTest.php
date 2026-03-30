<?php
/******************************************************************************

    Functional test for src/command/death_fr/control.php
    All tests deal with study "death-fr".
    
    @pre splitTest and controlTest must have been executed before executing the tests of this class.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use PHPUnit\Framework\TestCase;
use observe\commands\tests\Death_fr_tests;
use observe\model\Observe;
use observe\model\Studies;
use observe\commands\death_fr\init;

class controlTest extends TestCase{
    
    private static array $studyConfig;

    public static function setUpBeforeClass(): void {
        self::$studyConfig = Death_fr_tests::loadStudy('study1/study1.yml');
        
        // initialize tmp database
        init::execute(self::$studyConfig, []);
        control::execute(self::$studyConfig, ['1-10']);
    }
    
    // TODO tests on tmp database
    
    /** 
        For all distributions, test that the sums of the frequencies in control distributions
        are equal to the sum of observed frequencies.
    **/
    public function testStudy1_sums(){
        $nDates = count(self::$studyConfig['dates']);
        
//        foreach(self::$studyConfig['splits'] as $split){
// currently, test done only on split 'full'
// because control computation for partial subgroups is not clearly analyzed.
        foreach(['full'] as $split){
            $splitDir = Studies::getSplitDirectory(self::$studyConfig, $split);
            $subgroups = Death_fr::getSplitDirnames($split);
            
            foreach($subgroups as $subgroup){
                $observedDir = Studies::getObservedDirectory(self::$studyConfig, $split, $subgroup);
                $controlDirs = glob(Studies::getControlsDirectory(self::$studyConfig, $split, $subgroup) . DS . '*');
                //
                // Distributions of type distrib1
                //
                for($i=0; $i < $nDates; $i++){
                    $dateName = self::$studyConfig['dates'][$i]; // ex: birth
                    // planets and aspects
                    foreach(['aspects', 'planets'] as $distribType){                                                               
                        $observedSubdir = implode(DS, [$observedDir, $dateName, $distribType]);
                        $observedFiles = glob($observedSubdir . DS . '*.csv');
                        foreach($observedFiles as $observedFile){
                            $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                            $observedSum = array_sum($observedDistrib);
                            $distribName = basename($observedFile, '.csv');
                            foreach($controlDirs as $controlDir){
                                $controlFile = implode(DS, [$controlDir, $dateName, $distribType, $distribName . '.csv']);
                                $controlDistrib = Death_fr_tests::readCsv($controlFile, Observe::CSV_SEP);
                                $controlSum = array_sum($controlDistrib);
                                $this->assertEquals($controlSum, $observedSum);
                            }
                        }
                    }
                    // day
                    $observedFile = implode(DS, [$observedDir, $dateName, 'day.csv']);
                    $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                    $observedSum = array_sum($observedDistrib);
                    foreach($controlDirs as $controlDir){
                        $controlFile = implode(DS, [$controlDir, $dateName, 'day.csv']);
                        $controlDistrib = Death_fr_tests::readCsv($controlFile, Observe::CSV_SEP);
                        $controlSum = array_sum($controlDistrib);
                        $this->assertEquals($controlSum, $observedSum);
                    }
                    // year
                    $observedFile = implode(DS, [$observedDir, $dateName, 'year.csv']);
                    $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                    $observedSum = array_sum($observedDistrib);
                    foreach($controlDirs as $controlDir){
                        $controlFile = implode(DS, [$controlDir, $dateName, 'year.csv']);
                        $controlDistrib = Death_fr_tests::readCsv($controlFile, Observe::CSV_SEP);
                        $controlSum = array_sum($controlDistrib);
                        $this->assertEquals($controlSum, $observedSum);
                    }
                }
                //
                // Distributions of type distrib2
                //
                for($i=0; $i < $nDates; $i++){
                    for($j=$i+1; $j < $nDates; $j++){
                        $dateName = self::$studyConfig['dates'][$i] . '-' . self::$studyConfig['dates'][$j]; // ex: birth-death
                        // interaspects
                        $observedSubdir = implode(DS, [$observedDir, $dateName, 'interaspects']);
                        $observedFiles = glob($observedSubdir . DS . '*.csv');
                        foreach($observedFiles as $observedFile){
                            $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                            $observedSum = array_sum($observedDistrib);
                            $distribName = basename($observedFile, '.csv'); // ex: VE-MA
                            foreach($controlDirs as $controlDir){
                                $controlFile = implode(DS, [$controlDir, $dateName, 'interaspects', $distribName . '.csv']);
                                $controlDistrib = Death_fr_tests::readCsv($controlFile, Observe::CSV_SEP);
                                $controlSum = array_sum($controlDistrib);
                                $this->assertEquals($controlSum, $observedSum);
                            }
                        }
                        // age
                        $observedFile = implode(DS, [$observedDir, $dateName, 'age.csv']);
                        $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                        $observedSum = array_sum($observedDistrib);
                        foreach($controlDirs as $controlDir){
                            $controlFile = implode(DS, [$controlDir, $dateName, 'age.csv']);
                            $controlDistrib = Death_fr_tests::readCsv($controlFile, Observe::CSV_SEP);
                            $controlSum = array_sum($controlDistrib);
                            $this->assertEquals($controlSum, $observedSum);
                        }
                    } // end loop on $j
                } // end loop on $i
            } // end loop on subgroups
        } // end loop on splits
    }
    
    /** 
        Test that distributions of birth days and years in controls are identical to the observed distributions.
        They must be equal because control::otherPerson() keeps the birth date of the original person.
    **/
    public function testStudy1_birth_date(){
        
        $observedDir = Studies::getObservedDirectory(self::$studyConfig, 'full', '01--0-200years');
        $controlDirs = glob(Studies::getControlsDirectory(self::$studyConfig, 'full', '01--0-200years') . DS . '*');
        
        $observed_days = Death_fr_tests::readCsv(implode(DS, [$observedDir, 'birth', 'day.csv']), Observe::CSV_SEP);
        $observed_years = Death_fr_tests::readCsv(implode(DS, [$observedDir, 'birth', 'year.csv']), Observe::CSV_SEP);
        
        foreach($controlDirs as $controlDir){
            // day
            $filename = $controlDir . DS . 'birth' . DS . 'day.csv';
            $controlValues = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
            $this->assertEquals($observed_days, $controlValues);
            // year
            $filename = $controlDir . DS . 'birth' . DS . 'year.csv';
            $controlValues = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
            $this->assertEquals($observed_years, $controlValues);
        }
    }
    
    
}// end class
