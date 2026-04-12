<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-11 22:34:35+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\stats;

use PHPUnit\Framework\TestCase;


class distribTest extends TestCase{

    const DISTRIBS = [
            // From book "Calculs d'incertitudes" de Mathieu Rouaud, p 1
            // sum = 5100+4230+3750+4560+3980 = 21620
            [
                'data' => [ 5100, 4230, 3750, 4560, 3980 ],
                'mean' => 4324,
                'sigma' => 530,
                'sigma-round' => -1,
            ],
            // Same as previous, in an associative array
            [
                'data' => [ 'a' => 5100, 'b' => 4230, 'c' => 3750, 'd' => 4560, 'e' => 3980 ],
                'mean' => 4324,
                'sigma' => 530,
                'sigma-round' => -1,
            ],
            // From book "Calculs d'incertitudes" de Mathieu Rouaud, p 4
            [
                'data' => [ 11, 9, 10, 14, 11, 8, 9, 12, 7, 8, 8, 9, 11, 14, 10, 9 ],
                'mean' => 10,
                'sigma' => 2.07,
            ],
            // From book "Calculs d'incertitudes" de Mathieu Rouaud, p 5
            [
                'data' => [ 15, 13, 12, 13, 14, 13, 16, 19, 13, 14, 10, 16, 14, 15, 13, 14 ],
                'mean' => 14,
                'sigma' => 2.00,
            ],
            // From book "Calculs d'incertitudes" de Mathieu Rouaud, p 5
            [
                'data' => [ 10, 10, 12, 11, 9, 8, 10, 9, 9, 11, 9, 11, 10, 10, 11, 10 ],
                'mean' => 10,
                'sigma' => 1.03,
            ],
        ];

    /** 
        Tests that the chi2 = 0 when observed and expected distributions are equal.
    **/
    public function test_sigma(){
        foreach(self::DISTRIBS as $distrib){
            $round = $distrib['sigma-round'] ?? 2;
            $this->assertEquals(
                round(distrib::sigma($distrib['data']), $round),
                $distrib['sigma']
            );
        }
    }
    
    /** 
        Tests that the chi2 = 0 when observed and expected distributions are equal.
    **/
    public function test_mean(){
        foreach(self::DISTRIBS as $distrib){
            $this->assertEquals(
                distrib::mean($distrib['data']),
                $distrib['mean']
            );
        }
    }
    
} // end class
