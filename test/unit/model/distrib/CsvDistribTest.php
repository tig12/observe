<?php
/******************************************************************************

    @todo       Test also distrib2csv ?
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.

    @history    2026-04-14 12:33:26+01:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Studies;
use observe\model\distrib\CsvDistrib;

class CsvDistribTest extends TestCase{

    const string WANTED_DIM1_REGULAR = <<<WANTED
0;100
1;200
2;250

WANTED;

    const string WANTED_DIM1_ASSOCIATIVE = <<<WANTED
a;100
b;200
c;250

WANTED;

    const string WANTED_DIM2 = <<<WANTED
1;2;3;4
11;12;13;14
21;22;23;24
31;32;33;34

WANTED;
    
    public function test_distrib2csv_dim1_regular(){
        $a = [
            0 => 100,
            1 => 200,
            2 => 250,
        ];
        $this->assertEquals(self::WANTED_DIM1_REGULAR, CsvDistrib::distrib2csv_dim1($a));
    }
    
    public function test_distrib2csv_dim1_associative(){
        $a = [
            'a' => 100,
            'b' => 200,
            'c' => 250,
        ];
        $this->assertEquals(self::WANTED_DIM1_ASSOCIATIVE, CsvDistrib::distrib2csv_dim1($a));
    }
    
    public function test_distrib2csv_dim2_regular(){
        $a = [
            0 => [1, 2, 3, 4],
            1 => [11, 12, 13, 14],
            2 => [21, 22, 23, 24],
            3 => [31, 32, 33, 34],
        ];
        $this->assertEquals(self::WANTED_DIM2, CsvDistrib::distrib2csv_dim2($a));
    }
    
    public function test_distrib2csv_dim2_associative(){
        $a = [
            'a' => ['a0' => 1, 'b' => 2, 3, 4],
            'b' => ['a1' => 11, 'b' => 12, 13, 14],
            'c' => ['a2' => 21, 'b' => 22, 23, 24],
            'd' => ['a3' => 31, 'b' => 32, 33, 34],
        ];
        $this->assertEquals(self::WANTED_DIM2, CsvDistrib::distrib2csv_dim2($a));
    }
    
}// end class
