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
$string = "      Row: ".$line.PHP_EOL;
$string .= "      Function: ".$function.'(';
for($i=count($arguments)-1;$i>=0;$i--) {
	$string .= $arguments[$i];
	if($i>0) {
		$string .=', ';
	}
}
$string .= ")".PHP_EOL;
return $string;
?>