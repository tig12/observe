<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-02-27 14:43:46+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\stats;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class chi2Test extends TestCase{
    
    public static function wantedProvider(): array
    {
        return [
            // From https://www.statisticshowto.com/probability-and-statistics/chi-square/
            // 2026-02-27 14:52:46+01:00
            [
                /*distrib_obs*/     [29, 24, 22, 19, 21, 18, 19, 20, 23, 18, 20, 23],
                /*distrib_exp*/     array_fill(0, 12, 21.333),
                /*chi2_wanted*/     5.094,
                /*chi2_round*/      3,
                /*p_value_wanted*/  0.9265,
                /*p_value_round*/   3,
            ],
            // From https://www.statology.org/chi-square-goodness-of-fit-test/
            // 2026-04-01 09:45:27+01:00
            [
                /*distrib_obs*/     [50, 60, 40, 47, 53],
                /*distrib_exp*/     [50, 50, 50, 50, 50],
                /*chi2_wanted*/     4.36,
                /*chi2_round*/      2,
                /*p_value_wanted*/  0.359472,
                /*p_value_round*/   3,
            ],
        ];
    }

    /** 
        Tests that the chi2 = 0 when observed and expected distributions are equal.
    **/
    public function test_chi2_zero(){
        $obs = $exp = [29, 24, 22, 19, 21, 18, 19, 20, 23, 18, 20, 23];
        $this->assertEquals(0, chi2::chi2($obs, $exp));
    }
    
    #[DataProvider('wantedProvider')]
    public function test_chi2_proba(
        array   $distrib_obs,
        array   $distrib_exp,
        float   $chi2_wanted,
        int     $chi2_round,
        float   $p_value_wanted,
        int     $p_value_round,
    ): void{
        $df = count($distrib_obs) - 1;
        [$chi2_got, $p_value_got] = chi2::chi2AndProba($df, $distrib_obs, $distrib_exp);
        $this->assertEquals(round($chi2_wanted, $chi2_round), round($chi2_got, $chi2_round));
        $this->assertEquals(round($p_value_wanted, $p_value_round), round($p_value_got, $p_value_round));
    }
    
} // end class
