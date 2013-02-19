<?php
if(class_exists("PJsUnit")) {
	PJsUnit::addAssertion("assertTrue",function($bool) {return $bool == true;});
	PJsUnit::addAssertion("assertFalse",(function($bool) {return $bool != true;}));
	PJsUnit::addAssertion("assertArrayKeyExists",function($key,$search) {return array_key_exists($key, $search);});
}
?>