<?php
include_once "PJsUnit.php";

//This class is added to the engine because the suffix of the class is "Test"
class ClassTest {
	function __construct() {
		;
	}
	// This method will be executed first
	function setUp() {
		// Set up
	}
	// This method will be executed, it has the "Test" suffix
	function assertTrueTest() {
		PJsUnit::assertTrue(true);
	}
	// This method will be executed, it has the "Test" suffix
	function assertTrueFailTest() {
		PJsUnit::assertTrue(false);
	}
	// This method will be executed last
	function tearDown() {
		// Tear Down
	}
}

class Obj {
	private $var;
	function __construct($var) {
		$this->var = $var;
	}
	// This method will be executed, it has the "Test" suffix
	function Test() {
		PJsUnit::assertTrue(false);
	}
}
// This function will added and executed automatically, it has the "_test" suffix
function functionTest_test() {
	PJsUnit::assertTrue(true);
	PJsUnit::assertTrue(false);
}
// Adding a custom assertion, assertString(str $String) to the library.
PJsUnit::addAssertion("assertString", function($string) {return is_string($string);});
?>