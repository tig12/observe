<?php
/******************************************************************************

    For all distributions, test that the sums of the frequencies in control distributions
    are equal to the sum of observed frequencies.
    
    The distributions of death-fr study must have been computed before executing this test.
    
    Same as testSums() in test/functional/studies/death_fr/controlTest.php, but tests the result of real computations.
    
    usage: phpunit test/verif/death_fr/controlTest.php
    
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-10 23:05:44+02:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use observe\studies\death_fr\Death_fr;

class controlTest extends TestCase{
    
    private static IStudy $study;
    
    public static function setUpBeforeClass(): void {
        self::$study = new Death_fr('death-fr');
    }
    
    public function test_sums(){
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
            $observedSubdir = implode(DS, [$observedDir, $dateName, 'aspects', 'dim1']);
            $observedFiles = glob($observedSubdir . DS . '*.csv');
            foreach($observedFiles as $observedFile){
                $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                $observedSum = array_sum($observedDistrib);
                $distribName = basename($observedFile, '.csv'); // ex: "SO-MO"
                foreach($controlDirs as $controlDir){
                    $controlFile = implode(DS, [$controlDir, $dateName, 'aspects', 'dim1',  $distribName . '.csv']);
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
                $observedSubdir = implode(DS, [$observedDir, $dateName, 'interaspects', 'dim1']);
                $observedFiles = glob($observedSubdir . DS . '*.csv');
                foreach($observedFiles as $observedFile){
                    $observedDistrib = CsvDistrib::csv2distrib_dim1($observedFile, Observe::CSV_SEP);
                    $observedSum = array_sum($observedDistrib);
                    $distribName = basename($observedFile, '.csv'); // ex: VE-MA
                    foreach($controlDirs as $controlDir){
                        $controlFile = implode(DS, [$controlDir, $dateName, 'interaspects', 'dim1', $distribName . '.csv']);
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
    
} // end class
