<?php
/******************************************************************************

    Functional test for src/studies/death_fr/import.php
    
    Uses study1 - see config/test/study1-README 
    
    usage: phpunit src/commands/death_fr/importTest.php 
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-03-25 21:16:34+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\test\functional\studies\death_fr;

use PHPUnit\Framework\TestCase;
use observe\model\Observe;
use observe\model\IStudy;
use observe\studies\death_fr\Death_fr;
use observe\studies\death_fr\import;

class importTest extends TestCase {
    
    private static IStudy $study;
    
    public static function setUpBeforeClass(): void {
        self::$study = new Death_fr('study1');
        import::execute(self::$study, []);
    }
    
    public function testFileExistence(){
        $this->assertTrue(is_file(self::$study->getDatafile()));
    }
    
    public function testFileContents(){
        $births_bz2 = [];
        $deaths_bz2 = [];
        $fileHandle = fopen('compress.bzip2://' . self::$study->getDatafile(), 'r');
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
    
}// end class
