<?php namespace PHPUnit;
if(!class_exists(__NAMESPACE__."\PHPUnit")) return;

function include_extract($path,$array = array()) {
	if(file_exists($path)) {
		extract($array);
		return include($path);
	} else {
		if(strpos($path, "/") != false) {
			$path = str_replace("/", "\\", $path);
		} else {
			$path = str_replace("\\", "/", $path);
		}
		if(file_exists($path)) {
			return include($path);
		}
	}
}

abstract class PHPUnit_testInstance {
	protected $name = null;
	protected $passed = true;
	protected $time = null;
	protected $type;
	
	function __toString() {
		$suffix = PHPUnit::display();
		$array['passed'] = $this->passed;
		$array['errors'] = array();
		foreach($this->errors as $e) {
			$array['errors'] = string($e);
		}
		$array['type'] = $this->type;
		$array['name'] = $this->name;
		$array['time'] = $this->time;
		$array['string'] = "";
		$type = strtolower($this->type);
		$dir = dirname(__FILE__);
		$path=$dir."/design/".$type."_".$suffix.".php";
		return include_extract($path,$array);
	}

	function test($run = true) {
		$function = $this->name();
		$start = microtime(true);
		if($run) $function();
		$this->time = microtime(true) - $start;
		return $this->passed();
	}

	function name() { return $this->name; }
	function time()	{ return $this->time; }
	
	function passed($bool = null) {
	if(is_bool($bool)) {
			$this->passed = $bool;	
		} else {
			throw new \Exception('Illegal argument. Expected "boolean", received "'.gettype($bool).'".');
		}
		return $this->passed;
	}
		
	function add_error($error) {
		if(!is_a($error, __NAMESPACE__."/Error")) {
			throw new \Exception('Illegal argument. Expected "'.__NAMESPACE__.'"/Error", received "'.gettype($error).'".');
			return false;
		}
	}
}

class PHPUnit_TestObject extends PHPUnit_testInstance {
	protected $type = "Object";

	private $passed_count 	= 0;
	private $methods = array();
	private $current_method = null;
	private $method_suffix = null;
	private $was_timed = false;
	
	function passed_count()	{ return $this->passed_count; }
	function failed_count()	{ return $this->method_count()-$this->passed_count(); }
	function method_count()	{ return count($this->methods); }
	
	function __construct($test_object) {
		$this->method_suffix = PHPUnit::method_suffix();
		$methods = get_class_methods($test_object);
		if($methods == null) {
			throw new \Exception("Parameter is not an object.");
		} else {
			$this->name = get_class($test_object);
			foreach($methods as $method) {
				$reflectionMethod = new \ReflectionMethod($this->name,$method);
				if (substr($method,-strlen($this->method_suffix)) == $this->method_suffix && $reflectionMethod->getNumberOfParameters() == 0) {
					$test_method = new \PHPUnit_TestMethod($test_object,$method);
					$this->methods[] = $test_method;
				}
			}
		}
	}
	
	function __toString() {
		$suffix = PHPUnit::display();
		$array['passed'] = $this->passed;
		$array['methods'] = array();
		foreach($this->methods as $m) {
			$array['methods'][] = string($m);
		}
		$array['type'] = $this->type;
		$array['name'] = $this->name;
		$array['time'] = $this->time;
		$array['passed_count'] = $this->passed_count;
		$array['failed_count'] = count($this->methods) - $this->passed_count;
		$array['string'] = "";
		$type = strtolower($this->type);
		$dir = dirname(__FILE__);
		$path=$dir."/design/".$type."_".$suffix.".php";
		return include_extract($path,$array);
	}

	function add_error($error, $failed=true) {
		try {
			parent::add_error($error);
			if($error->name() == $this->current_method->name()) {
				$method = $this->current_method;
			} else {
				foreach($this->methods as $m) {
					if($m->name() ==  $error->name()) {
						$method = $m;
						break;
					}
				}
			}
			if($failed) $this->passed = false;
			return true;
		} catch (\Exception $exception) {
			throw $exception;
			return false;
		}
	}

	function test() {
		$time = microtime(true);
		foreach($this->methods as $method) {
			$this->current_method = $method;
			if($method->test()) {
				$this->passed_count++;
			}
		}
		$this->time = microtime(true) - $time;
		return $this->passed();
	}
	
}

class PHPUnit_TestFunction extends PHPUnit_testInstance {
	protected $errors = array();
	protected $type = "Function";
	
	function __construct($function) {
		$this->name = $function;
	}

	function add_error($error, $failed=true) {
		try {
			parent::add_error($error);
			if($error->function == $this->name) {
				$this->errors[] = $error;
				if($failed) $this->passed = false;
				return true;
			} else {
				throw new \Exception("Illegal argument. The argument error wasn't encountered in function \"".$this->name.'" but in function "'.$error->function().'.');
			}
		}
		catch(\Exception $e) {
			throw $e;
			return false;
		}
	}
}

class PHPUnit_TestMethod extends PHPUnit_TestFunction {
	private $test_object = null;
	
	function __construct($test_object,$method) {
		parent::__construct($method);
		$this->test_object = $test_object;
		$this->type = "Method";
	}
	
	function test($run = true) {
		$method = $this->name();
		$start = microtime(true);
		$this->test_object->$method();
		$this->time = microtime(true) - $start;
		return $this->passed;
	}
	
	function __toString() {
		return parent::__toString();
	}
}

?>