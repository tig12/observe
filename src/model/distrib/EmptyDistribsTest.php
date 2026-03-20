<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-19 09:49:18+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use PHPUnit\Framework\TestCase;
use observe\model\Studies;
use observe\model\distrib\EmptyDistribs;

class EmptyDistribsTest extends TestCase{

    /** Study used for the tests **/
    private const string STUDY_SLUG = 'death-fr';
    
    private static array $studyConfig;
    
    protected function setUp(): void {
        self::$studyConfig = Studies::getStudyConfig(self::STUDY_SLUG);
    }
    
    // ***********************************************************************************
    // 1 - Test generic functions    
    // ***********************************************************************************
    
    public function testEmptySingleDistrib(){
        $codes = ['a', 'b', 'c'];
        $N = 3;
        $this->assertEquals(EmptyDistribs::emptySingleDistrib($codes, $N), [
                'a' => [0, 0, 0],
                'b' => [0, 0, 0],
                'c' => [0, 0, 0],
            ]
        );
    }
    
    public function testEmptyDoubleDistrib_square(){
        $codes1 = ['a', 'b', 'c'];
        $codes2 = ['x', 'y', 'z'];
        $N = 3;
        $this->assertEquals(EmptyDistribs::emptyDoubleDistrib_square($codes1, $codes2 , $N), [
                'a-x' => [0, 0, 0],
                'a-y' => [0, 0, 0],
                'a-z' => [0, 0, 0],
                'b-x' => [0, 0, 0],
                'b-y' => [0, 0, 0],
                'b-z' => [0, 0, 0],
                'c-x' => [0, 0, 0],
                'c-y' => [0, 0, 0],
                'c-z' => [0, 0, 0],
            ]
        );
    }
    
    public function testEmptyDoubleDistrib_triangle(){
        $codes1 = ['a', 'b', 'c'];
        $codes2 = ['x', 'y', 'z'];
        $N = 3;
        $this->assertEquals(EmptyDistribs::emptyDoubleDistrib_triangle($codes1, $codes2 , $N), [
                'a-y' => [0, 0, 0],
                'a-z' => [0, 0, 0],
                'b-z' => [0, 0, 0],
            ]
        );
    }
    
    // ***********************************************************************************
    // 2 - Test functions aware of study structure    
    // ***********************************************************************************
    
    public function testEmptyDistrib1(){
        $arr360 = array_fill(0, 360, 0);
        $this->assertEquals(EmptyDistribs::emptyDistrib1(self::$studyConfig), [
            'planets'=> [
                'SO' => $arr360,
                'MO' => $arr360,
                'ME' => $arr360,
                'VE' => $arr360,
                'MA' => $arr360,
                'JU' => $arr360,
                'SA' => $arr360,
                'UR' => $arr360,
                'NE' => $arr360,
                'PL' => $arr360,
                'NN' => $arr360,
            ],
            'aspects' => [
                'SO-MO' => $arr360,
                'SO-ME' => $arr360,
                'SO-VE' => $arr360,
                'SO-MA' => $arr360,
                'SO-JU' => $arr360,
                'SO-SA' => $arr360,
                'SO-UR' => $arr360,
                'SO-NE' => $arr360,
                'SO-PL' => $arr360,
                'SO-NN' => $arr360,
                'MO-ME' => $arr360,
                'MO-VE' => $arr360,
                'MO-MA' => $arr360,
                'MO-JU' => $arr360,
                'MO-SA' => $arr360,
                'MO-UR' => $arr360,
                'MO-NE' => $arr360,
                'MO-PL' => $arr360,
                'MO-NN' => $arr360,
                'ME-VE' => $arr360,
                'ME-MA' => $arr360,
                'ME-JU' => $arr360,
                'ME-SA' => $arr360,
                'ME-UR' => $arr360,
                'ME-NE' => $arr360,
                'ME-PL' => $arr360,
                'ME-NN' => $arr360,
                'VE-MA' => $arr360,
                'VE-JU' => $arr360,
                'VE-SA' => $arr360,
                'VE-UR' => $arr360,
                'VE-NE' => $arr360,
                'VE-PL' => $arr360,
                'VE-NN' => $arr360,
                'MA-JU' => $arr360,
                'MA-SA' => $arr360,
                'MA-UR' => $arr360,
                'MA-NE' => $arr360,
                'MA-PL' => $arr360,
                'MA-NN' => $arr360,
                'JU-SA' => $arr360,
                'JU-UR' => $arr360,
                'JU-NE' => $arr360,
                'JU-PL' => $arr360,
                'JU-NN' => $arr360,
                'SA-UR' => $arr360,
                'SA-NE' => $arr360,
                'SA-PL' => $arr360,
                'SA-NN' => $arr360,
                'UR-NE' => $arr360,
                'UR-PL' => $arr360,
                'UR-NN' => $arr360,
                'NE-PL' => $arr360,
                'NE-NN' => $arr360,
                'PL-NN' => $arr360,
            ],
            'day' => EmptyDistribs::emptyDayDistrib(),
            'year' => [],
        ]);
    }
    
    public function testEmptyDistrib2(){
        $arr360 = array_fill(0, 360, 0);
        $this->assertEquals(EmptyDistribs::emptyDistrib2(self::$studyConfig), [
            'interaspects' => [
                'SO-SO' => $arr360,
                'SO-MO' => $arr360,
                'SO-ME' => $arr360,
                'SO-VE' => $arr360,
                'SO-MA' => $arr360,
                'SO-JU' => $arr360,
                'SO-SA' => $arr360,
                'SO-UR' => $arr360,
                'SO-NE' => $arr360,
                'SO-PL' => $arr360,
                'SO-NN' => $arr360,
                //
                'MO-SO' => $arr360,
                'MO-MO' => $arr360,
                'MO-ME' => $arr360,
                'MO-VE' => $arr360,
                'MO-MA' => $arr360,
                'MO-JU' => $arr360,
                'MO-SA' => $arr360,
                'MO-UR' => $arr360,
                'MO-NE' => $arr360,
                'MO-PL' => $arr360,
                'MO-NN' => $arr360,
                //
                'ME-SO' => $arr360,
                'ME-MO' => $arr360,
                'ME-ME' => $arr360,
                'ME-VE' => $arr360,
                'ME-MA' => $arr360,
                'ME-JU' => $arr360,
                'ME-SA' => $arr360,
                'ME-UR' => $arr360,
                'ME-NE' => $arr360,
                'ME-PL' => $arr360,
                'ME-NN' => $arr360,
                //
                'VE-SO' => $arr360,
                'VE-MO' => $arr360,
                'VE-ME' => $arr360,
                'VE-VE' => $arr360,
                'VE-MA' => $arr360,
                'VE-JU' => $arr360,
                'VE-SA' => $arr360,
                'VE-UR' => $arr360,
                'VE-NE' => $arr360,
                'VE-PL' => $arr360,
                'VE-NN' => $arr360,
                //
                'MA-SO' => $arr360,
                'MA-MO' => $arr360,
                'MA-ME' => $arr360,
                'MA-VE' => $arr360,
                'MA-MA' => $arr360,
                'MA-JU' => $arr360,
                'MA-SA' => $arr360,
                'MA-UR' => $arr360,
                'MA-NE' => $arr360,
                'MA-PL' => $arr360,
                'MA-NN' => $arr360,
                //
                'JU-SO' => $arr360,
                'JU-MO' => $arr360,
                'JU-ME' => $arr360,
                'JU-VE' => $arr360,
                'JU-MA' => $arr360,
                'JU-JU' => $arr360,
                'JU-SA' => $arr360,
                'JU-UR' => $arr360,
                'JU-NE' => $arr360,
                'JU-PL' => $arr360,
                'JU-NN' => $arr360,
                //
                'SA-SO' => $arr360,
                'SA-MO' => $arr360,
                'SA-ME' => $arr360,
                'SA-VE' => $arr360,
                'SA-MA' => $arr360,
                'SA-JU' => $arr360,
                'SA-SA' => $arr360,
                'SA-UR' => $arr360,
                'SA-NE' => $arr360,
                'SA-PL' => $arr360,
                'SA-NN' => $arr360,
                //
                'UR-SO' => $arr360,
                'UR-MO' => $arr360,
                'UR-ME' => $arr360,
                'UR-VE' => $arr360,
                'UR-MA' => $arr360,
                'UR-JU' => $arr360,
                'UR-SA' => $arr360,
                'UR-UR' => $arr360,
                'UR-NE' => $arr360,
                'UR-PL' => $arr360,
                'UR-NN' => $arr360,
                //
                'NE-SO' => $arr360,
                'NE-MO' => $arr360,
                'NE-ME' => $arr360,
                'NE-VE' => $arr360,
                'NE-MA' => $arr360,
                'NE-JU' => $arr360,
                'NE-SA' => $arr360,
                'NE-UR' => $arr360,
                'NE-NE' => $arr360,
                'NE-PL' => $arr360,
                'NE-NN' => $arr360,
                //
                'PL-SO' => $arr360,
                'PL-MO' => $arr360,
                'PL-ME' => $arr360,
                'PL-VE' => $arr360,
                'PL-MA' => $arr360,
                'PL-JU' => $arr360,
                'PL-SA' => $arr360,
                'PL-UR' => $arr360,
                'PL-NE' => $arr360,
                'PL-PL' => $arr360,
                'PL-NN' => $arr360,
                //
                'NN-SO' => $arr360,
                'NN-MO' => $arr360,
                'NN-ME' => $arr360,
                'NN-VE' => $arr360,
                'NN-MA' => $arr360,
                'NN-JU' => $arr360,
                'NN-SA' => $arr360,
                'NN-UR' => $arr360,
                'NN-NE' => $arr360,
                'NN-PL' => $arr360,
                'NN-NN' => $arr360,
            ],
            'age' => [],
        ]);
    }
}// end class
