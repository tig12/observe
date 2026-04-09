<?php
/******************************************************************************
    SVG generation adapted to observe output pages.
    
    @license    GPL
    @history    2021-03-19 01:38:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tigdraw;

class svg {
    

    /** 
        TODO Useless ? (as separate header works also when svg is inside the html)
    **/
    public static function header(bool $separate, int $width, int $height): string {
        return $separate
            ? self::header_separate($width, $height)
            : self::header_inside($width, $height);
    }
    
    /**
        SVG header when the svg is stored in a separate .svg file
    **/
    public static function header_separate(int $width, int $height): string {
        return <<<SVG
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:cc="http://creativecommons.org/ns#"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:svg="http://www.w3.org/2000/svg"
    xmlns="http://www.w3.org/2000/svg"
    width="$width"
    height="$height"    
>

SVG;
    }
    
    /**
        SVG header when the svg is stored in the html
    **/
    public static function header_inside(int $width, int $height): string {
        return <<<SVG
<svg
    width="$width"
    height="$height"
>

SVG;
    }

} // end class
