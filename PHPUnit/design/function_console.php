<?php
/*
This script states how the functions will be represented as strings 
Variables available:
$passed		True if the unit test passed, else false
$errors		An array containing the error messages recieved from running the function.
$type 		The type (function)
$name			The function name
$time			The time it took to run the function.
*/
$string = $type.": ".$name."()";
	if($passed) {
		$string.= " PASSED";
		if($time > 0) {
			$string.= " (".$timestr." s)";
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