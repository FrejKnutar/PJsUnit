<?php
include_once("PHPUnit.php");

class class_test {

	private $var = 5;

	function __construct() {
		;
	}

	function set_up() {
		$var = 10;
	}

	function first_test($var = 5) {
		PHPUnit::assert_false($this->var == $var);
	}
	function second_test($var = 5) {
		PHPUnit::assert_false(true);
	}
	static function third_test($var = 5) {
		PHPunit::assert_true(true);
	}
	private function fourth_test($var = 5) {
		PHPUnit::assert_false(true);
	}
	function test() {
		PHPUnit::assert_true(true);
	}

	function tear_down() {
		echo "tear_down".PHP_EOL;
	}
}

function function_test($var = 5) {
	PHPUnit::assert_true(true);
}

function function_two_test() {
	PHPUnit::assert_true(false);
}

function foo() {
	PHPUnit::assert_true(true);
}

function bar() {
	PHPUnit::assert_false(true);
}

$obj = new class_test();
PHPUnit::add_object($obj);

PHPUnit::add_function("foo");
bar();
$obj_test = new class_test();

?>