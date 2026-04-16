<?php
/******************************************************************************
    Generates an image with a table representing a comparison of two distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-13 20:07:36+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tigdraw;

class castille {
    
    const array DEFAULT_PARAMS = [
        'padding'           => 15, // in px
        'cell-size'         => 2,  // in px
        //
        'title'             => '',
        'title-height'      => 40, // in px
        //
        'x-title'           => '', // describes what is represented in x
        'x-title-height'    => 7, // in px
        'x-legends'         => [], // x values
        'x-legends-height'  => 20, // in px
        //
        'y-title'           => '', // describes what is represented in y
        'y-title-width'     => 15, // in px
        'y-legends'         => [], // y values
        'y-legends-width'   => 40, // in px
    ];
    
    private static array $colors;
    
    /**
        @param  $
    **/
    private static function handleParams(array $params): array {
        //
        $res = array_merge(self::DEFAULT_PARAMS, $params);
        //
        if($res['title'] == ''){
            $res['title-height'] = 0;
        }
        if(empty($res['x-legends'])){
            $res['x-legends-height'] = 0;
        }
        if($res['x-title'] == ''){
            $res['-xtitle-height'] = 0;
        }
        if(empty($res['y-legends'])){
            $res['y-legends-width'] = 0;
        }
        if($res['y-title'] == ''){
            $res['y-title-width'] = 0;
        }
        return $res;
    }
    
    /** 
        Main function
    **/
    public static function image(array &$data, array $params): \GDImage {
        //
        $params = self::handleParams($params);
        //
        // main characteristics of data
        //
        $N = count($data[0]);   // nb of columns
        $M = count($data);      // nb of lines
        //
        // main characteristics of image
        //
        $tableWidth = $N * $params['cell-size'];
        $tableHeight = $M * $params['cell-size'];
        // $xBegin, $yBegin = coordinates of top-left of the table
        $xBegin = $params['padding'] + $params['y-title-width'] + $params['y-legends-width'];
        $yBegin = $params['padding'] + $params['title-height'] + $params['x-title-height'] + $params['x-legends-height'];
        // width of the image
        $w = $xBegin + $tableWidth + $params['padding'];
        // height of the image
        $h = $yBegin + $tableHeight + $params['padding'];
        //
        // Draw
        //
        $img = imagecreatetruecolor($w, $h);
        $colors = self::prepareColors($img);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $w-1, $h-1, self::$colors['plus'][0]); // white background
        //
        // title
        //
        if($params['title'] != ''){
            // approx computation for $x and $y => TODO handle correctly
            $x = $params['padding'] + 10;
            $y = $params['padding'];
            imagestring($img, 5, $x, $y, $params['title'], $black);
        }
        //
        // x-title
        //
        if($params['x-title'] != ''){
            // approx computation for $x and $y => TODO handle correctly
            $x = $xBegin;
            $y = $yBegin - $params['x-legends-height'] - 20;
            imagestring($img, 3, $x, $y, $params['x-title'], $black);
        }
        //
        // x-legends (on top of the table)
        //
        if(!empty($params['x-legends'])){
            foreach($params['x-legends'] as $k => $v){
                $x1 = $x2 = $xBegin + $v * $params['cell-size'];
                $y1 = $yBegin - 6;
                $y2 = $yBegin - 1;
                imageline($img, $x1, $y1, $x2, $y2, $black); // graduation
                // approx computation for $x and $y => TODO handle correctly
                $x = $x1 - 6;
                $y = $y1 - 15;
                imagestring($img, 2, $x, $y, $v, $black);
            }
        }
        //
        // y-title
        //
        if($params['y-title'] != ''){
            // approx computation for $x and $y => TODO handle correctly
            $x = $params['padding'];
            $y = $yBegin;
            $letters = str_split($params['y-title']);
            foreach($letters as $letter){
                imagestring($img, 3, $x, $y, $letter, $black);
                $y += 15;
            }
        }
        //
        // y-legends
        //
        if(!empty($params['y-legends'])){
            foreach($params['y-legends'] as $k => $v){
                $y1 = $y2 = $yBegin + $v * $params['cell-size'];
                $x1 = $xBegin - 6;
                $x2 = $xBegin - 1;
                imageline($img, $x1, $y1, $x2, $y2, $black); // graduation
                // approx computation for $x and $y => TODO handle correctly
                $x = $xBegin - 30;
                $y = $y1 - 6;
                imagestring($img, 2, $x, $y, $v, $black);
            }
        }
        //
        // Border around the table
        //
        $x1 = $xBegin - 1;
        $y1 = $yBegin - 1;
        $x2 = $xBegin + $tableWidth + 1;
        $y2 = $yBegin + $tableHeight + 1;
        imagerectangle($img, $x1, $y1, $x2, $y2, $black);
        //
        // Main loop - Draw the table
        //
        $y = $yBegin;
        foreach($data as $row){
            $x = $xBegin;
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
    private static function getColor($percent): int {
        $sign = ($percent > 0 ? 'plus' : 'minus');
        $abs = abs($percent);
        //
        $THRESHOLD = 5;
        $nColors = 9; // nb of colors for plus and minus (must be equal)
        //
        // the color changes every $THRESHOLD percent
        // if $percent is between 0 and $THRESHOLD, $idx = 0
        // if $percent is between $THRESHOLD and 2 * $THRESHOLD, $idx = 1 etc. until $nColors
        $idx = min(floor($abs / $THRESHOLD), $nColors);
        return self::$colors[$sign][$idx];
    }
    
    /**
        Fills self::$colors
    **/
    private static function prepareColors(\GDImage $img): void {
        self::$colors = [
            'plus' => [
                imagecolorallocate($img, 255, 255, 255), // white
                imagecolorallocate($img, 255, 228, 228),
                imagecolorallocate($img, 255, 201, 201),
                imagecolorallocate($img, 255, 174, 174),
                imagecolorallocate($img, 255, 148, 148),
                imagecolorallocate($img, 255, 121, 121),
                imagecolorallocate($img, 255, 94, 94),
                imagecolorallocate($img, 255, 67, 67),
                imagecolorallocate($img, 255, 40, 40),
                imagecolorallocate($img, 255, 13, 13), // dark red
            ],
            'minus' => [
                imagecolorallocate($img, 255, 255, 255), // white
                imagecolorallocate($img, 228, 228, 255),
                imagecolorallocate($img, 201, 201, 255),
                imagecolorallocate($img, 174, 174, 255),
                imagecolorallocate($img, 148, 148, 255),
                imagecolorallocate($img, 121, 121, 255),
                imagecolorallocate($img, 94, 94, 255),
                imagecolorallocate($img, 67, 67, 255),
                imagecolorallocate($img, 40, 40, 255),
                imagecolorallocate($img, 13, 13, 255), // dark blue
            ],
        ];
    }
    
    
} // end class
