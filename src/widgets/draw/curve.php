<?php
/******************************************************************************
    curve draws line curves of 1 - n data on the same diagram
    
    @param  $data    Data to represent and display instructions.
        Contains the following YAML structure:
        data:
          head
            title:
            desc:
            x-axis: 
            y:axis: 
          data
            0:
              data: [data1, data2 ... dataN]
              head:
                css:
                label:
                abbr:
                :
                :
            1:
              data:
              head:

    @license    GPL
    @history    2021-01-31 19:31:00+01:00,   Thierry Graff : Creation
********************************************************************************/
namespace observe\widgets\draw;

use observe\Observe;
use observe\patterns\Command;
use observe\ObserveException;
use tiglib\arrays\csvAssociative;
use tiglib\arrays\csvRegular;

class curve implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters                                                     
        //
        $classname = 'bar'; // TODO copute by reflection
        if(!isset($params['input-file'])){
            throw new ObserveException("$classname needs a parameter 'input-file'");
        }
        $infile = $params['input-file'];
        if(!is_file($infile)){
            throw new ObserveException("File not found : $infile");
        }
        //
        if(!isset($params['col'])){
            throw new ObserveException("$classname needs a parameter 'col'");
        }
        //
        if(!isset($params['assoc'])){
            throw new ObserveException("$classname needs a parameter 'assoc'");
        }
        // output
        if(isset($params['output-file']) && isset($params['output-data'])){
            throw new ObserveException("$classname can't have both parameters 'output-data' and 'output-file'");
        }
        if(!isset($params['output-file']) && !isset($params['output-data'])){
            throw new ObserveException("$classname needs either parameter 'output-data' or 'output-file'");
        }
        if(isset($params['output-file'])){
            $outfile = $params['output-file'];
            $outdir = dirname($outfile);
            if(!is_dir($outdir)){
                throw new ObserveException("output directory does not exist: $outdir");
            }
        }
        //
        //  execute
        //
        if($params['assoc']){
            $in = csvAssociative::compute($infile);
        }
        else{
            $in = csvRegular::compute($infile);
        }
        //
        $data = [];
        foreach($in as $elt){
            $data[] = $elt[$params['col']];
        }
        //
        //
        //
        $res = '';
        [$min, $max] = [min($data), max($data)];
        $N = count($data);
        //
        $barW = 2; // px
        $barGap = 1; // px
        $barDelta = $barW + $barGap;
        $barStyle = "stroke:blue;stroke-width:$barW;";
        //
        [$hgap, $vgap] = [15, 15];
        [$legendW, $legendH] = [30, 20];
        [$legendRightGap, $legendTopGap] = [5, 2];
        //
        $h = 250;
        //
        $xBegin = $hgap + $legendW + $legendRightGap;
        $yBegin = $h - $vgap - $legendH - $legendTopGap;
        $deltaY = $yBegin - $vgap;
        //
        $w = $xBegin + $hgap + $N*$barW + ($N-1)*$barGap;
        //
        $svgStyle = 'background-color:white;'; // TODO does not work
        $res .= "<svg width=\"$w\" height=\"$h\" style=\"$svgStyle\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" version=\"1.1\">\n";
        $res .= "<rect width=\"100%\" height=\"100%\" fill=\"lightyellow\" />\n"; // hack for bg color 
        // axis
        $axisStyle = 'stroke:black;stroke-width:1;';
        // vertical
        //[$x1, $y1] = [$xBegin, $vgap];
        //[$x2, $y2] = [$xBegin, $h - $yBegin];
        //$res .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" style=\"$axisStyle\" />\n";
        // horizontal
        [$x1, $y1] = [$xBegin - $barW, $yBegin];
        [$x2, $y2] = [$w - $hgap - $barW, $yBegin];
        $res .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" style=\"$axisStyle\" />\n";
        // bars
        for($i=0; $i < $N; $i++){
            $x = $xBegin + ($i-1)*$barGap + $i*$barW;
            $y1 = $yBegin;
            $cur = $data[$i];
            $y = ($cur-$min) * $deltaY / ($max-$min);
            $y2 = $yBegin - $y;
            $res .= "<line x1=\"$x\" y1=\"$y1\" x2=\"$x\" y2=\"$y2\" style=\"$barStyle\" />\n";
        }
        // legend vertival
        // TODO clean $legendW+10
        [$x, $y] = [$xBegin - $legendW, $yBegin];
        $res .= "<text x=\"$x\" y=\"$y\" style=\"text-anchor: middle\">$min</text>\n";
        [$x, $y] = [$xBegin - $legendW, $vgap + $legendH];
        $res .= "<text x=\"$x\" y=\"$y\" style=\"text-anchor: middle\">$max</text>\n";
        // legend horizontal
        // TODO absolute draft - pass the legend in parameter
        foreach([0, 90, 120, 180, 240, 270] as $i){
            [$x, $y] = [$xBegin + $i*$barDelta, $yBegin + $legendH];
            $res .= "<text x=\"$x\" y=\"$y\" style=\"text-anchor: middle\">$i</text>\n";
        }
        //
        $res .= "</svg>\n";
        //
        // write output
        //
        if(isset($params['output-file'])){
            file_put_contents($outfile, $res);
            echo "Wrote $outfile\n";
        }
        else {
            return $res;
        }
    }
    
}// end class
