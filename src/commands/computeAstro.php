<?php
/******************************************************************************

    @license    GPL
    @history    2020-12-17 21:31:38+01:00, Thierry Graff : Creation
********************************************************************************/
namespace distrib\commands;

use distrib\Distrib;
use distrib\patterns\Command;
use distrib\DistribException;
use tiglib\arrays\csvAssociative;
use swetest\Sweph;
use swetest\SolarSystemConstants;

class ComputeAstro implements Command {
    
    /** 
        Match between constants used by swetest and
        International Astrological Abbreviations (IAA) for planets,
        as found in journal "Correlation" (vol 30.2 2016)
    **/
    const SWEPH_IAA = [
        SolarSystemConstants::SUN           => 'SO',
        SolarSystemConstants::MOON          => 'MO',
        SolarSystemConstants::MERCURY       => 'ME',
        SolarSystemConstants::VENUS         => 'VE',
        SolarSystemConstants::MARS          => 'MA',
        SolarSystemConstants::JUPITER       => 'JU',
        SolarSystemConstants::SATURN        => 'SA',
        SolarSystemConstants::URANUS        => 'UR',
        SolarSystemConstants::NEPTUNE       => 'NE',
        SolarSystemConstants::PLUTO         => 'PL',
        SolarSystemConstants::NORTH_NODE    => 'NN',
        SolarSystemConstants::SOUTH_NODE    => 'SN',
    ];
    
    private static $IAA_SWEPH;
    
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = 'ComputeAstro'; // TODO copute by reflection
        if(!isset($params['input-file'])){
            throw new DistribException("$classname needs a parameter 'input-file'");
        }
        //
        $infile = $params['input-file'];
        if(!is_file($infile)){
            throw new DistribException("File not found : $infile");
        }
        //
        if(!isset($params['actions'])){
            throw new DistribException("$classname needs a parameter 'actions'");
        }
        $actions = self::computeActions($params['actions']);
        //
        if(!isset($params['output-file'])){
            throw new DistribException("$classname needs a parameter 'output-file'");
        }
        $outfile = $params['output-file'];
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            throw new DistribException("Create directory '$dir' and try again");
        }
        //
        //  execute
        //
echo "\n"; print_r($actions); echo "\n";
exit;
        $outcols = [];
        foreach($actions as $action){
            $outcols[] = $action['out-col'];
        }
        //
        $res = implode(Distrib::CSV_SEP, $outcols) . "\n";
        $in = csvAssociative::compute($infile);
        //
        $Nactions = count($actions);
        $N =0;
        foreach($in as $old){
            $new = array_fill_keys($outcols, '');
            for($i=0; $i < $Nactions; $i++){
                $new = array_merge(
                    $new,
                    $actions[$i]['method']->invoke(
                        null,
                        $old,
                        $actions[$i]['in-col'],
                        $actions[$i]['out-col'],
                ));
            }
            $res .= implode(Distrib::CSV_SEP, $new) . "\n";
            $N++;
            if($N % 100000 == 0) echo "$N\n";
        }
        file_put_contents($outfile, $res);
        echo "Wrote $N lines in $outfile\n";
    }
    
    /**
        Parses lines expressing actions, like
            planets C SO MO ME VE MA JU SA UR NE PL NN SN
        The first word is the method name
        The second word is the name of column of input file (ISO 8601 date)
        Following words are IAA codes of astrological factors to compute
    **/
    private static function computeActions($param){
        $res = [];
        foreach($param as $line){
            $action = [];
            $tmp = preg_split('/\s+/', $line);
            if(count($tmp) < 3){
                throw new DistribException("Invalid syntax : $line");
            }
            $action['method-name'] = array_shift($tmp);
            $action['in-col'] = array_shift($tmp);
            $action['astro'] = $tmp;
            try{
                $method = new \ReflectionMethod(__CLASS__ . '::' . $action['method-name']);
            }
            catch(\ReflectionException $e){
                throw new DistribException("Invalid method name '{$action['method-name']}' in line : $line");
            }
            $method->setAccessible(true);
            $action['method'] = $method;
            $res[] = $action;
        }
        return $res;
    }
    
    /** 
    **/
    private static function planets($inLine, $inCols, $outCol){
    }
    
}// end class
