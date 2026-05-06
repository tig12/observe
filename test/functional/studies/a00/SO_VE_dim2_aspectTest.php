<?php
/******************************************************************************

    Test a distribution which looks suspicious.
    
    usage: phpunit test/studies/a00/SO_VE_dim2_aspectTest.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-06 20:52:18+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\test\functional\studies\death_fr;

use PHPUnit\Framework\TestCase;
use observe\model\Observe;

class SO_VE_dim2_aspectTest extends TestCase {
    
    private static array $distrib;
    
    private static int $nTotal = 321838; // nb of weddings in a0 study
    
    public static function setUpBeforeClass(): void {
        $file = 'var/studies/a00/observed/wedding/aspects/dim2/SO-VE.csv';
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        for($i=0; $i < count($lines); $i++){
            self::$distrib[$i] = explode(Observe::CSV_SEP, $lines[$i]);
        }
    }
    
    public function testGeneral(){
        $this->assertEquals(count(self::$distrib), 360);
        $this->assertEquals(count(self::$distrib[0]), 360);
    }
    
    public function testSum(){
        $sum = 0;
        foreach(self::$distrib as $line){
            $sum += array_sum($line);
        }
        $this->assertEquals(self::$nTotal, $sum);
    }
    
} // end class
