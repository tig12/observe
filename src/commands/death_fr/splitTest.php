<?php
/******************************************************************************

    Functional test for src/command/death_fr/split.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use PHPUnit\Framework\TestCase;
use observe\commands\tests\Death_fr_tests;
use observe\model\Observe;
use observe\model\Studies;

class splitTest extends TestCase {
    
    private static array $studyConfig;

    public static function setUpBeforeClass(): void {
        self::$studyConfig = Death_fr_tests::loadStudy('study1/study1.yml');
    }
    
    public function testStudy1_full(){
        
        split::execute(self::$studyConfig, ['full']);
        
        $dir_wanted = implode(DS, [self::$studyConfig['working-dir'], 'split-full', '01--0-200years']);
        $this->assertTrue(is_dir($dir_wanted));
        
        $filename = 'compress.bzip2://' . $dir_wanted . DS . 'data.csv.bz2';
        $births_bz2 = [];
        $deaths_bz2 = [];
        $fileHandle = fopen($filename, 'r');
        while(false !== $line = fgets($fileHandle)){
            $fields = explode(Observe::CSV_SEP, trim($line));
            $births_bz2[] = $fields[0];
            $deaths_bz2[] = $fields[1];
        }
        fclose($fileHandle);
        $birth_wanted = [
            '1906-09-11',
            '1903-03-20',
            '1905-10-03',
            '1908-02-08',
            '1942-03-02',
            '1902-04-19',
            '1904-05-14',
            '1992-01-02',
            '1952-11-01',
            '1932-07-07',
        ];
        $deaths_wanted = [
            '1991-12-31',
            '1991-12-31',
            '1992-01-01',
            '1992-01-01',
            '1992-01-01',
            '1992-01-05',
            '1992-01-01',
            '1992-01-04',
            '1992-01-06',
            '1992-01-06',
        ];
        $this->assertEquals($births_bz2, $birth_wanted);
        $this->assertEquals($deaths_bz2, $deaths_wanted);
    }
    
    public function testStudy1_age(){
        
        split::execute(self::$studyConfig, ['age']);
        
        $dirs_wanted = [
            '01--0-2days',
            '02--2days-2months',
            '03--2months-6months',
            '04--6months-2years',
            '05--2years-5years',
            '06--5years-20years',
            '07--20years-50years',
            '08--50years-90years',
            '09--90years-200years',
        ];
        $glob = glob(self::$studyConfig['working-dir'] . DS . 'split-age' .DS . '*');
        $dirs_computed = array_map('basename', $glob);
        
        $this->assertEquals($dirs_computed, $dirs_wanted);
        
        $births_bz2 = [];
        $deaths_bz2 = [];
        $births_computed = [];
        $deaths_computed = [];
        foreach($glob as $dir){
            $subgroupName = basename($dir);
            $births_computed[$subgroupName] = [];
            $deaths_computed[$subgroupName] = [];
            $filename = 'compress.bzip2://' . $dir . DS . 'data.csv.bz2';
            $fileHandle = fopen($filename, 'r');
            while(false !== $line = fgets($fileHandle)){
                $fields = explode(Observe::CSV_SEP, trim($line));
                $births_computed[$subgroupName][] = $fields[0];
                $deaths_computed[$subgroupName][] = $fields[1];
            }
            fclose($fileHandle);
        }
        // wanted births and deaths can be computed independantly of the execution
        // from the limits of the ages, in Death_fr::SPLITS
        // '0'           => '0',
        // '2'           => '2days',
        // '60'          => '2months',
        // '182.625'     => '6months',
        // '730.5'       => '2years',
        // '1826.25'     => '5years',
        // '7305'        => '20years',
        // '18262.5'     => '50years',
        // '32872.5'     => '90years',
        // '54787.5'     => '200years',
        $births_wanted = [
            '01--0-2days' => [
            ],
            '02--2days-2months' => [
                '1992-01-02',
            ],
            '03--2months-6months' => [
            ],
            '04--6months-2years' => [
            ],
            '05--2years-5years' => [
            ],
            '06--5years-20years' => [
            ],
            '07--20years-50years' => [
                '1942-03-02',
                '1952-11-01',
            ],
            '08--50years-90years' => [
                '1906-09-11',
                '1903-03-20',
                '1905-10-03',
                '1908-02-08',
                '1902-04-19',
                '1904-05-14',
                '1932-07-07',
            ],
            '09--90years-200years' => [
            ],
        ];
        $deaths_wanted = [
            '01--0-2days' => [
            ],
            '02--2days-2months' => [
                '1992-01-04',
            ],
            '03--2months-6months' => [
            ],
            '04--6months-2years' => [
            ],
            '05--2years-5years' => [
            ],
            '06--5years-20years' => [
            ],
            '07--20years-50years' => [
                '1992-01-01',
                '1992-01-06',
            ],
            '08--50years-90years' => [
                '1991-12-31',
                '1991-12-31',
                '1992-01-01',
                '1992-01-01',
                '1992-01-05',
                '1992-01-01',
                '1992-01-06',
            ],
            '09--90years-200years' => [
            ],
        ];
        $this->assertEquals($births_computed, $births_wanted);
        $this->assertEquals($deaths_computed, $deaths_wanted);
    }
    
}// end class
