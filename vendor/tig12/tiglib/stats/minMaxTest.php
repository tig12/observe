<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-12 07:41:40+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\stats;

use PHPUnit\Framework\TestCase;


class minMaxTest extends TestCase{

    const TESTS = [
        [
            'distrib' => [
                'a' => 12,
                'b' => 14,
                'c' => 10,
                'd' => 18,
                'e' => 12,
            ],
            'from'      => 'a',
            'to'        => 'e',
            'min_key'   => 'c',
            'min'       => 10,
            'max_key'   => 'd',
            'max'       => 18,
        ],
        // all values equal
        [
            'distrib' => [
                'a' => 10,
                'b' => 10,
                'c' => 10,
                'd' => 10,
                'e' => 10,
            ],
            'from'      => 'a',
            'to'        => 'e',
            'min_key'   => 'a',
            'min'       => 10,
            'max_key'   => 'a',
            'max'       => 10,
        ],
        // all values equal only for min
        [
            'distrib' => [
                'a' => 10,
                'b' => 10,
                'c' => 10,
                'd' => 11,
                'e' => 10,
            ],
            'from'      => 'a',
            'to'        => 'e',
            'min_key'   => 'a',
            'min'       => 10,
            'max_key'   => 'd',
            'max'       => 11,
        ],
        // keys not sorted
        [
            'distrib' => [
                1935 => 3784,
                1920 => 2258,
                1895 => 3687,
                1900 => 3500,
            ],
            'from'      => 1895,
            'to'        => 1935,
            'min_key'   => 1920,
            'min'       => 2258,
            'max_key'   => 1935,
            'max'       => 3784,
        ],
    ];
    
    public function test_indicators(){
        foreach(self::TESTS as $test){
            [$from, $to, $min_key, $min, $max_key, $max] = minMax::minMaxIndicators($test['distrib']);
            $this->assertEquals($from,      $test['from']);
            $this->assertEquals($to,        $test['to']);
            $this->assertEquals($min_key,   $test['min_key']);
            $this->assertEquals($min,       $test['min']);
            $this->assertEquals($max_key,   $test['max_key']);
            $this->assertEquals($max,       $test['max']);
        }
    }
    
} // end class
