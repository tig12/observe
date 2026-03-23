<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-19 16:57:11+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use PHPUnit\Framework\TestCase;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use observe\model\distrib\EmptyDistribs;

class DistribsTest extends TestCase{

    /** Study used for the tests **/
    private const string STUDY_SLUG = 'death-fr';
    
    private static array $studyConfig;
    
    protected function setUp(): void {
        self::$studyConfig = Studies::getStudyConfig(self::STUDY_SLUG);
    }
    
    public function testComputeDistributions(){
        $baseOutdir = Studies::getSplitDirectory(self::$studyConfig, 'all');
        $filename = 'compress.bzip2://' . $baseOutdir . DS . '01--0-150years' . DS . 'data.csv.bz2';
        $f = function() use ($filename) {
            if (!$fileHandle = fopen($filename, 'r')) {
                return false;
            }
            $i = 0;
            while ($i < 2 && false !== $line = fgets($fileHandle)) {
                yield $line;
                $i++;
            }
            fclose($fileHandle);
        };
        //
        $arr360 = array_fill(0, 360, 0);
        $emptyPlanets = array_fill_keys(self::$studyConfig['planets'], $arr360);
        $emptyAspects = [];
        for($i=0; $i < count(self::$studyConfig['planets']); $i++){
            for($j=$i+1; $j < count(self::$studyConfig['planets']); $j++){
                $key = self::$studyConfig['planets'][$i] . '-' . self::$studyConfig['planets'][$j];
                $emptyAspects[$key] = $arr360;
            }
        }
        $emptyInteraspects = [];
        foreach(self::$studyConfig['planets'] as $code1){
            foreach(self::$studyConfig['planets'] as $code2){
                $emptyInteraspects["$code1-$code2"] = $arr360;
            }
        }
        $emptyDays = EmptyDistribs::emptyDayDistrib();
        //
        $expected = [
            'birth' => [
                'planets' => $emptyPlanets,
                'aspects' => $emptyAspects,
                'year' => [],
                'day' => $emptyDays,
            ],
            'death' => [
                'planets' => $emptyPlanets,
                'aspects' => $emptyAspects,
                'year' => [],
                'day' => $emptyDays,
            ],
            'birth-death' => [
                'interaspects' => $emptyInteraspects,
                'age' => [],
            ],
        ];
        
/*
Based on execution using Meeus1 computations
========= line 1 =========               ========= line 2 =========
dates                                    dates
    [0] => 1922-01-09                        [0] => 1969-03-29
    [1] => 1970-12-10                        [1] => 1970-04-25
planets                                  planets
    [0] => Array(                            [0] => Array
            [SO] => 288.474                          [SO] => 8.628
            [MO] => 54.159                           [MO] => 136.996
            [ME] => 296.33                           [ME] => 358.301
            [VE] => 281.092                          [VE] => 24.35
            [MA] => 218.128                          [MA] => 252.248
            [JU] => 197.967                          [JU] => 180.166
            [SA] => 187.553                          [SA] => 26.032
            [UR] => 336.961                          [UR] => 181.682
            [NE] => 135.269                          [NE] => 238.498
            [PL] => 98.752                           [PL] => 172.836
            [NN] => 193.222                          [NN] => 359.98
    [1] => Array(                            [1] => Array
            [SO] => 258.022                          [SO] => 34.866
            [MO] => 48.251                           [MO] => 262.776
            [ME] => 278.71                           [ME] => 52.08
            [VE] => 221.489                          [VE] => 57.131
            [MA] => 212.423                          [MA] => 64.607
            [JU] => 233.261                          [JU] => 210.596
            [SA] => 46.969                           [SA] => 41.168
            [UR] => 192.86                           [UR] => 185.576
            [NE] => 241.266                          [NE] => 240.188
            [PL] => 181.436                          [PL] => 173.753
            [NN] => 327.095                          [NN] => 339.222
*/
        //
        // birth
        //
        $expected['birth']['planets']['SO'][288] = 1;
        $expected['birth']['planets']['SO'][8] = 1;
        $expected['birth']['planets']['MO'][54] = 1;
        $expected['birth']['planets']['MO'][136] = 1;
        $expected['birth']['planets']['ME'][296] = 1;
        $expected['birth']['planets']['ME'][358] = 1;
        $expected['birth']['planets']['VE'][281] = 1;
        $expected['birth']['planets']['VE'][24] = 1;
        $expected['birth']['planets']['MA'][218] = 1;
        $expected['birth']['planets']['MA'][252] = 1;
        $expected['birth']['planets']['JU'][197] = 1;
        $expected['birth']['planets']['JU'][180] = 1;
        $expected['birth']['planets']['SA'][187] = 1;
        $expected['birth']['planets']['SA'][26] = 1;
        $expected['birth']['planets']['UR'][336] = 1;
        $expected['birth']['planets']['UR'][181] = 1;
        $expected['birth']['planets']['NE'][135] = 1;
        $expected['birth']['planets']['NE'][238] = 1;
        $expected['birth']['planets']['PL'][98] = 1;
        $expected['birth']['planets']['PL'][172] = 1;
        $expected['birth']['planets']['NN'][193] = 1;
        $expected['birth']['planets']['NN'][359] = 1;
        //
        $expected['birth']['aspects']['SO-MO'][125] = 1;
        $expected['birth']['aspects']['SO-MO'][128] = 1;
        $expected['birth']['aspects']['SO-ME'][7] = 1;
        $expected['birth']['aspects']['SO-ME'][349] = 1;
        // $expected['birth']['aspects']['SO-VE'][] = 1;
        // $expected['birth']['aspects']['SO-VE'][] = 1;
        // $expected['birth']['aspects']['SO-MA'][] = 1;
        // $expected['birth']['aspects']['SO-MA'][] = 1;
        // $expected['birth']['aspects']['SO-JU'][] = 1;
        // $expected['birth']['aspects']['SO-JU'][] = 1;
        // $expected['birth']['aspects']['SO-SA'][] = 1;
        // $expected['birth']['aspects']['SO-SA'][] = 1;
        // $expected['birth']['aspects']['SO-UR'][] = 1;
        // $expected['birth']['aspects']['SO-UR'][] = 1;
        // $expected['birth']['aspects']['SO-NE'][] = 1;
        // $expected['birth']['aspects']['SO-NE'][] = 1;
        // $expected['birth']['aspects']['SO-PL'][] = 1;
        // $expected['birth']['aspects']['SO-PL'][] = 1;
        // $expected['birth']['aspects']['SO-NN'][] = 1;
        // $expected['birth']['aspects']['SO-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['MO-ME'][] = 1;
        // $expected['birth']['aspects']['MO-ME'][] = 1;
        // $expected['birth']['aspects']['MO-VE'][] = 1;
        // $expected['birth']['aspects']['MO-VE'][] = 1;
        // $expected['birth']['aspects']['MO-MA'][] = 1;
        // $expected['birth']['aspects']['MO-MA'][] = 1;
        // $expected['birth']['aspects']['MO-JU'][] = 1;
        // $expected['birth']['aspects']['MO-JU'][] = 1;
        // $expected['birth']['aspects']['MO-SA'][] = 1;
        // $expected['birth']['aspects']['MO-SA'][] = 1;
        // $expected['birth']['aspects']['MO-UR'][] = 1;
        // $expected['birth']['aspects']['MO-UR'][] = 1;
        // $expected['birth']['aspects']['MO-NE'][] = 1;
        // $expected['birth']['aspects']['MO-NE'][] = 1;
        // $expected['birth']['aspects']['MO-PL'][] = 1;
        // $expected['birth']['aspects']['MO-PL'][] = 1;
        // $expected['birth']['aspects']['MO-NN'][] = 1;
        // $expected['birth']['aspects']['MO-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['ME-VE'][] = 1;
        // $expected['birth']['aspects']['ME-VE'][] = 1;
        // $expected['birth']['aspects']['ME-MA'][] = 1;
        // $expected['birth']['aspects']['ME-MA'][] = 1;
        // $expected['birth']['aspects']['ME-JU'][] = 1;
        // $expected['birth']['aspects']['ME-JU'][] = 1;
        // $expected['birth']['aspects']['ME-SA'][] = 1;
        // $expected['birth']['aspects']['ME-SA'][] = 1;
        // $expected['birth']['aspects']['ME-UR'][] = 1;
        // $expected['birth']['aspects']['ME-UR'][] = 1;
        // $expected['birth']['aspects']['ME-NE'][] = 1;
        // $expected['birth']['aspects']['ME-NE'][] = 1;
        // $expected['birth']['aspects']['ME-PL'][] = 1;
        // $expected['birth']['aspects']['ME-PL'][] = 1;
        // $expected['birth']['aspects']['ME-NN'][] = 1;
        // $expected['birth']['aspects']['ME-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['VE-MA'][] = 1;
        // $expected['birth']['aspects']['VE-MA'][] = 1;
        // $expected['birth']['aspects']['VE-JU'][] = 1;
        // $expected['birth']['aspects']['VE-JU'][] = 1;
        // $expected['birth']['aspects']['VE-SA'][] = 1;
        // $expected['birth']['aspects']['VE-SA'][] = 1;
        // $expected['birth']['aspects']['VE-UR'][] = 1;
        // $expected['birth']['aspects']['VE-UR'][] = 1;
        // $expected['birth']['aspects']['VE-NE'][] = 1;
        // $expected['birth']['aspects']['VE-NE'][] = 1;
        // $expected['birth']['aspects']['VE-PL'][] = 1;
        // $expected['birth']['aspects']['VE-PL'][] = 1;
        // $expected['birth']['aspects']['VE-NN'][] = 1;
        // $expected['birth']['aspects']['VE-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['MA-JU'][] = 1;
        // $expected['birth']['aspects']['MA-JU'][] = 1;
        // $expected['birth']['aspects']['MA-SA'][] = 1;
        // $expected['birth']['aspects']['MA-SA'][] = 1;
        // $expected['birth']['aspects']['MA-UR'][] = 1;
        // $expected['birth']['aspects']['MA-UR'][] = 1;
        // $expected['birth']['aspects']['MA-NE'][] = 1;
        // $expected['birth']['aspects']['MA-NE'][] = 1;
        // $expected['birth']['aspects']['MA-PL'][] = 1;
        // $expected['birth']['aspects']['MA-PL'][] = 1;
        // $expected['birth']['aspects']['MA-NN'][] = 1;
        // $expected['birth']['aspects']['MA-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['JU-SA'][] = 1;
        // $expected['birth']['aspects']['JU-SA'][] = 1;
        // $expected['birth']['aspects']['JU-UR'][] = 1;
        // $expected['birth']['aspects']['JU-UR'][] = 1;
        // $expected['birth']['aspects']['JU-NE'][] = 1;
        // $expected['birth']['aspects']['JU-NE'][] = 1;
        // $expected['birth']['aspects']['JU-PL'][] = 1;
        // $expected['birth']['aspects']['JU-PL'][] = 1;
        // $expected['birth']['aspects']['JU-NN'][] = 1;
        // $expected['birth']['aspects']['JU-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['SA-UR'][] = 1;
        // $expected['birth']['aspects']['SA-UR'][] = 1;
        // $expected['birth']['aspects']['SA-NE'][] = 1;
        // $expected['birth']['aspects']['SA-NE'][] = 1;
        // $expected['birth']['aspects']['SA-PL'][] = 1;
        // $expected['birth']['aspects']['SA-PL'][] = 1;
        // $expected['birth']['aspects']['SA-NN'][] = 1;
        // $expected['birth']['aspects']['SA-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['UR-NE'][] = 1;
        // $expected['birth']['aspects']['UR-NE'][] = 1;
        // $expected['birth']['aspects']['UR-PL'][] = 1;
        // $expected['birth']['aspects']['UR-PL'][] = 1;
        // $expected['birth']['aspects']['UR-NN'][] = 1;
        // $expected['birth']['aspects']['UR-NN'][] = 1;
        // //
        // $expected['birth']['aspects']['NE-PL'][] = 1;
        // $expected['birth']['aspects']['NE-PL'][] = 1;
        // $expected['birth']['aspects']['NE-NN'][] = 1;
        // $expected['birth']['aspects']['NE-NN'][] = 1;
        //
        $expected['birth']['aspects']['PL-NN'][94] = 1;
        $expected['birth']['aspects']['PL-NN'][187] = 1;
        //
        $expected['birth']['year']['1922'] = 1;
        $expected['birth']['year']['1969'] = 1;
        //
        $expected['birth']['day']['01-09'] = 1;
        $expected['birth']['day']['03-29'] = 1;
        //
        // death
        //
        $expected['death']['planets']['SO'][258] = 1;
        $expected['death']['planets']['SO'][34] = 1;
        $expected['death']['planets']['MO'][48] = 1;
        $expected['death']['planets']['MO'][262] = 1;
        $expected['death']['planets']['ME'][278] = 1;
        $expected['death']['planets']['ME'][52] = 1;
        $expected['death']['planets']['VE'][221] = 1;
        $expected['death']['planets']['VE'][57] = 1;
        $expected['death']['planets']['MA'][212] = 1;
        $expected['death']['planets']['MA'][64] = 1;
        $expected['death']['planets']['JU'][233] = 1;
        $expected['death']['planets']['JU'][210] = 1;
        $expected['death']['planets']['SA'][46] = 1;
        $expected['death']['planets']['SA'][41] = 1;
        $expected['death']['planets']['UR'][192] = 1;
        $expected['death']['planets']['UR'][185] = 1;
        $expected['death']['planets']['NE'][241] = 1;
        $expected['death']['planets']['NE'][240] = 1;
        $expected['death']['planets']['PL'][181] = 1;
        $expected['death']['planets']['PL'][173] = 1;
        $expected['death']['planets']['NN'][327] = 1;
        $expected['death']['planets']['NN'][339] = 1;
        //
        $expected['death']['aspects']['SO-MO'][150] = 1;
        $expected['death']['aspects']['SO-MO'][227] = 1;
        $expected['death']['aspects']['SO-ME'][20] = 1;
        $expected['death']['aspects']['SO-ME'][17] = 1;
        // $expected['death']['aspects']['SO-VE'][] = 1;
        // $expected['death']['aspects']['SO-VE'][] = 1;
        // $expected['death']['aspects']['SO-MA'][] = 1;
        // $expected['death']['aspects']['SO-MA'][] = 1;
        // $expected['death']['aspects']['SO-JU'][] = 1;
        // $expected['death']['aspects']['SO-JU'][] = 1;
        // $expected['death']['aspects']['SO-SA'][] = 1;
        // $expected['death']['aspects']['SO-SA'][] = 1;
        // $expected['death']['aspects']['SO-UR'][] = 1;
        // $expected['death']['aspects']['SO-UR'][] = 1;
        // $expected['death']['aspects']['SO-NE'][] = 1;
        // $expected['death']['aspects']['SO-NE'][] = 1;
        // $expected['death']['aspects']['SO-PL'][] = 1;
        // $expected['death']['aspects']['SO-PL'][] = 1;
        // $expected['death']['aspects']['SO-NN'][] = 1;
        // $expected['death']['aspects']['SO-NN'][] = 1;
        // //
        // $expected['death']['aspects']['MO-ME'][] = 1;
        // $expected['death']['aspects']['MO-ME'][] = 1;
        // $expected['death']['aspects']['MO-VE'][] = 1;
        // $expected['death']['aspects']['MO-VE'][] = 1;
        // $expected['death']['aspects']['MO-MA'][] = 1;
        // $expected['death']['aspects']['MO-MA'][] = 1;
        // $expected['death']['aspects']['MO-JU'][] = 1;
        // $expected['death']['aspects']['MO-JU'][] = 1;
        // $expected['death']['aspects']['MO-SA'][] = 1;
        // $expected['death']['aspects']['MO-SA'][] = 1;
        // $expected['death']['aspects']['MO-UR'][] = 1;
        // $expected['death']['aspects']['MO-UR'][] = 1;
        // $expected['death']['aspects']['MO-NE'][] = 1;
        // $expected['death']['aspects']['MO-NE'][] = 1;
        // $expected['death']['aspects']['MO-PL'][] = 1;
        // $expected['death']['aspects']['MO-PL'][] = 1;
        // $expected['death']['aspects']['MO-NN'][] = 1;
        // $expected['death']['aspects']['MO-NN'][] = 1;
        // //
        // $expected['death']['aspects']['ME-VE'][] = 1;
        // $expected['death']['aspects']['ME-VE'][] = 1;
        // $expected['death']['aspects']['ME-MA'][] = 1;
        // $expected['death']['aspects']['ME-MA'][] = 1;
        // $expected['death']['aspects']['ME-JU'][] = 1;
        // $expected['death']['aspects']['ME-JU'][] = 1;
        // $expected['death']['aspects']['ME-SA'][] = 1;
        // $expected['death']['aspects']['ME-SA'][] = 1;
        // $expected['death']['aspects']['ME-UR'][] = 1;
        // $expected['death']['aspects']['ME-UR'][] = 1;
        // $expected['death']['aspects']['ME-NE'][] = 1;
        // $expected['death']['aspects']['ME-NE'][] = 1;
        // $expected['death']['aspects']['ME-PL'][] = 1;
        // $expected['death']['aspects']['ME-PL'][] = 1;
        // $expected['death']['aspects']['ME-NN'][] = 1;
        // $expected['death']['aspects']['ME-NN'][] = 1;
        // //
        // $expected['death']['aspects']['VE-MA'][] = 1;
        // $expected['death']['aspects']['VE-MA'][] = 1;
        // $expected['death']['aspects']['VE-JU'][] = 1;
        // $expected['death']['aspects']['VE-JU'][] = 1;
        // $expected['death']['aspects']['VE-SA'][] = 1;
        // $expected['death']['aspects']['VE-SA'][] = 1;
        // $expected['death']['aspects']['VE-UR'][] = 1;
        // $expected['death']['aspects']['VE-UR'][] = 1;
        // $expected['death']['aspects']['VE-NE'][] = 1;
        // $expected['death']['aspects']['VE-NE'][] = 1;
        // $expected['death']['aspects']['VE-PL'][] = 1;
        // $expected['death']['aspects']['VE-PL'][] = 1;
        // $expected['death']['aspects']['VE-NN'][] = 1;
        // $expected['death']['aspects']['VE-NN'][] = 1;
        // //
        // $expected['death']['aspects']['MA-JU'][] = 1;
        // $expected['death']['aspects']['MA-JU'][] = 1;
        // $expected['death']['aspects']['MA-SA'][] = 1;
        // $expected['death']['aspects']['MA-SA'][] = 1;
        // $expected['death']['aspects']['MA-UR'][] = 1;
        // $expected['death']['aspects']['MA-UR'][] = 1;
        // $expected['death']['aspects']['MA-NE'][] = 1;
        // $expected['death']['aspects']['MA-NE'][] = 1;
        // $expected['death']['aspects']['MA-PL'][] = 1;
        // $expected['death']['aspects']['MA-PL'][] = 1;
        // $expected['death']['aspects']['MA-NN'][] = 1;
        // $expected['death']['aspects']['MA-NN'][] = 1;
        // //
        // $expected['death']['aspects']['JU-SA'][] = 1;
        // $expected['death']['aspects']['JU-SA'][] = 1;
        // $expected['death']['aspects']['JU-UR'][] = 1;
        // $expected['death']['aspects']['JU-UR'][] = 1;
        // $expected['death']['aspects']['JU-NE'][] = 1;
        // $expected['death']['aspects']['JU-NE'][] = 1;
        // $expected['death']['aspects']['JU-PL'][] = 1;
        // $expected['death']['aspects']['JU-PL'][] = 1;
        // $expected['death']['aspects']['JU-NN'][] = 1;
        // $expected['death']['aspects']['JU-NN'][] = 1;
        // //
        // $expected['death']['aspects']['SA-UR'][] = 1;
        // $expected['death']['aspects']['SA-UR'][] = 1;
        // $expected['death']['aspects']['SA-NE'][] = 1;
        // $expected['death']['aspects']['SA-NE'][] = 1;
        // $expected['death']['aspects']['SA-PL'][] = 1;
        // $expected['death']['aspects']['SA-PL'][] = 1;
        // $expected['death']['aspects']['SA-NN'][] = 1;
        // $expected['death']['aspects']['SA-NN'][] = 1;
        // //
        // $expected['death']['aspects']['UR-NE'][] = 1;
        // $expected['death']['aspects']['UR-NE'][] = 1;
        // $expected['death']['aspects']['UR-PL'][] = 1;
        // $expected['death']['aspects']['UR-PL'][] = 1;
        // $expected['death']['aspects']['UR-NN'][] = 1;
        // $expected['death']['aspects']['UR-NN'][] = 1;
        // //
        // $expected['death']['aspects']['NE-PL'][] = 1;
        // $expected['death']['aspects']['NE-PL'][] = 1;
        // $expected['death']['aspects']['NE-NN'][] = 1;
        // $expected['death']['aspects']['NE-NN'][] = 1;
        //
        $expected['death']['aspects']['PL-NN'][145] = 1;
        $expected['death']['aspects']['PL-NN'][165] = 1;
        //
        $expected['death']['year']['1970'] = 2;
        //
        $expected['death']['day']['12-10'] = 1;
        $expected['death']['day']['04-25'] = 1;
        //
        // birth-death
        //
        $expected['birth-death']['interaspects']['SO-SO'][329] = 1;
        $expected['birth-death']['interaspects']['SO-SO'][26] = 1;
        // $expected['birth-death']['interaspects']['SO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['SO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['SO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['SO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['SO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['SO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['SO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['SO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['SO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['SO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['SO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['SO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['SO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['SO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['SO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['SO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['SO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['SO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['SO-NN'][] = 1;
        // $expected['birth-death']['interaspects']['SO-NN'][] = 1;
        //
        // $expected['birth-death']['interaspects']['MO-SO'][] = 1;
        // $expected['birth-death']['interaspects']['MO-SO'][] = 1;
        // $expected['birth-death']['interaspects']['MO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['MO-MO'][] = 1;
        // $expected['birth-death']['interaspects']['MO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['MO-ME'][] = 1;
        // $expected['birth-death']['interaspects']['MO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['MO-VE'][] = 1;
        // $expected['birth-death']['interaspects']['MO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['MO-MA'][] = 1;
        // $expected['birth-death']['interaspects']['MO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['MO-JU'][] = 1;
        // $expected['birth-death']['interaspects']['MO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['MO-SA'][] = 1;
        // $expected['birth-death']['interaspects']['MO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['MO-UR'][] = 1;
        // $expected['birth-death']['interaspects']['MO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['MO-NE'][] = 1;
        // $expected['birth-death']['interaspects']['MO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['MO-PL'][] = 1;
        // $expected['birth-death']['interaspects']['MO-NN'][] = 1;
        // $expected['birth-death']['interaspects']['MO-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['ME-SO'][] = 1;
        // $expected['birth-death']['interaspects']['ME-SO'][] = 1;
        // $expected['birth-death']['interaspects']['ME-MO'][] = 1;
        // $expected['birth-death']['interaspects']['ME-MO'][] = 1;
        // $expected['birth-death']['interaspects']['ME-ME'][] = 1;
        // $expected['birth-death']['interaspects']['ME-ME'][] = 1;
        // $expected['birth-death']['interaspects']['ME-VE'][] = 1;
        // $expected['birth-death']['interaspects']['ME-VE'][] = 1;
        // $expected['birth-death']['interaspects']['ME-MA'][] = 1;
        // $expected['birth-death']['interaspects']['ME-MA'][] = 1;
        // $expected['birth-death']['interaspects']['ME-JU'][] = 1;
        // $expected['birth-death']['interaspects']['ME-JU'][] = 1;
        // $expected['birth-death']['interaspects']['ME-SA'][] = 1;
        // $expected['birth-death']['interaspects']['ME-SA'][] = 1;
        // $expected['birth-death']['interaspects']['ME-UR'][] = 1;
        // $expected['birth-death']['interaspects']['ME-UR'][] = 1;
        // $expected['birth-death']['interaspects']['ME-NE'][] = 1;
        // $expected['birth-death']['interaspects']['ME-NE'][] = 1;
        // $expected['birth-death']['interaspects']['ME-PL'][] = 1;
        // $expected['birth-death']['interaspects']['ME-PL'][] = 1;
        // $expected['birth-death']['interaspects']['ME-NN'][] = 1;
        // $expected['birth-death']['interaspects']['ME-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['VE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['VE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['VE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['VE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['VE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['VE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['VE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['VE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['VE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['VE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['VE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['VE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['VE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['VE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['VE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['VE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['VE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['VE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['VE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['VE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['VE-NN'][] = 1;
        // $expected['birth-death']['interaspects']['VE-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['MA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['MA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['MA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['MA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['MA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['MA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['MA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['MA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['MA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['MA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['MA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['MA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['MA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['MA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['MA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['MA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['MA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['MA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['MA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['MA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['MA-NN'][] = 1;
        // $expected['birth-death']['interaspects']['MA-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['JU-SO'][] = 1;
        // $expected['birth-death']['interaspects']['JU-SO'][] = 1;
        // $expected['birth-death']['interaspects']['JU-MO'][] = 1;
        // $expected['birth-death']['interaspects']['JU-MO'][] = 1;
        // $expected['birth-death']['interaspects']['JU-ME'][] = 1;
        // $expected['birth-death']['interaspects']['JU-ME'][] = 1;
        // $expected['birth-death']['interaspects']['JU-VE'][] = 1;
        // $expected['birth-death']['interaspects']['JU-VE'][] = 1;
        // $expected['birth-death']['interaspects']['JU-MA'][] = 1;
        // $expected['birth-death']['interaspects']['JU-MA'][] = 1;
        // $expected['birth-death']['interaspects']['JU-JU'][] = 1;
        // $expected['birth-death']['interaspects']['JU-JU'][] = 1;
        // $expected['birth-death']['interaspects']['JU-SA'][] = 1;
        // $expected['birth-death']['interaspects']['JU-SA'][] = 1;
        // $expected['birth-death']['interaspects']['JU-UR'][] = 1;
        // $expected['birth-death']['interaspects']['JU-UR'][] = 1;
        // $expected['birth-death']['interaspects']['JU-NE'][] = 1;
        // $expected['birth-death']['interaspects']['JU-NE'][] = 1;
        // $expected['birth-death']['interaspects']['JU-PL'][] = 1;
        // $expected['birth-death']['interaspects']['JU-PL'][] = 1;
        // $expected['birth-death']['interaspects']['JU-NN'][] = 1;
        // $expected['birth-death']['interaspects']['JU-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['SA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['SA-SO'][] = 1;
        // $expected['birth-death']['interaspects']['SA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['SA-MO'][] = 1;
        // $expected['birth-death']['interaspects']['SA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['SA-ME'][] = 1;
        // $expected['birth-death']['interaspects']['SA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['SA-VE'][] = 1;
        // $expected['birth-death']['interaspects']['SA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['SA-MA'][] = 1;
        // $expected['birth-death']['interaspects']['SA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['SA-JU'][] = 1;
        // $expected['birth-death']['interaspects']['SA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['SA-SA'][] = 1;
        // $expected['birth-death']['interaspects']['SA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['SA-UR'][] = 1;
        // $expected['birth-death']['interaspects']['SA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['SA-NE'][] = 1;
        // $expected['birth-death']['interaspects']['SA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['SA-PL'][] = 1;
        // $expected['birth-death']['interaspects']['SA-NN'][] = 1;
        // $expected['birth-death']['interaspects']['SA-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['UR-SO'][] = 1;
        // $expected['birth-death']['interaspects']['UR-SO'][] = 1;
        // $expected['birth-death']['interaspects']['UR-MO'][] = 1;
        // $expected['birth-death']['interaspects']['UR-MO'][] = 1;
        // $expected['birth-death']['interaspects']['UR-ME'][] = 1;
        // $expected['birth-death']['interaspects']['UR-ME'][] = 1;
        // $expected['birth-death']['interaspects']['UR-VE'][] = 1;
        // $expected['birth-death']['interaspects']['UR-VE'][] = 1;
        // $expected['birth-death']['interaspects']['UR-MA'][] = 1;
        // $expected['birth-death']['interaspects']['UR-MA'][] = 1;
        // $expected['birth-death']['interaspects']['UR-JU'][] = 1;
        // $expected['birth-death']['interaspects']['UR-JU'][] = 1;
        // $expected['birth-death']['interaspects']['UR-SA'][] = 1;
        // $expected['birth-death']['interaspects']['UR-SA'][] = 1;
        // $expected['birth-death']['interaspects']['UR-UR'][] = 1;
        // $expected['birth-death']['interaspects']['UR-UR'][] = 1;
        // $expected['birth-death']['interaspects']['UR-NE'][] = 1;
        // $expected['birth-death']['interaspects']['UR-NE'][] = 1;
        // $expected['birth-death']['interaspects']['UR-PL'][] = 1;
        // $expected['birth-death']['interaspects']['UR-PL'][] = 1;
        // $expected['birth-death']['interaspects']['UR-NN'][] = 1;
        // $expected['birth-death']['interaspects']['UR-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['NE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['NE-SO'][] = 1;
        // $expected['birth-death']['interaspects']['NE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['NE-MO'][] = 1;
        // $expected['birth-death']['interaspects']['NE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['NE-ME'][] = 1;
        // $expected['birth-death']['interaspects']['NE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['NE-VE'][] = 1;
        // $expected['birth-death']['interaspects']['NE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['NE-MA'][] = 1;
        // $expected['birth-death']['interaspects']['NE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['NE-JU'][] = 1;
        // $expected['birth-death']['interaspects']['NE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['NE-SA'][] = 1;
        // $expected['birth-death']['interaspects']['NE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['NE-UR'][] = 1;
        // $expected['birth-death']['interaspects']['NE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['NE-NE'][] = 1;
        // $expected['birth-death']['interaspects']['NE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['NE-PL'][] = 1;
        // $expected['birth-death']['interaspects']['NE-NN'][] = 1;
        // $expected['birth-death']['interaspects']['NE-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['PL-SO'][] = 1;
        // $expected['birth-death']['interaspects']['PL-SO'][] = 1;
        // $expected['birth-death']['interaspects']['PL-MO'][] = 1;
        // $expected['birth-death']['interaspects']['PL-MO'][] = 1;
        // $expected['birth-death']['interaspects']['PL-ME'][] = 1;
        // $expected['birth-death']['interaspects']['PL-ME'][] = 1;
        // $expected['birth-death']['interaspects']['PL-VE'][] = 1;
        // $expected['birth-death']['interaspects']['PL-VE'][] = 1;
        // $expected['birth-death']['interaspects']['PL-MA'][] = 1;
        // $expected['birth-death']['interaspects']['PL-MA'][] = 1;
        // $expected['birth-death']['interaspects']['PL-JU'][] = 1;
        // $expected['birth-death']['interaspects']['PL-JU'][] = 1;
        // $expected['birth-death']['interaspects']['PL-SA'][] = 1;
        // $expected['birth-death']['interaspects']['PL-SA'][] = 1;
        // $expected['birth-death']['interaspects']['PL-UR'][] = 1;
        // $expected['birth-death']['interaspects']['PL-UR'][] = 1;
        // $expected['birth-death']['interaspects']['PL-NE'][] = 1;
        // $expected['birth-death']['interaspects']['PL-NE'][] = 1;
        // $expected['birth-death']['interaspects']['PL-PL'][] = 1;
        // $expected['birth-death']['interaspects']['PL-PL'][] = 1;
        // $expected['birth-death']['interaspects']['PL-NN'][] = 1;
        // $expected['birth-death']['interaspects']['PL-NN'][] = 1;
        // //
        // $expected['birth-death']['interaspects']['NN-SO'][] = 1;
        // $expected['birth-death']['interaspects']['NN-SO'][] = 1;
        // $expected['birth-death']['interaspects']['NN-MO'][] = 1;
        // $expected['birth-death']['interaspects']['NN-MO'][] = 1;
        // $expected['birth-death']['interaspects']['NN-ME'][] = 1;
        // $expected['birth-death']['interaspects']['NN-ME'][] = 1;
        // $expected['birth-death']['interaspects']['NN-VE'][] = 1;
        // $expected['birth-death']['interaspects']['NN-VE'][] = 1;
        // $expected['birth-death']['interaspects']['NN-MA'][] = 1;
        // $expected['birth-death']['interaspects']['NN-MA'][] = 1;
        // $expected['birth-death']['interaspects']['NN-JU'][] = 1;
        // $expected['birth-death']['interaspects']['NN-JU'][] = 1;
        // $expected['birth-death']['interaspects']['NN-SA'][] = 1;
        // $expected['birth-death']['interaspects']['NN-SA'][] = 1;
        // $expected['birth-death']['interaspects']['NN-UR'][] = 1;
        // $expected['birth-death']['interaspects']['NN-UR'][] = 1;
        // $expected['birth-death']['interaspects']['NN-NE'][] = 1;
        // $expected['birth-death']['interaspects']['NN-NE'][] = 1;
        // $expected['birth-death']['interaspects']['NN-PL'][] = 1;
        // $expected['birth-death']['interaspects']['NN-PL'][] = 1;
        $expected['birth-death']['interaspects']['NN-NN'][133] = 1;
        $expected['birth-death']['interaspects']['NN-NN'][339] = 1;
        //
        $expected['birth-death']['age'][587] = 1;
        $expected['birth-death']['age'][13] = 1;
        
        $computed = Distribs::computeDistributions($f, self::$studyConfig);
        $this->assertEquals($computed['birth']['planets'], $expected['birth']['planets']);
        $this->assertEquals($computed['birth']['aspects']['SO-MO'], $expected['birth']['aspects']['SO-MO']);
        $this->assertEquals($computed['birth']['aspects']['SO-ME'], $expected['birth']['aspects']['SO-ME']);
        $this->assertEquals($computed['birth']['aspects']['PL-NN'], $expected['birth']['aspects']['PL-NN']);
        $this->assertEquals($computed['birth']['year'], $expected['birth']['year']);
        $this->assertEquals($computed['birth']['day'], $expected['birth']['day']);
        //
        $this->assertEquals($computed['death']['planets'], $expected['death']['planets']);
        $this->assertEquals($computed['death']['aspects']['SO-MO'], $expected['death']['aspects']['SO-MO']);
        $this->assertEquals($computed['death']['aspects']['SO-ME'], $expected['death']['aspects']['SO-ME']);
        $this->assertEquals($computed['death']['aspects']['PL-NN'], $expected['death']['aspects']['PL-NN']);
        $this->assertEquals($computed['death']['year'], $expected['death']['year']);
        $this->assertEquals($computed['death']['day'], $expected['death']['day']);
        //
        $this->assertEquals($computed['birth-death']['interaspects']['SO-SO'], $expected['birth-death']['interaspects']['SO-SO']);
        $this->assertEquals($computed['birth-death']['interaspects']['NN-NN'], $expected['birth-death']['interaspects']['NN-NN']);
        $this->assertEquals($computed['birth-death']['age'], $expected['birth-death']['age']);
    }
    
}// end class
