<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-03 12:35:30+02:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Studies;
use observe\model\PlanetCode;

class PlanetCodeTest extends TestCase {

    public function testPairCode(){
        $values = ['SO', 'MO', 'ME', 'JU'];
        $this->assertEquals('SO-MO', PlanetCode::pairCode('MO', 'SO', $values));
        $this->assertEquals('SO-MO', PlanetCode::pairCode('SO', 'MO', $values));
        $this->assertEquals('SO-JU', PlanetCode::pairCode('SO', 'JU', $values));
        $this->assertEquals('SO-JU', PlanetCode::pairCode('JU', 'SO', $values));
        $this->assertEquals('MO-ME', PlanetCode::pairCode('MO', 'ME', $values));
        $this->assertEquals('MO-ME', PlanetCode::pairCode('ME', 'MO', $values));
    }
    
} // end class
