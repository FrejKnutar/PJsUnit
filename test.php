<?php
include_once "PJsUnit.php";

//This class is added to the engine because the suffix of the class is "_test"
class class_test {
	private $var = 5;

	function __construct() {
		;
	}

	//This method is called first, it is the set_up/before method
	function set_up() {
		echo("set-up");
		$var = 10;
	}

	//This method is called because the suffix is "_test"
	function first_test($var = 5) {
		PJsUnit::assert_false($this->var == $var);
	}
	//This method is called because the suffix is "_test" and the parameter of this function isn't required.
	function second_test($var = 5) {
		PJsUnit::assert_false(true);
	}
	//This static method is called because the suffix is "_test" and the parameter isn't required.
	static function third_test($var = 5) {
		PJsUnit::assert_true(true);
		PJsUnit::assert_array_key_exists("hej",["hej"=>"ja"]);
	}
	//This method is not added, the method is prvate.
	private function fourth_test($var = 5) {
		PJsUnit::assert_false(true);
	}
	//This method is not added automatically unless it has an assert method call and is called by an objct.
	function test() {
		PJsUnit::assert_string("true");
	}
	//This method is the last one to be called, it is the tear_down/after method.
	function tear_down() {
		echo "tear_down".PHP_EOL;
	}
}

class Obj {
	private $var;
	function __construct($var) {
		$this->var = $var;
	}
	function test() {
		PJsUnit::assert_true(false);
	}
}

//This function is added automatically because the suffix is "_test" and the parameter isn't required.
function function_test($var = 5) {
	PJsUnit::assert_true(true);
}
//This function is added automatically because the suffix is "_test".
function function_two_test() {
	PJsUnit::assert_true(false);
}
//This function is added when it is called.
function foo() {
	PJsUnit::assert_true(true);
}
//This function is added when it is called.
function bar() {
	PJsUnit::assert_false(true);
}
//Creates an anonymous function.
$fun = function($string) {return is_string($string);};
//Adds the previously created function to PJsUnit and is now callable as a static assertion function.
PJsUnit::assert_string($fun);

$obj = new class_test();
$obj->test();
PJsUnit::addObject($obj);
PJsUnit::addObject($obj);
$obj2 = new Obj(5);
$obj2->test();
?>