<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\Studies;

class splitTest extends TestCase{

    /*
    The original data contains the 10 first lines of deces-1994.txt
    (curiously, not dead in 1994 - but after check, this corresponds to deces-1994.txt
    select bday,dday,(julianday(dday)-julianday(bday)) from person;
    +------------+------------+-----------------------------------+
    |    bday    |    dday    | (julianday(dday)-julianday(bday)) |
    +------------+------------+-----------------------------------+
    | 1906-09-11 | 1991-12-31 | 31157.0                           |
    | 1903-03-20 | 1991-12-31 | 32428.0                           |
    | 1905-10-03 | 1992-01-01 | 31501.0                           |
    | 1908-02-08 | 1992-01-01 | 30643.0                           |
    | 1942-03-02 | 1992-01-01 | 18202.0                           |
    | 1902-04-19 | 1992-01-05 | 32768.0                           |
    | 1904-05-14 | 1992-01-01 | 32008.0                           |
    | 1992-01-02 | 1992-01-04 | 2.0                               |
    | 1952-11-01 | 1992-01-06 | 14310.0                           |
    | 1932-07-07 | 1992-01-06 | 21732.0                           |
    +------------+------------+-----------------------------------+
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
        split::execute($studyConfig, ['full']);
        $filename = 'compress.bzip2://' . $studyConfig['working-dir'] . DS . 'split-full' . DS . '01--0-200years' . DS . 'data.csv.bz2';
        $births_bz2 = [];
        $deaths_bz2 = [];
        $fileHandle = fopen($filename, 'r');
        while(false !== $line = fgets($fileHandle)){
            $fields = explode(Observe::CSV_SEP, trim($line));
            $births_bz2[] = $fields[0];
            $deaths_bz2[] = $fields[1];
        }
        fclose($fileHandle);
        $births_db = [
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
        $deaths_db = [
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
        $this->assertEquals($births_bz2, $births_db);
        $this->assertEquals($deaths_bz2, $deaths_db);
    }
    
    public function testStudy1_age(){
        $studyConfig = $this->loadStudy1();
        split::execute($studyConfig, ['age']);
        $this->assertEquals(1, 1);
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
        $glob = glob($studyConfig['working-dir'] . DS . 'split-age' .DS . '*');
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
        // wanted births and deaths can be computed independantlly of the execution
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
