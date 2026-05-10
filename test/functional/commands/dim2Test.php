<?php
/******************************************************************************
    
    Functional test for src/command/dim2.php
    
    usage: phpunit test/functional/commands/dim2Test.php

    Uses study1 - see config/test/study1-README
    
    @pre        This test needs that steps init, import and observed are performed:
                phpunit test/functional/studies/death_fr/importTest.php
                phpunit test/functional/commands/observedTest.php
                or
                php run-observe.php study1 import
                php run-observe.php study1 observed
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-05-04 22:37:29+02:00, Thierry Graff : Creation
********************************************************************************/

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use observe\studies\death_fr\Death_fr;
//use observe\commands\observed;
use observe\commands\dim2;

class dim2Test extends TestCase{
    
    private static IStudy $study;

    public static function setUpBeforeClass(): void {
        self::$study = new Death_fr('study1');
        
        // uncomment next lines to include previous steps in this test
        // observed::execute(self::$study, []);
        
//        dim2::execute(self::$study, []);
    }
    
    /** 
        Test the existence of the directories and files.
    **/
    public function test_files(){
        $observedDir = self::$study->getObservedDirectory();
        $nDates = count(self::$study->config['dates']);
        $nPlanets = count(self::$study->config['planets']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$study->config['dates'][$i]; // ex: birth
            //aspects
            $this->assertTrue(is_dir(implode(DS, [$observedDir, $dateName, 'aspects', 'dim2'])));
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$study->config['planets'][$j] . '-' . self::$study->config['planets'][$k]; // ex: MA-NE
                    $this->assertTrue(is_file(implode(DS, [$observedDir, $dateName, 'aspects', 'dim2', $key . '.csv'])));
                }
            }
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = self::$study->config['dates'][$i];
                $dateName2 = self::$study->config['dates'][$j];
                $dateName = "$dateName1-$dateName2"; // ex: birth-death
                // interaspects
                $this->assertTrue(is_dir(implode(DS, [$observedDir, $dateName, 'interaspects', 'dim2'])));
                foreach(self::$study->config['planets'] as $planet1){
                    foreach(self::$study->config['planets'] as $planet2){
                        $this->assertTrue(is_file(implode(DS, [$observedDir, $dateName, 'interaspects', 'dim2', "$planet1-$planet2.csv"])));
                    }
                }
            } // end loop on $j
        } // end loop on $i
    }
    
    /** 
        Checks that the sums of dim1 and dim2 distributions are equal.
    **/
    public function test_sums(){
        $observedDir = self::$study->getObservedDirectory();
        $nDates = count(self::$study->config['dates']);
        $nPlanets = count(self::$study->config['planets']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = self::$study->config['dates'][$i]; // ex: birth
            //aspects
            for($j=0; $j < $nPlanets; $j++){
                for($k=$j+1; $k < $nPlanets; $k++){
                    $key = self::$study->config['planets'][$j] . '-' . self::$study->config['planets'][$k]; // ex: MA-NE
                    $dim1_file = implode(DS, [$observedDir, $dateName, 'aspects', 'dim1', $key . '.csv']);
                    $dim2_file = implode(DS, [$observedDir, $dateName, 'aspects', 'dim2', $key . '.csv']);
                    $dim1_distrib = CsvDistrib::csv2distrib_dim1($dim1_file, Observe::CSV_SEP);
                    $dim2_distrib = CsvDistrib::csv2distrib_dim2($dim2_file, Observe::CSV_SEP);
                    $dim1_sum = round(array_sum($dim1_distrib));
                    $dim2_sum = 0;
                    foreach($dim2_distrib as $row){
                        $dim2_sum += array_sum($row);
                    }
                    $dim2_sum = round($dim2_sum);
                    $this->assertEquals($dim1_sum, $dim2_sum);
                }
            }
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = self::$study->config['dates'][$i];
                $dateName2 = self::$study->config['dates'][$j];
                $dateName = "$dateName1-$dateName2"; // ex: birth-death
                // interaspects
                foreach(self::$study->config['planets'] as $planet1){
                    foreach(self::$study->config['planets'] as $planet2){
                        $dim1_file = implode(DS, [$observedDir, $dateName, 'interaspects', 'dim1', $key . '.csv']);
                        $dim2_file = implode(DS, [$observedDir, $dateName, 'interaspects', 'dim2', $key . '.csv']);
                        $dim1_distrib = CsvDistrib::csv2distrib_dim1($dim1_file, Observe::CSV_SEP);
                        $dim2_distrib = CsvDistrib::csv2distrib_dim2($dim2_file, Observe::CSV_SEP);
                        $dim1_sum = round(array_sum($dim1_distrib));
                        $dim2_sum = 0;
                        foreach($dim2_distrib as $row){
                            $dim2_sum += array_sum($row);
                        }
                        $dim2_sum = round($dim2_sum);
                        $this->assertEquals($dim1_sum, $dim2_sum);
                    }
                }
            } // end loop on $j
        } // end loop on $i
    }
    
    /**
        Check some values of dim2 distributions.
        From config/test/study1-README:
select * from planet where day in ('1906-09-11', '1991-12-31');
select * from planet where day in ('1906-09-11', '1991-12-31');
select * from planet where day in ('1903-03-20', '1991-12-31');
select * from planet where day in ('1905-10-03', '1992-01-01');
select * from planet where day in ('1908-02-08', '1992-01-01');
select * from planet where day in ('1942-03-02', '1992-01-01');
select * from planet where day in ('1902-04-19', '1992-01-05');
select * from planet where day in ('1904-05-14', '1992-01-01');
select * from planet where day in ('1992-01-02', '1992-01-04');
select * from planet where day in ('1952-11-01', '1992-01-06');
select * from planet where day in ('1932-07-07', '1992-01-06');

+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+------------+-------------+
|    day     |     SO      |     MO      |     ME      |     VE      |     MA      |     JU      |     SA      |     UR      |     NE      |     PL     |     NN      |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+------------+-------------+
| 1906-09-11 | 167.8244931 | 84.6974614  | 156.4374004 | 213.9875369 | 149.3427842 | 97.4736461  | 341.1518549 | 274.5055836 | 102.3073759 | 83.7400261 | 129.7070613 |
| 1991-12-31 | 279.3010592 | 229.9697553 | 257.4540683 | 239.8332656 | 263.4218584 | 164.6286533 | 305.7997786 | 283.6507309 | 286.1973606 | 232.075761 | 279.8331533 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+------------+-------------+
| 1903-03-20 | 358.7072504 | 262.2582148 | 338.4912641 | 25.1114321  | 190.7290534 | 336.6931524 | 306.4702449 | 265.5827814 | 90.9209674  | 77.7313035 | 197.0165749 |
| 1991-12-31 | 279.3010592 | 229.9697553 | 257.4540683 | 239.8332656 | 263.4218584 | 164.6286533 | 305.7997786 | 283.6507309 | 286.1973606 | 232.075761 | 279.8331533 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+------------+-------------+
| 1905-10-03 | 189.6012735 | 253.488816  | 182.7555503 | 157.654448  | 266.8784229 | 66.4059481  | 326.8486659 | 270.4853423 | 100.3847533 | 82.7228727  | 147.8711034 |
| 1992-01-01 | 280.3205789 | 242.2959182 | 258.6529581 | 241.0388549 | 264.1575215 | 164.6250739 | 305.9113271 | 283.7103999 | 286.2351174 | 232.1056741 | 279.7802252 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1908-02-08 | 318.3448491 | 40.2151938  | 335.1906307 | 353.1830409 | 19.8326304  | 127.3344458 | 355.2401402 | 284.8128877 | 102.5895712 | 82.9111117  | 102.4351391 |
| 1992-01-01 | 280.3205789 | 242.2959182 | 258.6529581 | 241.0388549 | 264.1575215 | 164.6250739 | 305.9113271 | 283.7103999 | 286.2351174 | 232.1056741 | 279.7802252 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1942-03-02 | 341.2792933 | 154.9184266 | 314.6949645 | 306.524548  | 57.1666715  | 72.4227244  | 52.9648571  | 56.6661868  | 178.9790423 | 123.880214  | 163.639507  |
| 1992-01-01 | 280.3205789 | 242.2959182 | 258.6529581 | 241.0388549 | 264.1575215 | 164.6250739 | 305.9113271 | 283.7103999 | 286.2351174 | 232.1056741 | 279.7802252 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1902-04-19 | 28.4932547  | 171.0067716 | 18.1630357  | 342.4663514 | 24.0187474  | 313.8562247 | 297.5153743 | 261.0556979 | 89.1126118  | 77.0930466  | 214.75717   |
| 1992-01-05 | 284.3991789 | 290.1769323 | 263.7494409 | 245.8725465 | 267.1075318 | 164.5781778 | 306.362954  | 283.9492179 | 286.3866785 | 232.2210904 | 279.5685433 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1904-05-14 | 53.2394733  | 41.6368767  | 51.6425266  | 38.4318206  | 57.4127563  | 17.5588739  | 320.7576819 | 269.3106312 | 94.1141267  | 79.5456131  | 174.7206714 |
| 1992-01-01 | 280.3205789 | 242.2959182 | 258.6529581 | 241.0388549 | 264.1575215 | 164.6250739 | 305.9113271 | 283.7103999 | 286.2351174 | 232.1056741 | 279.7802252 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1992-01-02 | 281.3401787 | 254.4407928 | 259.8853761 | 242.2456305 | 264.8939344 | 164.61824  | 306.0234435 | 283.7701302 | 286.2729399 | 232.1351754 | 279.7273044 |
| 1992-01-04 | 283.3795073 | 278.3457282 | 262.4365871 | 244.6625269 | 266.3689536 | 164.594793 | 306.2492818 | 283.8897708 | 286.3487317 | 232.1928951 | 279.6214677 |
+------------+-------------+-------------+-------------+-------------+-------------+------------+-------------+-------------+-------------+-------------+-------------+
| 1952-11-01 | 219.0130042 | 32.9564606  | 240.3488688 | 252.5664768 | 284.7512984 | 46.8436921  | 200.5142749 | 108.5061195 | 202.0968939 | 143.1376329 | 317.2822626 |
| 1992-01-06 | 285.4188184 | 301.9727528 | 265.0838332 | 247.0835513 | 267.8468052 | 164.5583054 | 306.4770961 | 284.0090648 | 286.4246469 | 232.2488286 | 279.5156104 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
| 1932-07-07 | 105.124324  | 148.5698832 | 128.2176069 | 92.3755788  | 70.6635273  | 142.8250228 | 302.6666833 | 23.1980671  | 155.900993  | 111.5187774 | 350.3038812 |
| 1992-01-06 | 285.4188184 | 301.9727528 | 265.0838332 | 247.0835513 | 267.8468052 | 164.5583054 | 306.4770961 | 284.0090648 | 286.4246469 | 232.2488286 | 279.5156104 |
+------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+-------------+
    **/
    public function test_some_values() {
        $observedDir = self::$study->getObservedDirectory();
        //
        $file_aspects = implode(DS, [$observedDir, 'birth', 'aspects', 'dim2', 'SO-MO' . '.csv']);
        $distrib_aspects = CsvDistrib::csv2distrib_dim2($file_aspects, Observe::CSV_SEP);
        $this->assertEquals($distrib_aspects[167][84], 1);
        $this->assertEquals($distrib_aspects[358][262], 1);
        $this->assertEquals($distrib_aspects[189][253], 1);
        $this->assertEquals($distrib_aspects[318][40], 1);
        $this->assertEquals($distrib_aspects[341][154], 1);
        $this->assertEquals($distrib_aspects[28][171], 1);
        $this->assertEquals($distrib_aspects[53][41], 1);
        $this->assertEquals($distrib_aspects[281][254], 1);
        $this->assertEquals($distrib_aspects[219][32], 1);
        $this->assertEquals($distrib_aspects[105][148], 1);
        //
        $file_interaspects = implode(DS, [$observedDir, 'birth-death', 'interaspects', 'dim2', 'SO-SO' . '.csv']);
        $distrib_interaspects = CsvDistrib::csv2distrib_dim2($file_interaspects, Observe::CSV_SEP);
        $this->assertEquals($distrib_interaspects[167][279], 1);
        $this->assertEquals($distrib_interaspects[358][279], 1);
        $this->assertEquals($distrib_interaspects[189][280], 1);
        $this->assertEquals($distrib_interaspects[318][280], 1);
        $this->assertEquals($distrib_interaspects[341][280], 1);
        $this->assertEquals($distrib_interaspects[28][284], 1);
        $this->assertEquals($distrib_interaspects[53][280], 1);
        $this->assertEquals($distrib_interaspects[281][283], 1);
        $this->assertEquals($distrib_interaspects[219][285], 1);
        $this->assertEquals($distrib_interaspects[105][285], 1);
    }

/* 
    public function test_all_values() {
        $observedDir = self::$study->getObservedDirectory();
        [$aspects_wanted, $interaspects_wanted] = self::build_test_values();
        $file_aspects = implode(DS, [$observedDir, $dateName, 'aspects', 'dim2', $key . '.csv']);
    }
*/
    /**

.mode csv
.separator ";"
select * from planet where day in ('1906-09-11', '1991-12-31');
select * from planet where day in ('1903-03-20', '1991-12-31');
select * from planet where day in ('1905-10-03', '1992-01-01');
select * from planet where day in ('1908-02-08', '1992-01-01');
select * from planet where day in ('1942-03-02', '1992-01-01');
select * from planet where day in ('1902-04-19', '1992-01-05');
select * from planet where day in ('1904-05-14', '1992-01-01');
select * from planet where day in ('1992-01-02', '1992-01-04');
select * from planet where day in ('1952-11-01', '1992-01-06');
select * from planet where day in ('1932-07-07', '1992-01-06');
    **/
    /* private function build_test_values():array {
        $csvData = <<<CSV
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1906-09-11;167.8244931;84.6974614;156.4374004;213.9875369;149.3427842;97.4736461;341.1518549;274.5055836;102.3073759;83.7400261;129.7070613
1991-12-31;279.3010592;229.9697553;257.4540683;239.8332656;263.4218584;164.6286533;305.7997786;283.6507309;286.1973606;232.075761;279.8331533
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1903-03-20;358.7072504;262.2582148;338.4912641;25.1114321;190.7290534;336.6931524;306.4702449;265.5827814;90.9209674;77.7313035;197.0165749
1991-12-31;279.3010592;229.9697553;257.4540683;239.8332656;263.4218584;164.6286533;305.7997786;283.6507309;286.1973606;232.075761;279.8331533
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1905-10-03;189.6012735;253.488816;182.7555503;157.654448;266.8784229;66.4059481;326.8486659;270.4853423;100.3847533;82.7228727;147.8711034
1992-01-01;280.3205789;242.2959182;258.6529581;241.0388549;264.1575215;164.6250739;305.9113271;283.7103999;286.2351174;232.1056741;279.7802252
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1908-02-08;318.3448491;40.2151938;335.1906307;353.1830409;19.8326304;127.3344458;355.2401402;284.8128877;102.5895712;82.9111117;102.4351391
1992-01-01;280.3205789;242.2959182;258.6529581;241.0388549;264.1575215;164.6250739;305.9113271;283.7103999;286.2351174;232.1056741;279.7802252
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1942-03-02;341.2792933;154.9184266;314.6949645;306.524548;57.1666715;72.4227244;52.9648571;56.6661868;178.9790423;123.880214;163.639507
1992-01-01;280.3205789;242.2959182;258.6529581;241.0388549;264.1575215;164.6250739;305.9113271;283.7103999;286.2351174;232.1056741;279.7802252
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1902-04-19;28.4932547;171.0067716;18.1630357;342.4663514;24.0187474;313.8562247;297.5153743;261.0556979;89.1126118;77.0930466;214.75717
1992-01-05;284.3991789;290.1769323;263.7494409;245.8725465;267.1075318;164.5781778;306.362954;283.9492179;286.3866785;232.2210904;279.5685433
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1904-05-14;53.2394733;41.6368767;51.6425266;38.4318206;57.4127563;17.5588739;320.7576819;269.3106312;94.1141267;79.5456131;174.7206714
1992-01-01;280.3205789;242.2959182;258.6529581;241.0388549;264.1575215;164.6250739;305.9113271;283.7103999;286.2351174;232.1056741;279.7802252
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1992-01-02;281.3401787;254.4407928;259.8853761;242.2456305;264.8939344;164.61824;306.0234435;283.7701302;286.2729399;232.1351754;279.7273044
1992-01-04;283.3795073;278.3457282;262.4365871;244.6625269;266.3689536;164.594793;306.2492818;283.8897708;286.3487317;232.1928951;279.6214677
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1952-11-01;219.0130042;32.9564606;240.3488688;252.5664768;284.7512984;46.8436921;200.5142749;108.5061195;202.0968939;143.1376329;317.2822626
1992-01-06;285.4188184;301.9727528;265.0838332;247.0835513;267.8468052;164.5583054;306.4770961;284.0090648;286.4246469;232.2488286;279.5156104
day;SO;MO;ME;VE;MA;JU;SA;UR;NE;PL;NN
1932-07-07;105.124324;148.5698832;128.2176069;92.3755788;70.6635273;142.8250228;302.6666833;23.1980671;155.900993;111.5187774;350.3038812
1992-01-06;285.4188184;301.9727528;265.0838332;247.0835513;267.8468052;164.5583054;306.4770961;284.0090648;286.4246469;232.2488286;279.5156104
CSV;
        $lines = explode("\n", $csvData);
        $aspects_wanted = EmptyDistribs::emptyDoubleDistrib_triangle_dim2(self::$study->config['planets'], self::$study->config['planets'], 360);
        $interaspects_wanted = EmptyDistribs::emptyDoubleDistrib_square_dim2(self::$study->config['planets'], self::$study->config['planets'], 360);
        $nPlanets = count(self::$study->config['planets']);
        for($i=0; $i < count($lines); $i+=3){
            $b = $lines[$i+1];
            $d = $lines[$i+2];
            $fields_b = explode(';', $b);
            $fields_d = explode(';', $d);
            // remove year
            array_shift($fields_b);
            array_shift($fields_d);
            // works only if planet database contains the same fields as self::$study->config['planets']
            $fields_b = array_combine(self::$study->config['planets'], $fields_b);
            $fields_d = array_combine(self::$study->config['planets'], $fields_d);
            // aspects
            for($i=0; $i < $nPlanets; $i++){
                for($j=$i+1; $j < $nPlanets; $j++){
                    $planet1 = self::$study->config['planets'][$i];
                    $planet2 = self::$study->config['planets'][$j];
                    $code = "$planet1-$planet2";
                    $lg1 = floor($fields_b[$planet1]);
                    $lg2 = floor($fields_b[$planet2]);
echo "$code\n";
echo "$lg1\n";
echo "$lg2\n";
exit;
                }
            }
            break;
        }
exit;
        return [$aspects_wanted, $interaspects_wanted];
    } */
    
    
}// end class
