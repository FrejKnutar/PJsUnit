<?php
/*
This script states how the main progran will be represented as a string
Variables available:
$passed			True if all the unit tests passed, else false
$functions	An array containing all tested functions converted to strings.
$objects		An array containing all tested classes and objects converted to strings.
$time				The time it took to run all the unit tests.
*/
$string = '<div class="PHPUnit">';
if(count($functions)>0) {
	$string .= '<label>Functions</label><ul>';
	foreach($functions as $fun) {
		$string .= $fun;
	}
	$string -= '</ul>';
}
if(count($objects)>0) {
	$string .= '<label>Objects</label><ul>';
	foreach($objects as $obj) {
		$string .= $obj;
	}
	$string -= '</ul>';
}
if(count($classes)>0) {
	$string .= '<label>Objects</label><ul>';
	foreach($classes as $cls) {
		$string .= $cls;
	}
	$string -= '</ul>';
}
$string .= "<p><label>Passed</label>&nbsp".$passed_count.' ('.($tests>0 ? ((string) ($passed_count/$tests*100).'%') : 'NA').")"."</php>";
$string .= "<p><label>Failed</label>&nbsp".$failed_count.' ('.($tests>0 ? ((string) ($failed_count/$tests*100).'%') : 'NA').")"."</php>";
if($time != '0') {
	$string .= "<p><label>Failed</label>&nbsp$time</p>";
}
$string .= "</div>";
return $string;
?>