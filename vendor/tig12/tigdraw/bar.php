<?php
/******************************************************************************
    Generates SVG horizontal bar chart from a distribution.
    A distribution is an associative array, see param $data. 
        
    @license    GPL
    @history    2021-02-28 23:08:04+01:00, Thierry Graff : refactor, moved from commands to parts
    @history    2020-12-20 18:48:55+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tigdraw;

class bar {
    
    /** 
        Returns the svg markup of a distribution (one distribution only).
        
        Layout : the image is composed of legends, gaps and a bar area (containing only the bars).
        Bar area height is imposed (parameter $barH) ; bar width is computed.
        Image total height and width ($w and $h) are computed (= bar size + lengends and gaps).
    **/
    public static function svg(
        
        /** 
            $data is the data to represent.
            Must be an associative array.
            keys = x, values on the x axis.
            values = y, corresponding values on the y axis, = nb of occurences of x in the distribution.
        **/
        //
        //
        array   $data = [],
        //
        //
        //
        // image, general
        //
        /**
            $svg_separate:      Should generated markup to be saved in a separate .svg file or directly included in a html page?
                                (Changes the markup of the header)
        **/
        bool    $svg_separate,
        /** $hGap               in px - horizontal (left and right) gap of the image. **/
        int     $hGap = 25,
        /** $vGap               in px - vertical (top and bottom) gap of the image. **/
        int     $vGap = 15,
        /** $background         Background color of the image. **/
        string  $background = 'moccasin',
        //
        // title
        //
        /** $title              Title to display on the image. **/
        string  $title = '',
        /** $titleH             in px - height of the title (= font size). **/
        int     $titleH = 22,
        /** $titleBottomGap     in px - gap between the title and bar area. Set to 0 if title = empty string. **/
        int     $titleBottomGap = 15,
        //
        // bar
        //
        /** $drawArea           in px - height of the bar area. **/
        int     $drawArea = 250,
        /** $barW               in px - width of each vertical bar. **/
        int     $barW = 2,
        /** $barGap             in px - space between 2 vertical bars. **/
        int     $barGap = 1,
        /** $barColor           Color of the vertical bars. **/
        string  $barColor = 'slategray',
        /** $barHover           If true, a tooltip with (key, value) is displayed on mouse hover. **/
        bool    $barHover = true,
        //
        // x and y axis
        //
        /** $xAxis              draw x axis ? **/
        bool    $xAxis = true,
        /** $xAxisStyle         Style to draw the line of x axis **/
        string  $xAxisStyle = 'stroke:black;stroke-width:1;',
        /** $yAxis              boolean - draw y axis ? **/
        bool    $yAxis = true,
        /** $yAxisStyle         Style to draw the line of y axis **/
        string  $yAxisStyle = 'stroke:black;stroke-width:1;',
        //
        // x legends
        //
        /** $xlegends           Array of (x value, label) **/
        array   $xlegends = [],
        /** $xlegendsH          in px - height of x legends (= font size) **/
        int     $xlegendsH = 12,
        /** $xlegendsTopGap     in px - gap between x axis and x legends. Set to 0 if no x legends. **/
        int     $xlegendsTopGap = 5,
        //
        // y legends
        //
        /** $ylegends           Array of (y value, label) **/
        array   $ylegends = [],
        /** $ylegendsW          in px - width of y legends. **/
        int     $ylegendsW = 40,
        /** $ylegendsH          in px - height of y legends (= font size) **/
        int     $ylegendsH = 12,
        /** $ylegendsRightGap   in px - gap between y legends and y axis. **/
        int     $ylegendsRightGap = 5,
        /**
            $ylegendsRound      Nb of decimal to include in the displayed values.
                                (meaningful for mean, whidh is generally not integer)
        **/
        int     $ylegendsRound = 0,
        //
        // bottom
        //
        /** $bottom             Content to put below x-legends **/
        string  $bottom = '',
        
        //
        // other
        //
        /** 
            $stats              Associative array containing statistical informations about the distribution.
                                Possible keys:
                                    - mean: mean y value.
                                    - top-key: value of x corresponding to y max.
                                    - top-key-index: rank in the $data array containing top-key.
        **/
        array   $stats = [],
        /**  $meanLine          Draw horizontal line for mean ? - Only if $ylegends contain 'mean' **/
        bool    $meanLine = false,
        /**$meanLineStyle       Style for mean line **/
        ): string {
    
        $svg = '';
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
        $drawAreaW = $N * $barW + ($N-1) * $barGap;
        // $drawArea given in parameter
        $xEnd = $xBegin + $drawAreaW;
        $yEnd = $yBegin + $drawArea;
        //
        $deltaY = $yEnd - $yBegin;
        // $h, $w = size of the image
        $w = $xEnd + $hGap;
        $h = $yEnd + $xlegendsTopGap + $xlegendsH + $vGap;
        //
        $barDelta = $barW + $barGap; 
        //
        //
        //
        $style = <<<SVG
<style type="text/css"><![CDATA[
.bl { /* bar line */
    stroke:$barColor;
    stroke-width:$barW;
}
.title{
    text-anchor:left;
    font-weight:bold;
    font-size:{$titleH}px;
}
.xAxis{{$xAxisStyle}}
.yAxis{{$yAxisStyle}}
.xLegends{
    text-anchor:middle;
    font-size:{$xlegendsH}px;
}
.yLegends{
    text-anchor:end;
    dominant-baseline:middle;
    font-size:{$ylegendsH}px;
}
.meanLine{
    stroke:black;
    stroke-dasharray:5,20;
}
]]></style>

SVG;
        $svg .= svg::header(
            separate: $svg_separate,
            width: $w,
            height: $h,
        );
        $svg .= $style;
        $svg .= "<rect width=\"100%\" height=\"100%\" fill=\"$background\" />\n"; // hack for bg color 
        //
        // title
        //
        [$x, $y] = [$hGap, $vGap + $titleH];
        $svg .= "<text x=\"$x\" y=\"$y\" class=\"title\">$title</text>\n";
        //
        // axis
        //
        if($xAxis){
            [$x1, $y1] = [$xBegin, $yEnd];
            [$x2, $y2] = [$xEnd, $yEnd];
            $svg .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" class=\"xAxis\" />\n";
        }
        if($yAxis){
            [$x1, $y1] = [$xBegin, $yBegin];
            [$x2, $y2] = [$xBegin, $yEnd];
            $svg .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" class=\"yAxis\" />\n";
        }
        //
        // bars
        //
        $i = 0;
        $xlegendKeys = array_keys($xlegends);
        foreach($data as $key => $val){
            $x1 = $xBegin + $i*$barGap + ($i+0.5)*$barW;
            $y1 = $yEnd;
            $x2 = $x1;
            $y = round(($val-$min) * $deltaY / $maxMin, 1);
            $y2 = $yEnd - $y;
            if($barHover === true){
                $svg .= "<g><title>$key: $val</title>";
            }
            $svg .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" class=\"bl\" />";
            if($barHover === true){
                $svg .= '</g>';
            }
            $svg .= "\n";
            if(in_array($key, $xlegendKeys)){
                $svg .= "<text x=\"$x\" y=\"$y\" class=\"xLegends\">" . $xlegends[$key] . "</text>\n";
            }
        }
        //
        // x legend
        //
        if(!empty($xlegends)){
//print_r($xlegends);
//            $y = $yEnd + $xlegendsTopGap + $xlegendsH;
            foreach($xlegends as $value => $label){
//                    $svg .= "<text x=\"$x\" y=\"$y\" class=\"xLegends\">$key</text>\n";
//echo "$value => $label\n"; exit;
                // [$value, $label] = $xlegend;
                // $x = $xBegin + $i*$barGap + ($i+0.5)*$barW;
                // $i++;
            }
            
            $y = $yEnd + $xlegendsTopGap + $xlegendsH;
            // min
            $x = $xBegin;
            $text = $dataKeys[0];
            $svg .= "<text x=\"$x\" y=\"$y\" class=\"xLegends\">$text</text>\n";
            // max
            $x = $xBegin + $drawAreaW;
            $text = $dataKeys[count($dataKeys)-1];
            $svg .= "<text x=\"$x\" y=\"$y\" class=\"xLegends\">$text</text>\n";

/* 
                if(in_array('top', $xlegends)){
                    $x = $xBegin + ($stats['top-key-index']-1)*$barGap + $stats['top-key-index']*$barW;
                    $svg .= "<text x=\"$x\" y=\"$y\" class=\"xLegends\">{$stats['top-key']}</text>\n";
                }
*/
        }
        //
        // y legend
        //
        if(!empty($ylegends)){
            $x = $vGap + $ylegendsW;
            if(!empty($ylegends)){
                if(in_array('min', $ylegends)){
                    $y = $yEnd;
                    $svg .= "<text x=\"$x\" y=\"$y\" class=\"yLegends\">$min</text>\n";
                }
                if(in_array('max', $ylegends)){
                    $y = $yBegin;
                    $svg .= "<text x=\"$x\" y=\"$y\" class=\"yLegends\">$max</text>\n";
                }
            }
        }
        //
        // other
        //
        /* 
        if($meanLine){
            $y1 = $y2 = $yMean;
            $x1 = $xBegin;
            $x2 = $xEnd;
            $svg .= "<g fill=\"none\"><path class=\"meanLine\" d=\"M$x1 $y1 H$x2 $y2 Z\" /></g>\n";
        }
        */
        //
        $svg .= "</svg>\n";
        return $svg;
    }
    
} // end class
