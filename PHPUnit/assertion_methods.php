<?php
if(!class_exists("PHPUnit")) exit;

PHPUnit::assert_true((function($bool) {return $bool == true;}));
PHPUnit::assert_false((function($bool) {return $bool != true;}));
PHPUnit::assert_array_key_exists((function($key,$search) {return array_key_exists($key, $search);}));
?>