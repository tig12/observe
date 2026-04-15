<?php
/******************************************************************************
    Generates a SVG table representing a comparison of two distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-13 20:07:36+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tigdraw;

class castille {
    
    /**
    **/
    public static function svg(
        //
        // image, general
        //
        array   $data;
        /**
            $svg_separate:      Should generated markup to be saved in a separate .svg file or directly included in a html page?
                                (Changes the markup of the header)
        **/
        bool    $svg_separate = true,
        /** $drawArea           in px - height of the draw area. **/
        int     $drawArea = 250,
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
        /** $titleBottomGap     in px - gap between the title and draw area. Set to 0 if title = empty string. **/
        int     $titleBottomGap = 15,
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
        /**
            $ylegends           Indicates the text to write left of the y axis.
                                Associative array which can contain the following keys:
                                    - 'min': the lowest y value is displayed
                                    - 'max': the highest y value is displayed
                                    - 'mean': the (arithmetic) mean y value is displayed
        **/
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
        int     $ylegendsRound = 1,
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
                                    - top-key-index: rank in the $data_bar array containing top-key.
        **/
        array   $stats = [],
        /**  $meanLine          Draw horizontal line for mean ? - Only if $ylegends contain 'mean' **/
        bool    $meanLine = true,
        /**$meanLineStyle       Style for mean line **/
    // end parameters
    ): string {
    
print_r($data); exit;
        //
        // display details
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
        $style = <<<SVG
<style type="text/css"><![CDATA[
.bl { /* bar line */
    stroke:$barColor;
    stroke-width:$barW;
}
.cl { /* curve line */
    stroke:$curveColor;
    stroke-width:$curveW;
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
.xLegendsMark{
    stroke:black;
}
.yLegends{
    text-anchor:end;
    dominant-baseline:middle;
    font-size:{$ylegendsH}px;
}
.yLegendsMark{
    stroke:black;
}
.meanLine{
    stroke:black;
    stroke-dasharray:5,20;
}
]]></style>

SVG;
        //
        // main characteristics of data
        //
        $N = count($data_bar); // common to bar and curve
        //
        //
        // general variables for drawing
        // 
        // $xBegin, $xEnd, $yBegin, $yEnd = coordinates of top-left and bottom-right of the draw area
        $xBegin = $hGap + $ylegendsW + $ylegendsRightGap;
        $yBegin = $vGap + $titleH + $titleBottomGap;
        $drawAreaW = $N * $barW + ($N-1) * $barGap;
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
        // Start drawing
        //
        $svg = '';
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
        // bar and curve drawing
        //
        $i = 0;
        $xlegendKeys = array_keys($xlegends);
        foreach($dataKeys as $key){
            
            $x = $xBegin + $i * $barGap + ($i + 0.5) * $barW;
            
            //
            // bar
            //
            if(count($data_bar) != 0){
                $val_bar = $data_bar[$key];
                $x1 = $x;
                $x2 = $x;
                $y1 = $yEnd;
                $y2 = $yEnd - round(($val_bar - $min) * $deltaY / $maxMin, 1);
                if($barHover === true){
                    $svg .= "<g><title>$key: $val_bar</title>";
                }
                $svg .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" class=\"bl\" />";
                if($barHover === true){
                    $svg .= '</g>';
                }
                $svg .= "\n";
            }
            //
            // curve
            //
            if(count($data_curve) != 0){
                $val_curve = $data_curve[$key];
                // x1 and y1 are the same as bar
                $y = $yEnd - round(($val_curve - $min) * $deltaY / $maxMin, 1);
                if($i != 0){
                    $svg .= "<line x1=\"$x_prev\" y1=\"$y_prev\" x2=\"$x\" y2=\"$y\" class=\"cl\" />";
                }
                [$x_prev, $y_prev] = [$x, $y];
                $svg .= "\n";
            }
            $i++;
            //
            // x legends
            //
            // xlegends handled in this loop to take profit of $x computation
            if(in_array($key, $xlegendKeys)){
                $y = $yEnd + $xlegendsTopGap + $xlegendsH;
                $svg .= "<text x=\"$x\" y=\"$y\" class=\"xLegends\">" . $xlegends[$key] . "</text>\n";
                //
                $y1 = $yEnd;
                $y2 = $yEnd + 5;
                $svg .= "<line x1=\"$x\" y1=\"$y1\" x2=\"$x\" y2=\"$y2\" class=\"xLegendsMark\" />\n";
            }
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
                    $x1 = $xBegin;
                    $x2 = $xBegin - 5;
                    $svg .= "<line x1=\"$x1\" y1=\"$y\" x2=\"$x2\" y2=\"$y\" class=\"yLegendsMark\" />\n";
                }
                if(in_array('max', $ylegends)){
                    $y = $yBegin;
                    $svg .= "<text x=\"$x\" y=\"$y\" class=\"yLegends\">$max</text>\n";
                    $x1 = $xBegin;
                    $x2 = $xBegin - 5;
                    $svg .= "<line x1=\"$x1\" y1=\"$y\" x2=\"$x2\" y2=\"$y\" class=\"yLegendsMark\" />\n";
                }
                if(in_array('mean', $ylegends)){
                    $yMean = round($yBegin + $deltaY*($max-$stats['MEAN'])/$maxMin);
                    $y = $yMean;
                    $text = round($stats['MEAN'], $ylegendsRound);
                    $svg .= "<text x=\"$x\" y=\"$y\" class=\"yLegends\">$text</text>\n";
                    $x1 = $xBegin;
                    $x2 = $xBegin - 5;
                    $svg .= "<line x1=\"$x1\" y1=\"$y\" x2=\"$x2\" y2=\"$y\" class=\"yLegendsMark\" />\n";
                }
            }
        }
        //
        // other
        //
        if($meanLine){
            $yMean = round($yBegin + $deltaY*($max-$stats['MEAN'])/$maxMin);
            $y1 = $y2 = $yMean;
            $x1 = $xBegin;
            $x2 = $xEnd;
            $svg .= "<g fill=\"none\"><path class=\"meanLine\" d=\"M$x1 $y1 H$x2 $y2 Z\" /></g>\n";
        }
        //
        $svg .= "</svg>\n";
        return $svg;
    }
    
} // end class
