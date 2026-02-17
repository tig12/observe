<?php
/** 
    Test src/bar.php displaying a bar representation of day.csv
    usage: php bar_test.php > test.svg
**/
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';

use tigdraw\bar;
use tigdraw\svg;

$csv = file(__DIR__ . DIRECTORY_SEPARATOR . 'day.csv', FILE_IGNORE_NEW_LINES);
$data = [];
foreach($csv as $line){
    $tmp = explode(';', $line);
    $data[$tmp[0]] = $tmp[1];
}

[$html_markup, $file_contents] = bar::svg(
    data:           $data,
    title:          "Test SVG",
    svg_separate:   false,
    barW:           2,
    xlegends:       ['min', 'max'],
    ylegends:       ['min', 'max'],
    ylegendsRound:  1,
);

echo $html_markup;
