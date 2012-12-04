<?php
include_once("PHPUnit.php");

class class_test {
	function __construct() {
		echo __CLASS__."::".__METHOD__.PHP_EOL;
	}
	function method_test() {
		echo __CLASS__."::".__METHOD__.PHP_EOL;
	}
}

function function_test() {
	//$PHPUnit::assertTrue(true);
}

$obj_test = new class_test();

?>