<?php
if(class_exists("PJsUnit")) {
	PJsUnit::assert_true((function($bool) {return $bool == true;}));
	PJsUnit::assert_false((function($bool) {return $bool != true;}));
	PJsUnit::assert_array_key_exists((function($key,$search) {return array_key_exists($key, $search);}));
}
?>