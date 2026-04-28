<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-19 16:57:11+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use PHPUnit\Framework\TestCase;
use observe\model\distrib\Distribs;
use observe\model\distrib\EmptyDistribs;

class DistribsTest extends TestCase{

    private const array DATE_NAMES = ['birth', 'death'];
    
    private const array PLANET_CODES = ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SA', 'UR', 'NE', 'PL', 'NN'];
    
    private const string DISTRIB_AGE_UNIT = 'M';
    
    public function testComputeDistributions(){
        
        /* 
            Function passed to Distribs::computeDistributions()
            Simulates the reading of the 2 first lines of var/studies/death-fr/data.csv.bz2:
            bzcat var/studies/death-fr/data.csv.bz2 | head -n 2
            1922-01-09;1970-12-10
            1969-03-29;1970-04-25
        */
        $f = function() {
            yield ['1922-01-09', '1970-12-10'];
            yield ['1969-03-29', '1970-04-25'];
        };
        //
        $arr360 = array_fill(0, 360, 0);
        $emptyPlanets = array_fill_keys(self::PLANET_CODES, $arr360);
        $emptyAspects = [];
        for($i=0; $i < count(self::PLANET_CODES); $i++){
            for($j=$i+1; $j < count(self::PLANET_CODES); $j++){
                $key = self::PLANET_CODES[$i] . '-' . self::PLANET_CODES[$j];
                $emptyAspects[$key] = $arr360;
            }
        }
        $emptyInteraspects = [];
        foreach(self::PLANET_CODES as $code1){
            foreach(self::PLANET_CODES as $code2){
                $emptyInteraspects["$code1-$code2"] = $arr360;
            }
        }
        $emptyDays = EmptyDistribs::emptyDayDistrib();
        //
        $expected = [
            'birth' => [
                'positions' => $emptyPlanets,
                'aspects' => ['dim1' => $emptyAspects],
                'year' => [],
                'day' => $emptyDays,
            ],
            'death' => [
                'positions' => $emptyPlanets,
                'aspects' => ['dim1' => $emptyAspects],
                'year' => [],
                'day' => $emptyDays,
            ],
            'birth-death' => [
                'interaspects' => ['dim1' => $emptyInteraspects],
                'age-dim1' => [],
            ],
        ];
        
/*
Based on execution using Swissephem computations
select * from planet where day in ('1922-01-09','1970-12-10','1969-03-29','1970-04-25');
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
|    day     |     SO      |     MO      |     ME      |     VE      |     MA      |     JU      |     SA      |     UR      |     NE      |     PL      |     NN      |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1922-01-09 | 288.4696949 | 54.1648325  | 296.3189889 | 281.0818103 | 218.1236324 | 197.963406  | 187.5517234 | 336.94814   | 135.2806304 | 98.8159356  | 193.2233008 |
| 1969-03-29 | 8.6220338   | 137.0039167 | 358.2871684 | 24.3486099  | 252.2483913 | 180.1770698 | 26.0277003  | 181.6819333 | 238.4889072 | 173.38721   | 359.9802851 |
| 1970-04-25 | 34.8622467  | 262.7866333 | 52.0804289  | 57.122513   | 64.5989507  | 210.6034009 | 41.167102   | 185.5886505 | 240.1862056 | 175.0946797 | 339.2237309 |
| 1970-12-10 | 258.0208572 | 48.2657102  | 278.7094937 | 221.4945587 | 212.4186924 | 233.2577048 | 46.979153   | 192.8695529 | 241.2587457 | 179.5631401 | 327.0982989 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
*/
        //
        // birth
        //
        $expected['birth']['positions']['SO'][288] = 1;
        $expected['birth']['positions']['SO'][8] = 1;
        $expected['birth']['positions']['MO'][54] = 1;
        $expected['birth']['positions']['MO'][137] = 1;
        $expected['birth']['positions']['ME'][296] = 1;
        $expected['birth']['positions']['ME'][358] = 1;
        $expected['birth']['positions']['VE'][281] = 1;
        $expected['birth']['positions']['VE'][24] = 1;
        $expected['birth']['positions']['MA'][218] = 1;
        $expected['birth']['positions']['MA'][252] = 1;
        $expected['birth']['positions']['JU'][197] = 1;
        $expected['birth']['positions']['JU'][180] = 1;
        $expected['birth']['positions']['SA'][187] = 1;
        $expected['birth']['positions']['SA'][26] = 1;
        $expected['birth']['positions']['UR'][336] = 1;
        $expected['birth']['positions']['UR'][181] = 1;
        $expected['birth']['positions']['NE'][135] = 1;
        $expected['birth']['positions']['NE'][238] = 1;
        $expected['birth']['positions']['PL'][98] = 1;
        $expected['birth']['positions']['PL'][173] = 1;
        $expected['birth']['positions']['NN'][193] = 1;
        $expected['birth']['positions']['NN'][359] = 1;
        //
        $expected['birth']['aspects']['dim1']['SO-MO'][125] = 1;
        $expected['birth']['aspects']['dim1']['SO-MO'][128] = 1;
        $expected['birth']['aspects']['dim1']['SO-ME'][7] = 1;
        $expected['birth']['aspects']['dim1']['SO-ME'][349] = 1;
        // $expected['birth']['aspects']['dim1']['SO-VE'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-VE'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['SO-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['MO-ME'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-ME'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-VE'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-VE'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['MO-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['ME-VE'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-VE'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['ME-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['VE-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-MA'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['VE-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['MA-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-JU'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['MA-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['JU-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-SA'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['JU-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['SA-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-UR'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['SA-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['UR-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['UR-NE'][] = 1;
        // $expected['birth']['aspects']['dim1']['UR-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['UR-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['UR-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['UR-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['dim1']['NE-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['NE-PL'][] = 1;
        // $expected['birth']['aspects']['dim1']['NE-NN'][] = 1;
        // $expected['birth']['aspects']['dim1']['NE-NN'][] = 1;
        //
        $expected['birth']['aspects']['dim1']['PL-NN'][94] = 1;
        $expected['birth']['aspects']['dim1']['PL-NN'][186] = 1;
        //
        $expected['birth']['year']['1922'] = 1;
        $expected['birth']['year']['1969'] = 1;
        //
        $expected['birth']['day']['01-09'] = 1;
        $expected['birth']['day']['03-29'] = 1;
        //
        // death
        //
        $expected['death']['positions']['SO'][258] = 1;
        $expected['death']['positions']['SO'][34] = 1;
        $expected['death']['positions']['MO'][48] = 1;
        $expected['death']['positions']['MO'][262] = 1;
        $expected['death']['positions']['ME'][278] = 1;
        $expected['death']['positions']['ME'][52] = 1;
        $expected['death']['positions']['VE'][221] = 1;
        $expected['death']['positions']['VE'][57] = 1;
        $expected['death']['positions']['MA'][212] = 1;
        $expected['death']['positions']['MA'][64] = 1;
        $expected['death']['positions']['JU'][233] = 1;
        $expected['death']['positions']['JU'][210] = 1;
        $expected['death']['positions']['SA'][46] = 1;
        $expected['death']['positions']['SA'][41] = 1;
        $expected['death']['positions']['UR'][192] = 1;
        $expected['death']['positions']['UR'][185] = 1;
        $expected['death']['positions']['NE'][241] = 1;
        $expected['death']['positions']['NE'][240] = 1;
        $expected['death']['positions']['PL'][179] = 1;
        $expected['death']['positions']['PL'][175] = 1;
        $expected['death']['positions']['NN'][327] = 1;
        $expected['death']['positions']['NN'][339] = 1;
        //
        $expected['death']['aspects']['dim1']['SO-MO'][150] = 1;
        $expected['death']['aspects']['dim1']['SO-MO'][227] = 1;
        $expected['death']['aspects']['dim1']['SO-ME'][20] = 1;
        $expected['death']['aspects']['dim1']['SO-ME'][17] = 1;
        // $expected['death']['aspects']['dim1']['SO-VE'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-VE'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['SO-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['MO-ME'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-ME'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-VE'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-VE'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['MO-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['ME-VE'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-VE'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['ME-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['VE-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-MA'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['VE-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['MA-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-JU'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['MA-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['JU-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-SA'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['JU-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['SA-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-UR'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['SA-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['UR-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['UR-NE'][] = 1;
        // $expected['death']['aspects']['dim1']['UR-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['UR-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['UR-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['UR-NN'][] = 1;
        // //
        // $expected['death']['aspects']['dim1']['NE-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['NE-PL'][] = 1;
        // $expected['death']['aspects']['dim1']['NE-NN'][] = 1;
        // $expected['death']['aspects']['dim1']['NE-NN'][] = 1;
        //
        $expected['death']['aspects']['dim1']['PL-NN'][147] = 1;
        $expected['death']['aspects']['dim1']['PL-NN'][164] = 1;
        //
        $expected['death']['year']['1970'] = 2;
        //
        $expected['death']['day']['12-10'] = 1;
        $expected['death']['day']['04-25'] = 1;
        //
        // birth-death
        //
        $expected['birth-death']['interaspects']['dim1']['SO-SO'][329] = 1;
        $expected['birth-death']['interaspects']['dim1']['SO-SO'][26] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SO-NN'][] = 1;
        //
        // $expected['birth-death']['interaspects']['dim1']['MO-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MO-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['ME-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['ME-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['VE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['VE-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['MA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['MA-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['JU-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['JU-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['SA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['SA-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['UR-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['UR-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['NE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NE-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['PL-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-NN'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['PL-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['dim1']['NN-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-SO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-MO'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-ME'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-VE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-MA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-JU'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-SA'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-UR'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-NE'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-PL'][] = 1;
        // $expected['birth-death']['interaspects']['dim1']['NN-PL'][] = 1;
        $expected['birth-death']['interaspects']['dim1']['NN-NN'][133] = 1;
        $expected['birth-death']['interaspects']['dim1']['NN-NN'][339] = 1;
        //
        $expected['birth-death']['age-dim1'][587] = 1;
        $expected['birth-death']['age-dim1'][13] = 1;
        
        $computed = Distribs::computeDistributions($f, self::DATE_NAMES, self::PLANET_CODES, self::DISTRIB_AGE_UNIT);
//print_r($computed['birth']['aspects']['dim1']['SO-MO']); exit;
        
        $this->assertEquals($computed['birth']['positions'], $expected['birth']['positions']);                                                       
        $this->assertEquals($computed['birth']['aspects']['dim1']['SO-MO'], $expected['birth']['aspects']['dim1']['SO-MO']);
        $this->assertEquals($computed['birth']['aspects']['dim1']['SO-ME'], $expected['birth']['aspects']['dim1']['SO-ME']);
        $this->assertEquals($computed['birth']['aspects']['dim1']['PL-NN'], $expected['birth']['aspects']['dim1']['PL-NN']);
        $this->assertEquals($computed['birth']['year'], $expected['birth']['year']);
        $this->assertEquals($computed['birth']['day'], $expected['birth']['day']);
        //
        $this->assertEquals($computed['death']['positions'], $expected['death']['positions']);
        $this->assertEquals($computed['death']['aspects']['dim1']['SO-MO'], $expected['death']['aspects']['dim1']['SO-MO']);
        $this->assertEquals($computed['death']['aspects']['dim1']['SO-ME'], $expected['death']['aspects']['dim1']['SO-ME']);
        $this->assertEquals($computed['death']['aspects']['dim1']['PL-NN'], $expected['death']['aspects']['dim1']['PL-NN']);
        $this->assertEquals($computed['death']['year'], $expected['death']['year']);
        $this->assertEquals($computed['death']['day'], $expected['death']['day']);
        //
        $this->assertEquals($computed['birth-death']['interaspects']['dim1']['SO-SO'], $expected['birth-death']['interaspects']['dim1']['SO-SO']);
        $this->assertEquals($computed['birth-death']['interaspects']['dim1']['NN-NN'], $expected['birth-death']['interaspects']['dim1']['NN-NN']);
        $this->assertEquals($computed['birth-death']['age-dim1'], $expected['birth-death']['age-dim1']);
    }
    
}// end class
