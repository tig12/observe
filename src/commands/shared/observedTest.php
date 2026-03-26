<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\Studies;
use observe\model\distrib\EmptyDistribs;
use observe\commands\death_fr\Death_fr;

class observedTest extends TestCase{
    
/*
In person database:
select bday,dday from person;
+------------+------------+
|    bday    |    dday    |
+------------+------------+
| 1906-09-11 | 1991-12-31 |
| 1903-03-20 | 1991-12-31 |
| 1905-10-03 | 1992-01-01 |
| 1908-02-08 | 1992-01-01 |
| 1942-03-02 | 1992-01-01 |
| 1902-04-19 | 1992-01-05 |
| 1904-05-14 | 1992-01-01 |
| 1992-01-02 | 1992-01-04 |
| 1952-11-01 | 1992-01-06 |
| 1932-07-07 | 1992-01-06 |
+------------+------------+

In planet database:

Birth days:
select * from planet where day in (
'1906-09-11',
'1903-03-20',
'1905-10-03',
'1908-02-08',
'1942-03-02',
'1902-04-19',
'1904-05-14',
'1992-01-02',
'1952-11-01',
'1932-07-07'
);
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
|    day     |   SO    |   MO    |   ME    |   VE    |   MA    |   JU    |   SA    |   UR    |   NE    |   PL    |   NN    |
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
| 1902-04-19 | 28.496  | 171.005 | 18.175  | 342.467 | 24.027  | 313.844 | 297.526 | 261.048 | 89.129  | 76.419  | 214.755 |
| 1903-03-20 | 358.71  | 262.259 | 338.5   | 25.119  | 190.736 | 336.678 | 306.497 | 265.58  | 90.942  | 76.759  | 197.015 |
| 1904-05-14 | 53.246  | 41.639  | 51.642  | 38.444  | 57.423  | 17.535  | 320.791 | 269.31  | 94.143  | 79.246  | 174.722 |
| 1905-10-03 | 189.612 | 253.493 | 182.774 | 157.668 | 266.883 | 66.364  | 326.871 | 270.501 | 100.402 | 84.195  | 147.874 |
| 1906-09-11 | 167.835 | 84.701  | 156.456 | 213.996 | 149.357 | 97.448  | 341.174 | 274.526 | 102.324 | 85.241  | 129.711 |
| 1908-02-08 | 318.355 | 40.219  | 335.203 | 353.195 | 19.84   | 127.302 | 355.269 | 284.845 | 102.6   | 82.084  | 102.44  |
| 1932-07-07 | 105.129 | 148.562 | 128.226 | 92.375  | 70.671  | 142.843 | 302.658 | 23.179  | 155.907 | 111.494 | 350.303 |
| 1942-03-02 | 341.286 | 154.912 | 314.699 | 306.528 | 57.174  | 72.428  | 52.952  | 56.664  | 178.988 | 123.034 | 163.64  |
| 1952-11-01 | 219.016 | 32.944  | 240.354 | 252.573 | 284.755 | 46.816  | 200.501 | 108.502 | 202.1   | 144.748 | 317.279 |
| 1992-01-02 | 281.342 | 254.425 | 259.889 | 242.248 | 264.899 | 164.627 | 306.022 | 283.78  | 286.284 | 233.758 | 279.721 |
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+

Death days:
select * from planet where day in (
'1991-12-31',
'1991-12-31',
'1992-01-01',
'1992-01-01',
'1992-01-01',
'1992-01-05',
'1992-01-01',
'1992-01-04',
'1992-01-06',
'1992-01-06'
);
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
|    day     |   SO    |   MO    |   ME    |   VE    |   MA    |   JU    |   SA    |   UR    |   NE    |   PL    |   NN    |
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
| 1991-12-31 | 279.302 | 229.953 | 257.457 | 239.836 | 263.427 | 164.637 | 305.798 | 283.661 | 286.208 | 233.651 | 279.827 |    x 2
| 1992-01-01 | 280.322 | 242.279 | 258.656 | 241.041 | 264.163 | 164.634 | 305.91  | 283.72  | 286.246 | 233.705 | 279.774 |    x 4
| 1992-01-04 | 283.381 | 278.33  | 262.44  | 244.665 | 266.374 | 164.603 | 306.248 | 283.9   | 286.359 | 233.862 | 279.616 |    x 1
| 1992-01-05 | 284.401 | 290.161 | 263.753 | 245.875 | 267.113 | 164.586 | 306.361 | 283.959 | 286.397 | 233.912 | 279.563 |    x 1
| 1992-01-06 | 285.42  | 301.957 | 265.088 | 247.086 | 267.852 | 164.566 | 306.476 | 284.019 | 286.435 | 233.962 | 279.51  |    x 2
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+

*/

    private function loadStudy1(): array {
        $yamlStudyFile = implode (DS, [dirname(__DIR__), 'test-files', 'study1', 'study1.yml']);
        $studyConfig = yaml_parse_file($yamlStudyFile);
        Studies::initializeStudy($studyConfig);
        Death_fr::setSqlitePersonPath($studyConfig['sqlite-death-fr']);
        return $studyConfig;
    }
    
    public function testStudy1_full(){
        $studyConfig = $this->loadStudy1();
//        observed::execute($studyConfig, ['full']);
        $arr360 = array_fill(0, 360, 0);
        
        //
        // birth - day
        //
        $wanted = EmptyDistribs::emptyDayDistrib();
        $wanted['09-11'] = 1;
        $wanted['03-20'] = 1;
        $wanted['10-03'] = 1;
        $wanted['02-08'] = 1;
        $wanted['03-02'] = 1;
        $wanted['04-19'] = 1;
        $wanted['05-14'] = 1;
        $wanted['01-02'] = 1;
        $wanted['11-01'] = 1;
        $wanted['07-07'] = 1;
        //
        $filename = implode(DS, [$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'day.csv']);
        $observed = self::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        
        //
        // death - day
        //
        $wanted = EmptyDistribs::emptyDayDistrib();
        $wanted['12-31'] = 2;
        $wanted['01-01'] = 4;
        $wanted['01-05'] = 1;
        $wanted['01-04'] = 1;
        $wanted['01-06'] = 2;
        //
        $filename = implode(DS, [$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'day.csv']);
        $observed = self::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);

        //
        // birth - year
        //
        $wanted = [
            '1906' => 1,
            '1903' => 1,
            '1905' => 1,
            '1908' => 1,
            '1942' => 1,
            '1902' => 1,
            '1904' => 1,
            '1992' => 1,
            '1952' => 1,
            '1932' => 1,
        ];
        //
        $filename = implode(DS, [$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'year.csv']);
        $observed = self::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        
        //
        // death - year
        //
        $wanted = [
            '1991' => 2,
            '1992' => 8,
        ];
        //
        $filename = implode(DS, [$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'year.csv']);
        $observed = self::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        
        //
        // birth - planets
        //
        $wanted = [
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
        ];
        $wanted['SO'][28] = 1;
        $wanted['SO'][358] = 1;
        $wanted['SO'][53] = 1;
        $wanted['SO'][189] = 1;
        $wanted['SO'][167] = 1;
        $wanted['SO'][318] = 1;
        $wanted['SO'][105] = 1;
        $wanted['SO'][341] = 1;
        $wanted['SO'][219] = 1;
        $wanted['SO'][281] = 1;
        //
        $wanted['MO'][171] = 1;
        $wanted['MO'][262] = 1;
        $wanted['MO'][41] = 1;
        $wanted['MO'][253] = 1;
        $wanted['MO'][84] = 1;
        $wanted['MO'][40] = 1;
        $wanted['MO'][148] = 1;
        $wanted['MO'][154] = 1;
        $wanted['MO'][32] = 1;
        $wanted['MO'][254] = 1;
        //
        $wanted['ME'][18] = 1;
        $wanted['ME'][338] = 1;
        $wanted['ME'][51] = 1;
        $wanted['ME'][182] = 1;
        $wanted['ME'][156] = 1;
        $wanted['ME'][335] = 1;
        $wanted['ME'][128] = 1;
        $wanted['ME'][314] = 1;
        $wanted['ME'][240] = 1;
        $wanted['ME'][259] = 1;
        //
        $wanted['VE'][342] = 1;
        $wanted['VE'][25] = 1;
        $wanted['VE'][38] = 1;
        $wanted['VE'][157] = 1;
        $wanted['VE'][213] = 1;
        $wanted['VE'][353] = 1;
        $wanted['VE'][92] = 1;
        $wanted['VE'][306] = 1;
        $wanted['VE'][252] = 1;
        $wanted['VE'][242] = 1;
        //
        $dir = implode(DS, [$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'planets']);
        foreach(['SO', 'MO', 'ME', 'VE'] as $planet){
            $filename = $dir . DS . $planet . '.csv';
            $observed = self::readCsv($filename, Observe::CSV_SEP);
            $this->assertEquals($observed, $wanted[$planet]);
        }
            
        //
        // death - planets
        //
        $wanted = [
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
        ];
        $wanted['SO'][279] = 2;
        $wanted['SO'][280] = 4;
        $wanted['SO'][283] = 1;
        $wanted['SO'][284] = 1;
        $wanted['SO'][285] = 2;
        //
        $wanted['MO'][229] = 2;
        $wanted['MO'][242] = 4;
        $wanted['MO'][278] = 1;
        $wanted['MO'][290] = 1;
        $wanted['MO'][301] = 2;
        //
        $wanted['ME'][257] = 2;
        $wanted['ME'][258] = 4;
        $wanted['ME'][262] = 1;
        $wanted['ME'][263] = 1;
        $wanted['ME'][265] = 2;
        //
        $dir = implode(DS, [$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'planets']);
        foreach(['SO', 'MO', 'ME'] as $planet){
            $filename = $dir . DS . $planet . '.csv';
            $observed = self::readCsv($filename, Observe::CSV_SEP);
            $this->assertEquals($observed, $wanted[$planet]);
        }
/*
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
|    day     |   SO    |   MO    |   ME    |   VE    |   MA    |   JU    |   SA    |   UR    |   NE    |   PL    |   NN    |
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
| 1991-12-31 | 279.302 | 229.953 | 257.457 | 239.836 | 263.427 | 164.637 | 305.798 | 283.661 | 286.208 | 233.651 | 279.827 |
| 1992-01-01 | 280.322 | 242.279 | 258.656 | 241.041 | 264.163 | 164.634 | 305.91  | 283.72  | 286.246 | 233.705 | 279.774 |
| 1992-01-04 | 283.381 | 278.33  | 262.44  | 244.665 | 266.374 | 164.603 | 306.248 | 283.9   | 286.359 | 233.862 | 279.616 |
| 1992-01-05 | 284.401 | 290.161 | 263.753 | 245.875 | 267.113 | 164.586 | 306.361 | 283.959 | 286.397 | 233.912 | 279.563 |
| 1992-01-06 | 285.42  | 301.957 | 265.088 | 247.086 | 267.852 | 164.566 | 306.476 | 284.019 | 286.435 | 233.962 | 279.51  |
+------------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+---------+
*/
    }
    
    /* public function testStudy1_age(){
        $studyConfig = $this->loadStudy1();
        observed::execute($studyConfig, ['full']);
    } */
    
    private static function readCsv($filename, $delimiter=';'){
        $res = [];
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 0, $delimiter, escape: '')) !== false){
                if(count($data) == 1 && $data[0] == ''){
                    continue; // skip empty lines
                }
                $res[$data[0]] = $data[1];
            }
            fclose($handle);
        }
        return $res;
    }
}// end class
