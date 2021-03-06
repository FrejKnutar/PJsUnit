<?php
/*
This script states how the functions will be represented as strings 
Variables available:
$passed		True if the unit test passed, else false
$methods	An array containing the methods converted to Strings that were tested by the object.
$type 		The type (function)
$name			The function name
$time			The time it took to run the function.
*/
$string = "    ".$type.": ".$name."()";
	if($passed) {
		$string.= " PASSED";
		if($time > 0) {
			$string.= " (".$time." s)";
		}
		$string.= PHP_EOL;
	} else {
		$string.= " FAILED".PHP_EOL;
		foreach($errors as $e) {
			$string.= $e;
		}
	}
return $string;
?>