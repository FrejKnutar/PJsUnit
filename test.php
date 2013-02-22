<?php
include_once "PJsUnit.php";

//This class is added to the engine because the suffix of the class is "_test"
class ClassTest {
	function __construct() {
		;
	}
	function setUp() {
		// Set up
	}
	function assertTrueTest() {
		PJsUnit::assertTrue(true);
	}
	function assertTrueFailTest() {
		PJsUnit::assertTrue(false);
	}
	function tearDown() {
		// Tear Down
	}
}

class Obj {
	private $var;
	function __construct($var) {
		$this->var = $var;
	}
	function Test() {
		PJsUnit::assertTrue(false);
	}
}
function function_test() {
	PJsUnit::assertTrue(true);
}
PJsUnit::addAssertion("assertString", function($string) {return is_string($string);});
?>