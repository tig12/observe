<?php
/******************************************************************************

    Functional test for src/studies/death_fr/control.php
    
    Uses study1 - see config/test/study1-README 
    
    @pre        This test needs that steps init, import and observed are performed:
                php run-observe.php study1 init
                phpunit test/functional/studies/death_fr/importTest.php
                phpunit test/functional/commands/observedTest.php
                or
                php run-observe.php study1 init
                php run-observe.php study1 import
                php run-observe.php study1 observed
    
    @todo       tests on tmp database
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use tiglib\filesystem\rrmdir;
use observe\studies\death_fr\Death_fr;
use observe\studies\death_fr\init;
//use observe\commands\observed;
use observe\studies\death_fr\control;

class controlTest extends TestCase{
    
    private static IStudy $study;
    
    public static function setUpBeforeClass(): void {
        
        self::$study = new Death_fr('study1');
        
        // Step init must always be done to flush tmp.sqlite3
        // because used by control to load the initial distributions => modifies array_sum() and breaks the test.
        // rm var/studies/study1/tmp.sqlite3 to avoid answering y/n during init
        // WARNING: dangerous if config/test/study1.yml is not correctly set
        $file = self::$study->config['sqlite-tmp'];
        if(is_file($file)){
            unlink($file);
        }
        init::execute(self::$study, []);

        // uncomment next line to include previous step in this test
        // observed::execute(self::$study, []);
        
        // rm -fr var/studies/study1/controls to avoid answering y/n during control creation
        // WARNING: dangerous if config/test/study1.yml is not correctly set
        if(is_dir(self::$study->getControlsDirectory())){
            rrmdir::execute(self::$study->getControlsDirectory());
        }
        control::execute(self::$study, ['1-3']);
    }
    
    /** 
        For all distributions, test that the sums of the frequencies in control distributions
        are equal to the sum of observed frequencies.
    **/
    public function testStudy1_sums(){
        $nDates = count(self::$study->config['dates']);
        $observedDir = self::$study->getObservedDirectory();
        $controlDirs = self::$study->getControlSubdirectories();
        //
        // Distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$study->config['dates'][$i]; // ex: birth
            // planet positions
            $observedSubdir = implode(DS, [$observedDir, $dateName, 'positions']);
            $observedFiles = glob($observedSubdir . DS . '*.csv');
            foreach($observedFiles as $observedFile){
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $observedSum = array_sum($observedDistrib);
                $distribName = basename($observedFile, '.csv'); // ex: "SO"
                foreach($controlDirs as $controlDir){
                    $controlFile = implode(DS, [$controlDir, $dateName, 'positions', $distribName . '.csv']);
                    $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                    $controlSum = array_sum($controlDistrib);
                    $this->assertEquals($controlSum, $observedSum);
                }
            }
            // aspects
            $observedSubdir = implode(DS, [$observedDir, $dateName, 'aspects']);
            $observedFiles = glob($observedSubdir . DS . '*.csv');
            foreach($observedFiles as $observedFile){
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $observedSum = array_sum($observedDistrib);
                $distribName = basename($observedFile, '.csv'); // ex: "SO-MO"
                foreach($controlDirs as $controlDir){
                    $controlFile = implode(DS, [$controlDir, $dateName, 'positions', $distribName . '.csv']);
                    $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                    $controlSum = array_sum($controlDistrib);
                    $this->assertEquals($controlSum, $observedSum);
                }
            }
            // day
            $observedFile = implode(DS, [$observedDir, $dateName, 'day.csv']);
            $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
            $observedSum = array_sum($observedDistrib);
            foreach($controlDirs as $controlDir){
                $controlFile = implode(DS, [$controlDir, $dateName, 'day.csv']);
                $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                $controlSum = array_sum($controlDistrib);
                $this->assertEquals($controlSum, $observedSum);
            }
            // year
            $observedFile = implode(DS, [$observedDir, $dateName, 'year.csv']);
            $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
            $observedSum = array_sum($observedDistrib);
            foreach($controlDirs as $controlDir){
                $controlFile = implode(DS, [$controlDir, $dateName, 'year.csv']);
                $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                $controlSum = array_sum($controlDistrib);
                $this->assertEquals($controlSum, $observedSum);
            }
        }
        //
        // Distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = self::$study->config['dates'][$i] . '-' . self::$study->config['dates'][$j]; // ex: birth-death
                // interaspects
                $observedSubdir = implode(DS, [$observedDir, $dateName, 'interaspects']);
                $observedFiles = glob($observedSubdir . DS . '*.csv');
                foreach($observedFiles as $observedFile){
                    $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                    $observedSum = array_sum($observedDistrib);
                    $distribName = basename($observedFile, '.csv'); // ex: VE-MA
                    foreach($controlDirs as $controlDir){
                        $controlFile = implode(DS, [$controlDir, $dateName, 'interaspects', $distribName . '.csv']);
                        $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                        $controlSum = array_sum($controlDistrib);
                        $this->assertEquals($controlSum, $observedSum);
                    }
                }
                // age M
                $observedFile = implode(DS, [$observedDir, $dateName, 'age', 'dim1', 'age-M.csv']);
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $observedSum = array_sum($observedDistrib);
                foreach($controlDirs as $controlDir){
                    $controlFile = implode(DS, [$controlDir, $dateName, 'age', 'dim1', 'age-M.csv']);
                    $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                    $controlSum = array_sum($controlDistrib);
                    $this->assertEquals($controlSum, $observedSum);
                }
                // age Y
                $observedFile = implode(DS, [$observedDir, $dateName, 'age', 'dim1', 'age-Y.csv']);
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $observedSum = array_sum($observedDistrib);
                foreach($controlDirs as $controlDir){
                    $controlFile = implode(DS, [$controlDir, $dateName, 'age', 'dim1', 'age-Y.csv']);
                    $controlDistrib = CsvDistrib::csv2distrib_dim1($controlFile, Observe::CSV_SEP);
                    $controlSum = array_sum($controlDistrib);
                    $this->assertEquals($controlSum, $observedSum);
                }
            } // end loop on $j
        } // end loop on $i
    }
    
    /** 
        Test that distributions of birth days and years in controls are identical to the observed distributions.
        They must be equal because control::otherPerson() keeps the birth date of the original person.
    **/
    public function testStudy1_birth_date(){
        $observedDir = self::$study->getObservedDirectory();
        $controlDirs = self::$study->getControlSubdirectories();
        $observed_days = CsvDistrib::csv2distrib_dim1(implode(DS, [$observedDir, 'birth', 'day.csv']), Observe::CSV_SEP);
        $observed_years = CsvDistrib::csv2distrib_dim1(implode(DS, [$observedDir, 'birth', 'year.csv']), Observe::CSV_SEP);
        foreach($controlDirs as $controlDir){
            // day
            $filename = $controlDir . DS . 'birth' . DS . 'day.csv';
            $controlValues = CsvDistrib::csv2distrib_dim1($filename, Observe::CSV_SEP);
            $this->assertEquals($observed_days, $controlValues);
            // year
            $filename = $controlDir . DS . 'birth' . DS . 'year.csv';
            $controlValues = CsvDistrib::csv2distrib_dim1($filename, Observe::CSV_SEP);
            $this->assertEquals($observed_years, $controlValues);
        }
    }
    
    
} // end class
