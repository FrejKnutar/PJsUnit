<?php
include_once("PHPUnit.php");

class class_test {
	function __construct() {
		;
	}
	function method_test() {
		PHPUnit::assertTrue(false);
	}
}

function function_test() {
	PHPUnit::assertTrue(false);
}

$obj_test = new class_test();

?>