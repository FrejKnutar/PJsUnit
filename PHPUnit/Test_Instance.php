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
	if(file_exists($path)) {
		extract($array);
		if(isset($name)) $name = substr($name,0,1) == '\\' ? substr($name,1) : $name;
		return include($path);
	} else {
		if(strpos($path, '/') == false) {
			$path = str_replace("/", "\\", $path);
		} else {
			$path = str_replace("\\", "/", $path);
		}
		if(file_exists($path)) {
			extract($array);
			return include($path);
		}
	}
	return "File at location '$path' not found.".PHP_EOL;	
}

class Error {
	private $file;
	private $line;
	private $function;
	private $class;
	private $type;
	private $arguments = array();
	private $passed;
	private $caller;
	
	function __construct($error) {
		$this->file = $error["file"];
		$this->line = (int) $error["line"];
		$this->function = $error["function"];
		if(isset($error["class"])) {
			$this->class = $error["class"];
		} else {
			$this->class = null;
		}
		if(isset($error["type"])) {
			$this->type = $error["type"];
		} else {
			$this->type = null;
		}
		$this->arguments = $error["args"];
		$this->passed = $error["passed"];
		if(isset($error["caller"])) {
			$this->caller = $error["caller"];
		} else {
			$this->caller = null;
		}
	}
	
	function __toString() {
		$suffix = \PHPUnit::display();
		$array = array();
		$array['type'] = (string) __CLASS__;
		$array["file"] = $this->file;
		$array["line"] = $this->line;
		$array["function"] = $this->function;
		$array["class"] = $this->class;
		$array["type"] = $this->type;
		$array["arguments"] = array();
		foreach($this->arguments as $arg) {
			if($arg == true) {
				$array["arguments"][] = "true";
			} elseif($arg == false) {
				$array["arguments"][] = "false";
			} else {
				$array["arguments"][] = $arg;
			}
		}
		$array["passed"] = $this->passed;
		$array["caller"] = $this->caller;
		$array['string'] = "";
		$type = strtolower(stripslashes(str_replace(__NAMESPACE__,'',__CLASS__)));
		$dir = dirname(__FILE__);
		$path=$dir."/design/".$type."_".$suffix.".php";
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
	
	function __toString() {
		$suffix = \PHPUnit::display();
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
		$path=$dir."/design/".$type."_".$suffix.".php";
		return include_extract($path,$array);
	}

	function __get($name) {
		if(property_exists(__CLASS__, $name)) {
			return $this->$name;
		}
	}

	function __set($name, $value) {
		if($name == "passed" && is_bool($value)) {
			$this->passed = $bool;
			return true;
		}
		return false;
	}

	function test($run = true) {
		$function = $this->name;
		$start = microtime(true);
		if($run) $function();
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

	private $passed_count	= 0;
	private $methods = array();
	private $current_method = null;
	private $method_suffix = null;
	private $was_timed = false;
	
	function __get($name) {
		if(property_exists(__CLASS__, $name)) {
			return $this->$name;
		}
	}

	function __construct($test_object, $is_class = false) {
		if($is_class) {
			$this->type = "Class";
		}
		$this->method_suffix = \PHPUnit::method_suffix();
		$methods = get_class_methods($test_object);
		if($methods == null) {
			throw new \Exception("Parameter is not an object.");
		} else {
			$this->name = get_class($test_object);
			foreach($methods as $method) {
				$reflectionMethod = new \ReflectionMethod($this->name,$method);
				if (substr($method,-strlen($this->method_suffix)) == $this->method_suffix && $reflectionMethod->getNumberOfParameters() == 0) {
					$test_method = new Test_Method($test_object,$method);
					$this->methods[] = $test_method;
				}
			}
		}
	}
	
	function __toString() {
		$suffix = \PHPUnit::display();
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
		$path=$dir."/design/".$type."_".$suffix.".php";
		return include_extract($path,$array);
	}

	function add_error($error, $failed=true) {
		try {
			if($error->caller == $this->current_method->name) {
				$method = $this->current_method;
			} else {
				foreach($this->methods as $m) {
					if($m->name() ==  $error->name) {
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

	function test() {
		$time = microtime(true);
		foreach($this->methods as $method) {
			$this->current_method = $method;
			if($method->test()) {
				$this->passed_count++;
			}
		}
		$this->time = microtime(true) - $time;
		return $this->passed;
	}
	
}

class Test_Function extends Test_Instance {
	protected $errors = array();
	protected $type = "Function";
	
	function __construct($function) {
		$this->name = $function;
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
		parent::__construct($method);
		$this->test_object = $test_object;
		$this->type = "Method";
	}
	
	function test($run = true) {
		$method = $this->name;
		$start = microtime(true);
		$this->test_object->$method();
		$this->time = microtime(true) - $start;
		return $this->passed;
	}
}
?>