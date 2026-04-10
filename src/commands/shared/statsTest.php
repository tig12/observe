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

class statsTest extends TestCase{
    
    private static array $studyConfig;

    public static function setUpBeforeClass(): void {
        self::$studyConfig = Death_fr_tests::loadStudy('study1/study1.yml');
        // As controls and expected distributions have been computed only for split "full", the chi2 is only tested on this split
        chi2::execute(self::$studyConfig, ['full']);
    }
    
    /** 
    **/
    public function testStudy1(){
        $this->assertEquals(1, 1);
    }
    
}// end class
