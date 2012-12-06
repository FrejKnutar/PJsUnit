<?php
/*
This script states how the main progran will be represented as a string
Variables available:
$passed			True if all the unit tests passed, else false
$functions	An array containing all tested functions converted to strings.
$objects		An array containing all tested classes and objects converted to strings.
$time				The time it took to run all the unit tests.
*/
$string = "PHPUnit".PHP_EOL;
$string .= "Created by Frej Knutar.".PHP_EOL;
$string .= "https://github.com/FrejKnutar/PJsUnit".PHP_EOL;
if(count($functions) > 0) {
	$string .= "Functions:".PHP_EOL;
}
foreach($functions as $f) {
	$string .= $f.PHP_EOL;
}
if(count($classes) > 0) {
	$string .= "Classes:".PHP_EOL;
}
foreach($classes as $c) {
	$string .= $c.PHP_EOL;
}
if(count($objects) > 0) {
	$string .= "Objects:".PHP_EOL;
}
foreach($objects as $o) {
	$string .= $o.PHP_EOL;
}
return $string;
?>