<?php
/*
This script states how the functions will be represented as strings 
Variables available:
$passed		True if the unit test passed, else false
$errors		An array containing the error messages recieved from running the function.
$type 		The type of the object (function in this case)
$name			The function name
$time			The time it took to run the function.
*/
$string .= "<label>$type:&nbsp$name()&nbsp";
if($passed) {
	$string .= '<span class="#00ff00">PASSED</span></label>';
	if($time != 0) {
		$string .= " (".$time." s)";
	}
} else {
	$string .= '<span color="#ff0000">FAILED</span></label>';
	$string .= "<ol>";
	foreach($error as $e) {
		$string .= "<li>$e</li>";
	}
	$string .= "</ol>";
}
return $string;