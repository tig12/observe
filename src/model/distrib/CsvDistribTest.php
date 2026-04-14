<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-14 12:33:26+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use PHPUnit\Framework\TestCase;
use observe\model\Studies;
use observe\model\distrib\EmptyDistribs;

class CsvDistribTest extends TestCase{

    const string WANTED1 = <<<WANTED
1;2;3;4
11;12;13;14
21;22;23;24
31;32;33;34

WANTED;
    
    public function test_distrib2csv2dim_regular(){
        $a = [
            0 => [1, 2, 3, 4],
            1 => [11, 12, 13, 14],
            2 => [21, 22, 23, 24],
            3 => [31, 32, 33, 34],
        ];
        $this->assertEquals(self::WANTED1, CsvDistrib::distrib2csv2dim($a));
    }
    
    public function test_distrib2csv2dim_associative(){
        // mix of associative and regular
        $a = [
            'a' => ['a0' => 1, 'b' => 2, 3, 4],
            'b' => ['a1' => 11, 'b' => 12, 13, 14],
            'c' => ['a2' => 21, 'b' => 22, 23, 24],
            'd' => ['a3' => 31, 'b' => 32, 33, 34],
        ];
        $this->assertEquals(self::WANTED1, CsvDistrib::distrib2csv2dim($a));
    }
    
}// end class
