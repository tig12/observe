<?php
/******************************************************************************
    Generates a jpg image containing a table representing a comparison of two distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-13 20:07:36+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tigdraw;

class castille {
    
    const array DEFULT_PARAMS = [
        'svg-separate'  => true,
        'title'         => '',
        'cell-size'     => 2,
    ];
    
    private static array $colors;
    
    /**
        @param  $
    **/
    public static function handleParams(array $params): array {
        return array_merge(self::DEFULT_PARAMS, $params);
    }
    
    public static function jpg(array $data, array $params): \GDImage {
        $params = self::handleParams($params);
        //
        // main characteristics of data
        //
        $N = count($data[0]);   // nb of columns
        $M = count($data);      // nb of lines
        //
        // main characteristics of image
        //
        $h = $M * $params['cell-size']; // height of the image
        $w = $N * $params['cell-size']; // width of the image
        //
        // Draw
        //
        $img = imagecreatetruecolor($w, $h);
        $colors = self::prepareColors($img);
        $y = 0;
        foreach($data as $row){
            $x = 0;
            foreach($row as $value){
                $color = self::getColor($value);
                imagefilledrectangle($img, $x, $y, $x + $params['cell-size'], $y + $params['cell-size'], $color);
                $x += $params['cell-size'];
            }
            $y += $params['cell-size'];
        }
        return $img;
    }
    
    /**
        Associates a percentage to a color
    **/
    public static function getColor($percent): int {
        $sign = ($percent > 0 ? 'plus' : 'minus');
        $abs = abs($percent);
        if($abs < 5){
            return self::$colors['white'];
        }
        $idx = 0;
        if($abs < 50) {
            $idx = 0;
        }
        if($abs < 100) {
            $idx = 1;
        }
        if($abs < 150) {
            $idx = 2;
        }
        $idx = 3;
        return self::$colors[$sign][$idx];
    }
    
    /**
        Fills self::$colors
    **/
    private static function prepareColors(\GDImage $img): void {
        self::$colors = [
            'white' => imagecolorallocate($img, 255, 255, 255),
            'plus' => [
                imagecolorallocate($img, 255, 201, 201),
                imagecolorallocate($img, 255, 148, 148),
                imagecolorallocate($img, 255, 94, 94),
                imagecolorallocate($img, 255, 40, 40),
            ],
            'minus' => [
                imagecolorallocate($img, 201, 201, 255),
                imagecolorallocate($img, 148, 148, 255),
                imagecolorallocate($img, 94, 94, 255),
                imagecolorallocate($img, 40, 40, 255),
            ],
        ];
    }
    
    
} // end class
