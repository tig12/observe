<?php
/******************************************************************************
    
    Tests that dim1 and dim2 distributions are coherent, for aspects and interaspects.
    
    The distributions of death-fr study must have been computed before executing this test.
    
    Same as testSums() in test/functional/commands/dim2Test.php, but tests the result of real computations.
    
    usage: phpunit test/verif/death_fr/dim2Test.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-10 21:13:11+02:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use observe\studies\death_fr\Death_fr;

class dim2Test extends TestCase {
    
    private static IStudy $study;
    
    public static function setUpBeforeClass(): void {
        self::$study = new Death_fr('death-fr');
    }
    
    /** 
        Checks that the sums of dim1 and dim2 distributions are equal.
    **/
    public function test_sums(){
        $observedDir = self::$study->getObservedDirectory();
        $nDates = count(self::$study->config['dates']);
        $nPlanets = count(self::$study->config['planets']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$study->config['dates'][$i]; // ex: birth
            //aspects
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$study->config['planets'][$j] . '-' . self::$study->config['planets'][$k]; // ex: MA-NE
                    $dim1_file = implode(DS, [$observedDir, $dateName, 'aspects', 'dim1', $key . '.csv']);
                    $dim2_file = implode(DS, [$observedDir, $dateName, 'aspects', 'dim2', $key . '.csv']);
                    $dim1_distrib = CsvDistrib::csv2distrib_dim1($dim1_file, Observe::CSV_SEP);
                    $dim2_distrib = CsvDistrib::csv2distrib_dim2($dim2_file, Observe::CSV_SEP);
                    $dim1_sum = round(array_sum($dim1_distrib));
                    $dim2_sum = 0;
                    foreach($dim2_distrib as $row){
                        $dim2_sum += array_sum($row);
                    }
                    $dim2_sum = round($dim2_sum);
                    $this->assertEquals($dim1_sum, $dim2_sum);
                }
            }
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
                foreach(self::$study->config['planets'] as $planet1){
                    foreach(self::$study->config['planets'] as $planet2){
                        $dim1_file = implode(DS, [$observedDir, $dateName, 'interaspects', 'dim1', $key . '.csv']);
                        $dim2_file = implode(DS, [$observedDir, $dateName, 'interaspects', 'dim2', $key . '.csv']);
                        $dim1_distrib = CsvDistrib::csv2distrib_dim1($dim1_file, Observe::CSV_SEP);
                        $dim2_distrib = CsvDistrib::csv2distrib_dim2($dim2_file, Observe::CSV_SEP);
                        $dim1_sum = round(array_sum($dim1_distrib));
                        $dim2_sum = 0;
                        foreach($dim2_distrib as $row){
                            $dim2_sum += array_sum($row);
                        }
                        $dim2_sum = round($dim2_sum);
                        $this->assertEquals($dim1_sum, $dim2_sum);
                    }
                }
            } // end loop on $j
        } // end loop on $i
    }
    
} // end class
