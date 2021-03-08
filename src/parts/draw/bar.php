<?php
/******************************************************************************
    Generates SVG horizontal bar chart from a distribution
    
    @license    GPL
    @history    2021-02-28 23:08:04+01:00, Thierry Graff : refactor, moved from commands to parts
    @history    2020-12-20 18:48:55+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\draw;

use observe\parts\stats\distrib;

class bar {
    
    
    /** 
        Returns the svg markup of a distribution.
        Layout : the image is composed of {legends ang gaps} and a bar area (containing only the bars).
        Bar area height is imposed (parameter $barH) ; bar width is computed.
        Image total height and width ($w and $h) are computed (= bar size + lengends and gaps).
        @param  $data               The data to represent.
                                    Must be an associative array.
                                    keys = x, values on the x axis.
                                    values = y, corresponding values on the y axis, = nb of occurences of x in the distribution.
        // image, general
        @param  $hGap               in px - horizontal (left and right) gap of the image.
        @param  $vGap               in px - vertical (left and right) gap of the image.
        @param  $background         Background color of the image.
        // title
        @param  $title              Title to display on the image.
        @param  $titleH             in px - height of the title (= font size).
        @param  $titleBottomGap     in px - gap between the title and bar area.
                                    Set to 0 if title = empty string.
        // bar
        @param  $barAreaH           in px - height of the bar area.
        @param  $barW               in px - width of each vertical bar.
        @param  $barGap             in px - space between 2 vertical bars.
        @param  $barColor           Color of the vertical bars.
        @param  $barHover           If true, a tooltip with (key, value) is displayed on mouse hover
        // x and y axis
        @param  $xAxis              boolean - draw x axis ?
        @param  $xAxisStyle         Style to draw the line of x axis
        @param  $yAxis              boolean - draw y axis ?
        @param  $yAxisStyle         Style to draw the line of y axis
        // x and y legends
        @param  $xlegends           Text to write below the x axis.
                                    TODO explain syntax
        @param  $xlegendsH          in px - height of x legends (= font size)
        @param  $xlegendsTopGap     in px - gap between x axis and x legends
                                    Set to 0 if no x legends.
        @param  $ylegends           Text to write left of the y axis.
                                    TODO explain syntax
        @param  $ylegendsW          in px - width of y legends.
        @param  $ylegendsH          in px - height of y legends (= font size)
        @param  $ylegendsRightGap   in px - gap between y legends and y axis.
        @param  $ylegendsRound      Nb of decimal to include in the displayed values.
                                    (applies to mean)
        
    **/
    public static function svg(
            $data = [],
            // image, general
            $hGap = 25,
            $vGap = 15,
            $background = 'moccasin',
            // title
            $title = '',
            $titleH = 22,
            $titleBottomGap = 15,
            // bar
            $barAreaH = 250,
            $barW = 2,
            $barGap = 1,
            $barColor = 'slategray',
            $barHover = true,
            // x and y axis
            $xAxis = true,
            $xAxisStyle = 'stroke:black;stroke-width:1;',
            $yAxis = true,
            $yAxisStyle = 'stroke:black;stroke-width:1;',
            // x and y legends
            $xlegends = [],
            $xlegendsH = 12,
            $xlegendsTopGap = 5,
            //
            $ylegends = [],
            $ylegendsW = 40,
            $ylegendsH = 12,
            $ylegendsRightGap = 5,
            $ylegendsRound = 0,
        ){
        $res = '';
        // characteristics of data
        $dataKeys = array_keys($data);
        [$min, $max] = [min($data), max($data)];
        $maxMin = $max - $min;
        $N = count($data);
        //
        if($title == ''){
            $titleH = 0;
            $titleBottomGap = 0;
        }
        if(empty($xlegends)){
            $xlegendsH = 0;
            $xlegendsTopGap = 0;
        }
        if(empty($ylegends)){
            $ylegendsW = 0;
            $ylegendsRightGap = 0;
        }
        //
        // general variables for drawing
        // 
        // $xBegin, $xEnd, $yBegin, $yEnd = coordinates of top-left and bottom-right of the bar area
        $xBegin = $hGap + $ylegendsW + $ylegendsRightGap;
        $yBegin = $vGap + $titleH + $titleBottomGap;
        $barAreaW = $N * $barW + ($N-1) * $barGap;
        // $barAreaH given in parameter
        $xEnd = $xBegin + $barAreaW;
        $yEnd = $yBegin + $barAreaH;
        //
        $deltaY = $yEnd - $yBegin;
        // $h, $w = size of the image
        $w = $xEnd + $hGap;
        $h = $yEnd + $xlegendsTopGap + $xlegendsH + $vGap;
        //
        $barDelta = $barW + $barGap; 
        $barStyle = "stroke:$barColor;stroke-width:$barW;";
        //
        //
        //
        $svgStyle = "";
        $res .= "<svg width=\"$w\" height=\"$h\" style=\"$svgStyle\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" version=\"1.1\">\n";
        $res .= "<rect width=\"100%\" height=\"100%\" fill=\"$background\" />\n"; // hack for bg color 
        //
        // title
        //
        [$x, $y] = [$hGap, $vGap + $titleH];
        $text = $title;
        $res .= "<text x=\"$x\" y=\"$y\" style=\"text-anchor:left; font-weight:bold; font-size:{$titleH}px;\">$text</text>\n";
        //
        // axis
        //
        if($xAxis){
            [$x1, $y1] = [$xBegin, $yEnd];
            [$x2, $y2] = [$xEnd, $yEnd];
            $res .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" style=\"$xAxisStyle\" />\n";
        }
        if($yAxis){
            [$x1, $y1] = [$xBegin, $yBegin];
            [$x2, $y2] = [$xBegin, $yEnd];
            $res .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" style=\"$yAxisStyle\" />\n";
        }
        //
        // bars
        //
        $i = 0;
        foreach($data as $key => $val){
            $x1 = $xBegin + ($i)*$barGap + $i*$barW;
            $y1 = $yEnd;
            $x2 = $x1;
            $y = ($val-$min) * $deltaY / $maxMin;
            $y2 = $yEnd - $y;
            if($barHover === true){
                $res .= "<g><title>$key: $val</title>\n";
            }
            $res .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" style=\"$barStyle\" />\n";
            if($barHover === true){
                $res .= "</g>\n";
            }
            $i++;
        }
        //
        // x legend
        //
        if(!empty($xlegends)){
            $y = $yEnd + $xlegendsTopGap + $xlegendsH;
            $xlegendsStyle = "text-anchor:middle; font-size:{$xlegendsH}px;";
            if(in_array('min', $xlegends)){
                $x = $xBegin;
                $text = $dataKeys[0];
                $res .= "<text x=\"$x\" y=\"$y\" style=\"$xlegendsStyle\">$text</text>\n";
            }
            if(in_array('max', $xlegends)){
                $x = $xBegin + $barAreaW;
                $text = $dataKeys[count($dataKeys)-1];
                $res .= "<text x=\"$x\" y=\"$y\" style=\"$xlegendsStyle\">$text</text>\n";
            }
            if(in_array('top', $xlegends)){
                [$top, $place] = self::compute_top($data);
                $x = $xBegin + ($place-1)*$barGap + $place*$barW;
                $text = $top;
                $res .= "<text x=\"$x\" y=\"$y\" style=\"$xlegendsStyle\">$text</text>\n";
            }
        }
        //
        // y legend
        //
        if(!empty($ylegends)){
            $x = $vGap + $ylegendsW;
            $xlegendsStyle = "text-anchor:end; dominant-baseline:middle; font-size:{$ylegendsH}px;";
            if(!empty($ylegends)){
                if(in_array('min', $ylegends)){
                    $y = $yEnd;
                    $text = $min;
                    $res .= "<text x=\"$x\" y=\"$y\" style=\"$xlegendsStyle\">$text</text>\n";
                }
                if(in_array('max', $ylegends)){
                    $y = $yBegin;
                    $text = $max;
                    $res .= "<text x=\"$x\" y=\"$y\" style=\"$xlegendsStyle\">$text</text>\n";
                }
                if(in_array('mean', $ylegends)){
                    $mean = distrib::mean($data);
                    $y = $yBegin + $deltaY*($max-$mean)/$maxMin;
                    $text = round($mean, $ylegendsRound);
                    $res .= "<text x=\"$x\" y=\"$y\" style=\"$xlegendsStyle\">$text</text>\n";
                }
            }
        }
        //
        $res .= "</svg>\n";
        return $res;
    }
    
    // ******************************************************
    /**
        Computes the "top key".
        In key / value array $data, means the key having the highest value.
        Returns an array with 2 elements :
            - the top key
            - the place of this key in the array (0 = first keyof the array...)
    **/
    private static function compute_top(&$data) {
        $max = max($data);
        $place = 0;
        foreach($data as $k => $v){
            if($v == $max){
                $top = $k;
                break;
            }
            $place++;
        }
        return [$top, $place];
    }
    
    
} // end class
