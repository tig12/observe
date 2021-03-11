<?php
/******************************************************************************
    Computes distributions of planets and individual aspects
    
    @license    GPL
    @history    2021-03-10 04:31:53+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\mfc\distrib;

class planets {
    
    // ******************************************************
    /**
        @param $data    Associative array with main keys 'M', 'F', 'C' and optionnaly 'W'.
                        Main values contains an array of associative arrays containing planet coordinates.
                        Ex : [
                            'M' =>
                                0 => [
                                    'SO' => 302.524,
                                    'MO' => 49.212,
                                    ...
                                ],
                                ...
                                NNNN => [...],
                            ],
                            'F' => [ ... ],
                            'C' => [ ... ],
                            'W' => [ ... ],
                        ]
        @param  $processW   Should wedding distributions be computed ?
        @return The YMD distributions in associative arrays: [
                    'M' => [
                        'SO' => [ 0 => 1273, ... 359 => 1324 ],
                        'MO' => [ 0 => 1142, ... 359 => 1154 ],
                    ],
                    'F' => [ ... ],
                    'C' => [ ... ],
                    'W' => [ ... ],
                ]

    **/
    public static function computeDistrib(
        &$data,
        bool $processW,
        bool $verbose = false,
    ){
        //
        // initialize
        //
        $memberKeys = [ 'M', 'F', 'C'];
        if($processW){
            $memberKeys[] = 'W';
            $planet0 = array_keys($data['M'][0])[0];
        }
        $allDegrees = array_fill_keys(range(0, 359), 0); // [0 => 0, 1 => 0, ... 359 => 0]
        // M, F, C, W must all have the same planets => array_keys($data['M'][0]) ok.
        $planets = array_fill_keys(array_keys($data['M'][0]), $allDegrees);
        $res = array_fill_keys($memberKeys, $planets);
        //
        // compute
        //
        foreach(['M', 'F', 'C'] as $memberKey){
            if($verbose){ echo "Compute $memberKey...\n"; }
            foreach($data[$memberKey] as $line){
                foreach($line as $planet => $lg){
                    $res[$memberKey][$planet][floor($lg)]++; // HERE floor() => 0 - 359
                }
            }
        }
        if($processW){
            // repeat algo repeated for perf
            foreach(['W'] as $memberKey){
                if($verbose){ echo "Compute $memberKey...\n"; }
                foreach($data[$memberKey] as $line){
                    foreach($line as $planet => $lg){
                        if($line[$planet0] == ''){
                            continue;
                        }
                        $res[$memberKey][$planet][floor($lg)]++; // HERE floor() => 0 - 359
                    }
                }
            }
        }
//echo "\n<pre>"; print_r($res); echo "</pre>\n"; exit;
//exit;
        return $res;
    }
    
} // end class
