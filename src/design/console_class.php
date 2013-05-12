<?php
/*
This script states how classes will be represented as strings 
Variables available:
$passed		True if the unit test passed, else false
$methods	An array containing all the tested methods converted to Strings.
$type 		The type (function)
$name			The function name
$time			The time it took to run the function.
$file			The file that the object is located in.
*/
$string = "  ".$type.": ".$name."()";
if($passed) {
	$string.= " PASSED";
	if($time > 0) {
		$string.= " (".$time." s)";
	}
	$string.= PHP_EOL;
} else {
	$string .= " FAILED".PHP_EOL;
	foreach($methods as $m) {
		$string .= $m;
	}
	$string .= "  Methods: ".count($methods).PHP_EOL;
	$string .= "  Passed: $passedCount (".(count($methods)>0 ? ((string) ($passedCount/count($methods)*100).'%') : "NA").')'.PHP_EOL;
	$string .= "  Failed: $failedCount (".(count($methods)>0 ? ((string) ($failedCount/count($methods)*100).'%') : "NA").')'.PHP_EOL;
}
return $string;
?>