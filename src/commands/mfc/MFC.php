<?php
/******************************************************************************
    Common code for MFC (Mother Father Child) output generation.
    
    @license    GPL
    @history    2021-01-30 06:23:48+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

class MFC {
    
    const M = 'Mother';
    const F = 'Father';
    const W = 'Wedding';
    const C = 'Child';
    
    const LABELS = [
        'M' => 'Mother',
        'F' => 'Father',
        'C' => 'Child',
        'W' => 'Wedding',
    ];
    
    /**
        Computes the possible combinations of members in a MFCW experience.
        @param  $wedding    Include wedding in couple computation ?
    **/
    public static function computeCouples(bool $wedding) {
        $members = ['M', 'F', 'C'];
        if($wedding){
            $members[] = 'W';
        }
        $couples = [];
        for($i=0; $i < count($members); $i++){
            for($j=$i+1; $j < count($members); $j++){
                $couples[] = [$members[$i], $members[$j]];
            }
        }
        return $couples;
    }
    
    // ******************************************************
    /**
        @param  $params Parameters passed to mfc\pages\all
        @return array of arrays containing 2 elements (href and label)
                ex: [
                        ['index.html', 'a00 - Births in France, year 2000'],
                        ['mother.html', 'Mother'],
                        ...
                    ]
    **/
    public static function nav(&$params) {
        $tmp = self::LABELS;
        if(!$params['experience']['has-wedding']){
            unset($tmp['W']);
        }
        $members = array_keys($tmp);
        $couples = self::computeCouples($params['experience']['has-wedding']);
        foreach($couples as $couple){
        }
        $res = [];
// TODO put url in config
        $res[] = ['https://g5.tig12.net', 'g5.tig12.net'];
        $res[] = ['index.html', $params['experience']['title']];
        foreach($members as $member){
            $href = strToLower(self::LABELS[$member]) . '.html';
            $label = self::LABELS[$member];
            $res[] = [$href, $label];
        }
        $res[] = ['', '<center><hr style="width:80%;"></center>'];
        foreach($couples as [$member1, $member2]){
            $href = strToLower(self::LABELS[$member1]) . '-' . strToLower(self::LABELS[$member2]) . '.html';
            $label = self::LABELS[$member1] . ' - ' . self::LABELS[$member2];
            $res[] = [$href, $label];
        }
        $res[] = ['', '<center><hr style="width:80%;"></center>'];
        $res[] = ['distrib', 'CSV distributions'];
        return $res;
    }
    
    
} // end class
