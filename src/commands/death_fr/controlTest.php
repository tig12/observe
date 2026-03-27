<?php
/******************************************************************************

    Functional test for src/command/death_fr/control.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\Studies;

class controlTest extends TestCase{
    
    private static array $studyConfig;

    public static function setUpBeforeClass(): void {
        require_once implode(DS, [dirname(__DIR__), 'test-files', 'death_fr_tests.php']);
        self::$studyConfig = load_death_fr_study('study1/study1.yml');
    }
    
    public function testStudy1_full(){
        
        control::execute($studyConfig, [1-10]);
        
        $controlsDir = $studyConfig['working-dir'] . DS . 'controls' . DS . 'control-001';
    }
    
}// end class
