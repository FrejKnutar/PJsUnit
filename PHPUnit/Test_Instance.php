<?php namespace PHPUnit;
function PHPUnit_timeToString($secs) {
	$milliseconds  = (int) ($secs*1000);
	$seconds = floor($secs) % 60;
	$minutes = floor($secs / 60) % 60;
	$hours = floor($secs / 3600);
	$str = "";
	if($hours>0) {
		$str .= $hours.":";
	}
	if($minutes>9) {
		$str .= $minutes.":";
	} elseif($minutes>0) {
		if($hours > 0) {
			$str .= '0'.$minutes.":";
		} else {
			$str .= $minutes.":";
		}
	}
	if($seconds>9) {
		$str .= $seconds;
	} elseif($seconds>0) {
		if($hours > 0 || $minutes > 0) {
				$str .= '0'.$seconds;
		} else {
			$str .= $seconds;
		}
	} else {
		$str .='0';
	}
	if($milliseconds > 999) {
		$str .= '.'.$milliseconds;
	} elseif($milliseconds > 99) {
		$str .= '.0'.$milliseconds;
	} elseif($milliseconds > 9) {
		$str .= '.00'.$milliseconds;
	} elseif($milliseconds > 0) {
		$str .= '.000'.$milliseconds;
	}
	return $str;
}

function include_extract($path,$array = array()) {
	if(isset($array['name'])) $array['name'] = substr($array['name'],0,1) == '\\' ? substr($array['name'],1) : $array['name'];
	if(file_exists($path)) {
		extract($array);
		unset($array);
		return include($path);
	} else {
		if(strpos($path, '/') == false) {
			$path = str_replace("/", "\\", $path);
		} else {
			$path = str_replace("\\", "/", $path);
		}
		if(file_exists($path)) {
			extract($array);
			unset($array);
			return include($path);
		}
	}
	return "File at location '$path' not found.".PHP_EOL;	
}

class Error {
	private $file = null;
	private $row = null;
	private $line = null;
	private $function = null;
	private $arguments = array();
	private $passed = null;
	private $caller = null;
	private $class = null;
	private $type = null;
	
	function __construct($error) {
		$this->file = $error["file"];
		$this->row = (int) $error["line"];
		$this->function = $error["function"];
		$this->arguments = $error["args"];
		$this->passed = $error["passed"];
		$file = file($this->file);
		$this->line = trim($file[$this->row-1]);
		if(isset($error["caller"])) {
			$this->caller = $error["caller"];
		}
		if(isset($error["class"])) {
			$this->class = $error["class"];
		}
		if(isset($error["type"])) {
			$this->type = $error["type"];
		}
	}
	
	function __toString() {
		$prefix = \PHPUnit::design_prefix();
		$array = array();
		$array['type'] = (string) __CLASS__;
		$array["file"] = $this->file;
		$array["line"] = $this->line;
		$array["row"] = $this->row;
		$array["function"] = $this->function;
		$array["class"] = $this->class;
		$array["type"] = $this->type;
		$array["arguments"] = array();
		foreach($this->arguments as $arg) {
			$array["arguments"][] = print_r($arg,true);
		}
		$array["passed"] = $this->passed;
		$array["caller"] = $this->caller;
		$array['string'] = "";
		$type = strtolower(stripslashes(str_replace(__NAMESPACE__,'',__CLASS__)));
		$dir = dirname(__FILE__);
		$path=$dir."/design/".$prefix."_".$type.".php";
		return include_extract($path,$array);
	}

	function __get($name) {
		if(property_exists(__CLASS__, $name)) {
			return $this->$name;
		}
	}
}

abstract class Test_Instance {
	protected $name = null;
	protected $passed = true;
	protected $time = null;
	protected $type;
	protected $run_test = true;

	function __toString() {
		$prefix = \PHPUnit::design_prefix();
		$array['passed'] = $this->passed;
		$array['errors'] = array();
		foreach($this->errors as $e) {
			$array['errors'][] = (string) $e;
		}
		$array['type'] = $this->type;
		$array['name'] = substr($this->name,0,1) == '\\' ? substr($this->name,1) : $this->name;
		$array['time'] = $this->time;
		$array['string'] = "";
		$type = strtolower($this->type);
		$dir = dirname(__FILE__);
		$path=$dir."/design/".$prefix."_".$type.".php";
		return include_extract($path,$array);
	}

	function __get($name) {
		if(property_exists(__CLASS__, $name)) {
			return $this->$name;
		}
	}

	function __set($name, $value) {
		switch($name) {
			case "passed":
				if(is_bool($value)) {
					$this->passed = $value;
					return true;
				}
			case "run_test":
				if(is_bool($value)) {
					$this->run_test = $value;
					return true;
				}
		}
		return false;
	}

	function test($run_test = true) {
		$function = $this->name;
		$start = microtime(true);
		if($this->run_test && $run_test) {
			$function();
		}
		$this->time = microtime(true) - $start;
		return $this->passed;
	}
		
	function add_error($error) {
		if(!is_a($error, __NAMESPACE__."\\Error")) {
			throw new \Exception('Illegal argument. Expected "'.__NAMESPACE__.'"/Error", received "'.gettype($error).'".');
			return false;
		}
	}
}

class Test_Object extends Test_Instance {
	protected $type = "Object";
	protected $class = null;
	protected $object = null;
	protected $passed_count	= 0;
	protected $methods = array();
	protected $current_method = null;
	protected $was_timed = false;
	
	function __get($name) {
		if(property_exists(__CLASS__, $name)) {
			return $this->$name;
		}
	}

	function __set($name, $value) {
		parent::__set($name, $value);
		if($name == "run_test") {
			if(is_bool($value)) {
				foreach($this->methods as $m) {
					$m->$name = $value;
				}
				return true;
			}
		}
		return false;
	}

	function __construct($test_object,$object_name = null) {
		if(!is_object($test_object)) {
			throw new \Exception("Input parameter \$test_object is not an object");
		}
		$this->object = $test_object;
		$this->class = get_class($test_object);
		if($object_name != null && is_string($object_name)) {
			$this->name = $object_name;
		} else {
			$this->name = $this->class;
		}
		$method_suffix = \PHPUnit::method_suffix();
		$methods = get_class_methods($test_object);
		$temp_methods = array();
		foreach($methods as $method) {
			foreach($this->methods as $m) {
				if ($m->name == $method) {
					break(2);
				}
			}
			$reflectionMethod = new \ReflectionMethod($this->name,$method);
			if (substr($method,-strlen($method_suffix)) == $method_suffix && $reflectionMethod->getNumberOfRequiredParameters() == 0) {
				$test_method = new Test_Method($test_object,$method);
				$temp_methods[] = $test_method;
			}
		}
		foreach($temp_methods as $m) {
			$this->methods[] = $m;
		}
	}
	
	function __toString() {
		$prefix = \PHPUnit::design_prefix();
		$array['passed'] = $this->passed;
		$array['methods'] = array();
		foreach($this->methods as $m) {
			$array['methods'][] = (string) $m;
		}
		$array['type'] = $this->type;
		$array['name'] = $this->name;
		$array['time'] = $this->time;
		$array['passed_count'] = $this->passed_count;
		$array['failed_count'] = count($this->methods) - $this->passed_count;
		$array['string'] = "";
		$type = strtolower($this->type);
		$dir = dirname(__FILE__);
		$path=$dir."/design/".$prefix."_".$type.".php";
		return include_extract($path,$array);
	}

	function add_method($method,$run_test) {
		if($this->object == null || method_exists($this->object, $method)) {
			$method = new Test_Method($this->object, $method);
			$method->run_test = false;
			$this->methods[] = $method;
		}
	}

	function add_error($error, $failed=true) {
		try {
			if($this->current_method != null && $error->caller == $this->current_method->name) {
				$method = $this->current_method;
			} else {
				foreach($this->methods as $m) {
					if($m->name ==  $error->caller) {
						$method = $m;
						break;
					}
				}
			}
			if($failed) $this->passed = false;
			if(isset($method)) {
				return $method->add_error($error, $failed);
			}
		} catch (\Exception $exception) {
			throw $exception;
			return false;
		}
		return false;
	}

	function test($run_test=true) {
		$time = microtime(true);
		$set_up_name = \PHPUnit::set_up_name();
		$tear_down_name = \PHPUnit::tear_down_name();
		if($this->run_test && $run_test && method_exists($this->object, $set_up_name)) {
			$reflection_method = new \ReflectionMethod($this->name,$set_up_name);
			if($reflection_method->getNumberOfRequiredParameters() == 0) {
				$this->object->$set_up_name();
			}
		}
		foreach($this->methods as $method) {
			$this->current_method = $method;
			if($method->test($run_test)) {
				$this->passed_count++;
			}
		}
		if($this->run_test && $run_test && method_exists($this->object, $tear_down_name)) {
			$reflection_method = new \ReflectionMethod($this->name,$tear_down_name);
			if($reflection_method->getNumberOfRequiredParameters() == 0) {
				$this->object->$tear_down_name();
			}
		}
		$this->time = microtime(true) - $time;
		return $this->passed;
	}
	public function obj_equals($obj) {
		return $obj === $this->object;
	}
}

class Test_Class extends Test_Object {
	protected $type = "Class";
	function __construct($class_name, $run_test) {
		if(class_exists(!$class_name)) {
			throw new \Exception("The class '$class_name' does not exist.");
		}
		if($run_test === true) {
			if(method_exists($this->name, "__construct")) {
				$construct = new \ReflectionMethod($this->name,"__construct");
				if($construct->getNumberOfRequiredParameters() != 0) {
					throw new \Exception("The constructor of class '$class_name' requires parameters.");
				}
			}
			$object = new $class_name();
			parent::__construct($object);
		} else {
			$this->class = $class_name;
			$this->name = $class_name;
		}
	}
}

class Test_Function extends Test_Instance {
	protected $errors = array();
	protected $type = "Function";

	function __construct($function) {
		if(function_exists($function)) {
			$this->name = $function;
		} else {
			throw new \Exception("Trying to create test interface for undefined function '$function'");
		}
	}

	function add_error($error, $failed=true) {
		try {
			parent::add_error($error);
			$name = $this->name;
			if($name{0} == '\\') {
				$name = substr($name, 1);
			}
			if($error->caller == $name) {
				$this->errors[] = $error;
				if($failed) $this->passed = false;
				return true;
			} else {
				throw new \Exception("Illegal argument. The argument error wasn't encountered in function \"".$this->name.'" but in function "'.$error->caller.'.');
			}
		}
		catch(\Exception $e) {
			throw $e;
			return false;
		}
	}
}

class Test_Method extends Test_Function {
	private $test_object = null;
	
	function __construct($test_object,$method) {
		if($test_object == null || method_exists($test_object, $method)) {
			$this->name = $method;
			$this->test_object = $test_object;
			$this->type = "Method";
		} else {
			throw new \Exception("Trying to create test interface for undefined method '".get_class($test_object)."->$method'");
		}
	}
	
	function test($run_test = true) {
		if($this->run_test && $run_test) {
			$method = $this->name;
			$start = microtime(true);
			$this->test_object->$method();
			$this->time = microtime(true) - $start;
		}
		return $this->passed;
	}
}
?>