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
$string = "      File: ".$file.PHP_EOL;
$string .= "      Row: ".$row.PHP_EOL;
$string .= '      Line: "'.$line.'"'.PHP_EOL;
$string .= "      Function: ".$function.'(';
for($i=0;$i<count($arguments);$i++) {
	$string .= $arguments[$i];
	if($i<count($arguments)-1) {
		$string .=', ';
	}
}
$string .= ")".PHP_EOL;
return $string;
?>