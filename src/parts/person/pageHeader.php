<?php 
/******************************************************************************
    returns the HTML header of a person page
    
    
    @license    GPL
    @history    2021-02-13 01:58:48+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\person;


class pageHeader {
    
    public static function compute(
        $params=[],
        $title = '',
        $subtitle = '',
        $desc = '',
        $intro = '',
    ){
        $res = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>
        $title
        <div class="subtitle">$subtitle</div>
    </title>
    <meta name="description" content="$desc">
    <link rel="stylesheet" href="static/observe.css" type="text/css">
</head>

<body>

<header>
<h1>$title</h1>

<div class="intro">
$intro
</div>
</header>

<article>
<div class="toc">
    <ul>
HTML;
        return $res;
    }
    
}// end class
