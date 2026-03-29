<?php
/******************************************************************************

    Functional test for src/command/death_fr/control.php
    All tests deal with study "death-fr" and split "full".
    
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
return;
        init::execute(self::$studyConfig, []);
        control::execute(self::$studyConfig, ['1-10']);
    }
    
    /** 
        Test the existence of the directories and files.
    **/
    public function testStudy1_files(){
    
        $wanted = [
            'src/commands/test-files/var/study1/controls/control-001',
            'src/commands/test-files/var/study1/controls/control-002',
            'src/commands/test-files/var/study1/controls/control-003',
            'src/commands/test-files/var/study1/controls/control-004',
            'src/commands/test-files/var/study1/controls/control-005',
            'src/commands/test-files/var/study1/controls/control-006',
            'src/commands/test-files/var/study1/controls/control-007',
            'src/commands/test-files/var/study1/controls/control-008',
            'src/commands/test-files/var/study1/controls/control-009',
            'src/commands/test-files/var/study1/controls/control-010',
        ];
        $controlDirs = self::$studyConfig['working-dir'] . DS . 'controls';
        $dirs_computed = glob($controlDirs . DS . '*');
        
        $this->assertEquals($dirs_computed, $wanted);
        
        $wanted_subdirs = [
            'birth/aspects',
            'birth/planets',
            'death/aspects',
            'death/planets',
            'birth-death/interaspects',
        ];
        foreach($dirs_computed as $controlDir){
            foreach($wanted_subdirs as $subdir){
                $this->assertTrue(is_dir($controlDir . DS . $subdir));
                foreach(self::$studyConfig['planets'] as $planet){
                    
                    
                    
                    $filename = $controlDir . DS . 'birth' . DS . 'planets' . DS . $planet . '.csv';
                    
                    
                    
                    $this->assertTrue(is_file($filename));
                }
            }
        }
    }
    
    /** 
        Test that distributions of birth days and years in controls are identical to the observed distributions.
        They must be equal because control::otherPerson() keeps the birth date of the original person.
    **/
    public function testStudy1_birth_date(){
        
        $observedDir = Studies::getObservedDirectory(self::$studyConfig, 'full', '01--0-200years');
        $controlDirs = glob(Studies::getControlsDirectory(self::$studyConfig, 'full', '01--0-200years') . DS . '*');
        
        $observed_days = Death_fr_tests::readCsv(implode(DS, [$observedDir, 'birth', 'day.csv']));
        $observed_years = Death_fr_tests::readCsv(implode(DS, [$observedDir, 'birth', 'year.csv']));
        
        foreach($controlDirs as $controlDir){
            // day
            $filename = $controlDir . DS . 'birth' . DS . 'day.csv';
            $controlValues = Death_fr_tests::readCsv($filename);
            $this->assertEquals($observed_days, $controlValues);
            // year
            $filename = $controlDir . DS . 'birth' . DS . 'year.csv';
            $controlValues = Death_fr_tests::readCsv($filename);
            $this->assertEquals($observed_years, $controlValues);
        }
    }
    
    /** 
        For all distributions, test that the sums of the frequencies in control distributions are equal to the sum of observed frequencies.
    **/
    public function testStudy1_sums(){
        
        $observedDir = Studies::getObservedDirectory(self::$studyConfig, 'full', '01--0-200years');
        $controlDirs = glob(Studies::getControlsDirectory(self::$studyConfig, 'full', '01--0-200years') . DS . '*');
        
        $nDates = count(self::$studyConfig['dates']);
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
                    $observedDistrib = Death_fr_tests::readCsv($observedFile);
                    $observedSum = array_sum($observedDistrib);
                    $distribName = basename($observedFile, '.csv');
                    foreach($controlDirs as $controlDir){
                        $controlFile = implode(DS, [$controlDir, $dateName, $distribType, $distribName . '.csv']);
                        $controlDistrib = Death_fr_tests::readCsv($controlFile);
                        $controlSum = array_sum($controlDistrib);
                        $this->assertEquals($controlSum, $observedSum);
                    }
                }
            }
            // day
            $observedFile = implode(DS, [$observedDir, $dateName, 'day.csv']);
            $observedDistrib = Death_fr_tests::readCsv($observedFile);
            $observedSum = array_sum($observedDistrib);
            foreach($controlDirs as $controlDir){
                $controlFile = implode(DS, [$controlDir, $dateName, 'day.csv']);
                $controlDistrib = Death_fr_tests::readCsv($controlFile);
                $controlSum = array_sum($controlDistrib);
                $this->assertEquals($controlSum, $observedSum);
            }
            // day
            $observedFile = implode(DS, [$observedDir, $dateName, 'year.csv']);
            $observedDistrib = Death_fr_tests::readCsv($observedFile);
            $observedSum = array_sum($observedDistrib);
            foreach($controlDirs as $controlDir){
                $controlFile = implode(DS, [$controlDir, $dateName, 'year.csv']);
                $controlDistrib = Death_fr_tests::readCsv($controlFile);
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
                    $observedDistrib = Death_fr_tests::readCsv($observedFile);
                    $observedSum = array_sum($observedDistrib);
                    $distribName = basename($observedFile, '.csv');
                    foreach($controlDirs as $controlDir){
                        $controlFile = implode(DS, [$controlDir, $dateName, 'interaspects', $distribName . '.csv']);
                        $controlDistrib = Death_fr_tests::readCsv($controlFile);
                        $controlSum = array_sum($controlDistrib);
                        $this->assertEquals($controlSum, $observedSum);
                    }
                }
                // age
                $observedFile = implode(DS, [$observedDir, $dateName, 'age.csv']);
                $observedDistrib = Death_fr_tests::readCsv($observedFile);
                $observedSum = array_sum($observedDistrib);
                foreach($controlDirs as $controlDir){
                    $controlFile = implode(DS, [$controlDir, $dateName, 'age.csv']);
                    $controlDistrib = Death_fr_tests::readCsv($controlFile);
                    $controlSum = array_sum($controlDistrib);
                    $this->assertEquals($controlSum, $observedSum);
                }
            } // end loop on $j
        } // end loop on $i
        
        
    }
    
}// end class
