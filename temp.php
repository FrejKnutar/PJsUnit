<?php
include_once("PHPUnit.php");
function function_test() {
	PHPUnit::assert_true(5 == 5);
}
function manual_function() {
	PHPUnit::assert_true("foo" != "bar");
}
class Class_test {
	private $var = 0;
	function __construct() {}
	function set_up() {
		$this->var = 5;
	}
	function method_test() {
		PHPUnit::assert_true($this->var == 0);
		PHPUnit::assert_true($this->var == 5);
	}
	function manual_method() {
		PHPUnit::assert_true($this->var == 0);
	}
}
PHPUnit::add_function("manual_function");
$test_obj = new Class_test();
$test_obj->manual_method();
?>