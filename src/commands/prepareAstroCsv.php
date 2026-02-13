<?php
/******************************************************************************
    Computes planetary positions for a date range.
    
    Parameters defined in commands/prepare.yml, in section "planets-csv"
    
    Stores the results in var/tmp/planets/
    
    @license    GPL
    @history    2026-02-10 20:16:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands;

use observe\app\Command;
use observe\app\Observe;
use observe\app\ObserveException;
use tigeph\ephem\meeus1\Meeus1;
use tigeph\model\IAA;

class prepareAstroCsv implements Command {
        
    public static function execute($params=[]){
        //
        // Check parameters
        //
        $msg = "Usage: php run-observe.php prepare planets <date or date range>\n"
            . "Ex: php run-observe.php prepare planets 1970\n"
            . "    php run-observe.php prepare planets 1970-1985\n";
        // optional parameter to specify dates to compute
        if(empty($params[Observe::PARAM_OPTIONAL_STRING])){
            echo "MISSING PARAMETER: you must specify date range\n$msg";
            return;
        }
        if(count($params[Observe::PARAM_OPTIONAL_STRING]) > 1){
            $useless = $params[Observe::PARAM_OPTIONAL_STRING][1];
            echo "USELESS PARAMETER: $useless\n$msg";
            return;
        }
        // tmp dir
        if(!isset($params['tmp-dir'])){
            echo "MISSING 'tmp-dir' PARAMETER IN COMMAND FILE\n"
                . "Add this parameter in commands/prepare.yml.\n";
            return;
        }
        if(!isset($params['tmp-subdir'])){
            echo "MISSING 'tmp-subdir' PARAMETER IN COMMAND FILE\n"
                . "Add this parameter in commands/prepare.yml.\n";
            return;
        }
        $dir = $params['tmp-dir'] . DS . $params['tmp-subdir'];
        if(!is_dir($dir)){
            echo "ERROR directory '$dir' does not exist\n"
                . "Create this directory before executing this command.\n";
            return;
        }
        //
        // compute $years
        //
        $str_range = $params[Observe::PARAM_OPTIONAL_STRING][0];
        $years = [];
        $p_year = '/^\d{4}$/';
        $p_range = '/^\d{4}-\d{4}$/';
        preg_match($p_year, $str_range, $m);
        if(count($m) == 1){
            $years[] = $m[0];
        }
        else {
            preg_match($p_range, $str_range, $m);
            if(count($m) == 1){
                $from = substr($m[0], 0, 4);
                $to = substr($m[0], 5);
                // here, should check:
                // - that $from < $to
                // - that $from >= min(available years)
                // - that $to >= max(available years)
                // - that all dates between $from and $to correspond to existing dates
                // not done because it's a build command, executed by a person supposed to be careful
                $years = range($from, $to);
            }
            else {
                echo "INVALID PARAMETER: {$str_range}\n$msg";
                return;
            }
        }
        //
        // compute planet positions
        //
        // TODO put $planets in command file
        $planets = ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SA', 'UR', 'NE', 'PL', 'NN'];
        $tigephPlanets = array_values(array_intersect_key(IAA::IAA_TIGEPH, array_flip($planets))); // Convert to constants of SolarSystemC
        //
        $file_header = 'DAY' . Observe::CSV_SEP . implode(Observe::CSV_SEP, $planets) . "\n"; 
        foreach($years as $year){
            $file = $dir . DS . $year . '.csv';
            $contents = $file_header;
            $days = self::listDays($year);
            foreach($days as $day){
                $datetime = $day . ' 12:00:00';
                // note: $coords is an associative array with keys expressed with constants of SolarSystemC
                // but $planets and $tigephPlanets share the same order, so it's no use to convert back to IAA keys
                $coords = Meeus1::ephem($datetime, $tigephPlanets)['planets'];
                $contents .= $day . Observe::CSV_SEP . implode(Observe::CSV_SEP, $coords) . "\n";
            }
            file_put_contents($file, $contents);
            echo "Generated $file\n";
        }
    }

    /**
        Returns a list of days YYYY-MM-DD of a given year.
        @param  $year   ex: 1985
    **/
    private static function listDays($year) {
        $res = [];
        $start = new \DateTime("$year-01-01");
        $end   = (new \DateTime("$year-12-31"))->modify('+1 day');
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        foreach ($period as $date) {
            $res[] = $date->format('Y-m-d');
        }
        return $res;
    }
    
    
}// end class
