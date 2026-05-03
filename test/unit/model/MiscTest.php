<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-03 12:35:30+02:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Studies;
use observe\model\Misc;

class MiscTest extends TestCase {

    public function testPairCode(){
        $values = ['SO', 'MO', 'ME', 'JU'];
        $this->assertEquals('SO-MO', Misc::pairCode('MO', 'SO', $values));
        $this->assertEquals('SO-MO', Misc::pairCode('SO', 'MO', $values));
        $this->assertEquals('SO-JU', Misc::pairCode('SO', 'JU', $values));
        $this->assertEquals('SO-JU', Misc::pairCode('JU', 'SO', $values));
        $this->assertEquals('MO-ME', Misc::pairCode('MO', 'ME', $values));
        $this->assertEquals('MO-ME', Misc::pairCode('ME', 'MO', $values));
    }
    
}// end class
