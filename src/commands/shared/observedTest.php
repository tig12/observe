<?php
/******************************************************************************
    
    Functional test for src/command/shared/observed.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use PHPUnit\Framework\TestCase;
use observe\commands\tests\Death_fr_tests;
use observe\model\Observe;
use observe\model\Studies;
use observe\model\distrib\EmptyDistribs;
use observe\commands\death_fr\Death_fr;

class observedTest extends TestCase{
    
    private static array $studyConfig;

    public static function setUpBeforeClass(): void {
        self::$studyConfig = Death_fr_tests::loadStudy('study1/study1.yml');
        foreach(self::$studyConfig['splits'] as $split){
            observed::execute(self::$studyConfig, [$split]);
        }
    }
    
    // Another possible test: check that sums of the lines in the data.bz2 of age
    // are the same as the nb of lines in full/data.bz2
    
    /**
        Test that the sums of the observed distributions are equal to the number of elements in the original data.
    **/
    public function testStudy1_sums(){
        $nDates = count(self::$studyConfig['dates']);
        $nPlanets = count(self::$studyConfig['planets']);
        
        foreach(self::$studyConfig['splits'] as $split){
            $splitDir = Studies::getSplitDirectory(self::$studyConfig, $split);
            $subgroups = Death_fr::getSplitSubgroups($split);
            foreach($subgroups as $subgroup){
                $subgroupDir = Studies::getSubgroupDirectory(self::$studyConfig, $split, $subgroup);
                // sum of elements in data.csv.bz2
                $filename = 'compress.bzip2://' . $subgroupDir . DS . 'data.csv.bz2';
                $fileHandle = fopen($filename, 'r');
                $rawSum = 0; // nb of elements present in the subgroup
                while(false !== $line = fgets($fileHandle)){
                    $rawSum++;
                }
                fclose($fileHandle);
                //
                $observedDir = Studies::getObservedDirectory(self::$studyConfig, $split, $subgroup);
                //
                // distributions of type distrib1
                //
                for($i=0; $i < $nDates; $i++){
                    $dateName = self::$studyConfig['dates'][$i]; // ex: birth
                    // planets
                    foreach(self::$studyConfig['planets'] as $planet){
                        $observedFile = implode(DS, [$observedDir, $dateName, 'planets', $planet . '.csv']);
                        $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                        $observedSum = array_sum($observedDistrib);
                        $this->assertEquals($observedSum, $rawSum);
                    }
                    // aspects
                    for($j=0; $j < $nPlanets; $j++){
                        for($k=$j+1; $k < $nPlanets; $k++){
                            $key = self::$studyConfig['planets'][$j] . '-' . self::$studyConfig['planets'][$k]; // ex: MA-NE
                            $observedFile = implode(DS, [$observedDir, $dateName, 'aspects', $key . '.csv']);
                            $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                            $observedSum = array_sum($observedDistrib);
                            $this->assertEquals($observedSum, $rawSum);
                        }
                    }
                    // day
                    $observedFile = implode(DS, [$observedDir, $dateName, 'day.csv']);
                    $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                    $observedSum = array_sum($observedDistrib);
                    $this->assertEquals($observedSum, $rawSum);
                    // year
                    $observedFile = implode(DS, [$observedDir, $dateName, 'year.csv']);
                    $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                    $observedSum = array_sum($observedDistrib);
                    $this->assertEquals($observedSum, $rawSum);
                } // end loop on dates
                //
                // distributions of type distrib2
                //
                for($i=0; $i < $nDates; $i++){
                    for($j=$i+1; $j < $nDates; $j++){
                        $dateName1 = self::$studyConfig['dates'][$i];
                        $dateName2 = self::$studyConfig['dates'][$j];
                        $dateName = "$dateName1-$dateName2"; // ex: birth-death
                        // interaspects
                        foreach(self::$studyConfig['planets'] as $planet1){
                            foreach(self::$studyConfig['planets'] as $planet2){
                                $observedFile = implode(DS, [$observedDir, $dateName, 'interaspects', "$planet1-$planet2.csv"]);
                                $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                                $observedSum = array_sum($observedDistrib);
                                $this->assertEquals($observedSum, $rawSum);
                            }
                        }
                        // age
                        $observedFile = implode(DS, [$observedDir, $dateName, 'age.csv']);
                        $observedDistrib = Death_fr_tests::readCsv($observedFile, Observe::CSV_SEP);
                        $observedSum = array_sum($observedDistrib);
                        $this->assertEquals($observedSum, $rawSum);
                    } // end loop on $j
                } // end loop on $i
            } // end loop on subgroups
        } // end loop on splits
    }
    
    /** 
        Tests a subset of values for split full.
    **/
    public function testStudy1_full_values(){
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
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'day.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
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
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'day.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
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
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'year.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        //
        // death - year
        //
        $wanted = [
            '1991' => 2,
            '1992' => 8,
        ];
        //
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'year.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        //
        // birth - planets
        //
        $wanted = [
            'SO' => $arr360,
            'MO' => $arr360,
            'ME' => $arr360,
            'VE' => $arr360,
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
        $dir = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'planets']);
        foreach(['SO', 'MO', 'ME', 'VE'] as $planet){
            $filename = $dir . DS . $planet . '.csv';
            $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
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
        $dir = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'planets']);
        foreach(['SO', 'MO', 'ME'] as $planet){
            $filename = $dir . DS . $planet . '.csv';
            $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
            $this->assertEquals($observed, $wanted[$planet]);
        }
        //
        // birth - aspects MA-JU
        //
        // calc 313.844-24.07
        // calc 336.678-190.736
        // calc 360-57.423+17.535
        // calc 360-266.883+66.364
        // calc 360-149.357+97.448
        // calc 127.302-19.84
        // calc 142.843-70.671
        // calc 72.428-57.174
        // calc 360-284.755+46.816
        // calc 360-264.899+164.627
        //     289.774
        //     145.942
        //     320.112
        //     159.481
        //     308.091
        //     107.462
        //     72.172
        //     15.254
        //     122.061
        //     259.728
        $wanted =  $arr360;
        $wanted[289] = 1;
        $wanted[145] = 1;
        $wanted[320] = 1;
        $wanted[159] = 1;
        $wanted[308] = 1;
        $wanted[107] = 1;
        $wanted[72] = 1;
        $wanted[15] = 1;
        $wanted[122] = 1;
        $wanted[259] = 1;
        //
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth', 'aspects', 'MA-JU.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        //
        // death - aspects ME-VE
        //
        // calc 360-257.457+239.836
        // calc 360-258.656+241.041
        // calc 360-262.44+244.665
        // calc 360-263.753+245.875
        // calc 360-265.088+247.086
        //     342.379
        //     342.385
        //     342.225
        //     342.122
        //     341.998
        $wanted =  $arr360;
        $wanted[342] = 8;
        $wanted[341] = 2;
        //
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'death', 'aspects', 'ME-VE.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        //
        // birth-death - age
        //
        // See src/commands/test-files/study1/README to build $wanted from person database.
        $wanted = [
            1024 => 1,
            1065 => 1,
            1035 => 1,
            1007 => 1,
            598 => 1, 
            1076 => 1,
            1052 => 1,
            0 => 1,   
            470 => 1, 
            714 => 1, 
        ];
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth-death', 'age.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
        //
        // birth-death - interaspects SO-SO
        //
        // calc 279.302-167.835
        // calc 360-358.71 +279.302
        // calc 280.322-189.612
        // calc 360-318.355+280.322
        // calc 360-341.286+280.322
        // calc 284.401-28.496 
        // calc 280.322-53.246                                                          
        // calc 283.381-281.342
        // calc 285.42-219.016
        // calc 285.42-105.129
        //     111.467
        //     280.592
        //     90.71
        //     321.967
        //     299.036
        //     255.905
        //     227.076
        //     2.039
        //     66.404
        //     180.291
        $wanted =  $arr360;
        $wanted[111] = 1;
        $wanted[280] = 1;
        $wanted[90] = 1;
        $wanted[321] = 1;
        $wanted[299] = 1;
        $wanted[255] = 1;
        $wanted[227] = 1;
        $wanted[2] = 1;
        $wanted[66] = 1;
        $wanted[180] = 1;
        //
        $filename = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years', 'observed', 'birth-death', 'interaspects', 'SO-SO.csv']);
        $observed = Death_fr_tests::readCsv($filename, Observe::CSV_SEP);
        $this->assertEquals($observed, $wanted);
    }
    
}// end class
