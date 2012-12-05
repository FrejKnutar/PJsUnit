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

class Error {
	private $file;
	private $line;
	private $function;
	private $class;
	private $type;
	private $args = array();
	private $passed;
	private $caller;
	private $padding = "        ";
	private $str_array = array(
		"File",
		"Row",
		"Function",
		"true",
		"false"
	);
	
	function name() { return $this->caller; }
	function file() { return $this->file; }
	function line() { return $this->line; }
	function function_name() { return $this->function; }
	function class_name() { return $this->class; }
	function type() { return $this->type; }
	function passed() { return $this->passed; }
	function caller() { return $this->caller; }

	function __construct($error) {
		$this->file = $error["file"];
		$this->line = int($error["line"]);
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
		$this->args = $error["args"];
		$this->passed = $error["passed"];
		if(isset($error["caller"])) {
			$this->caller = $error["caller"];
		} else {
			$this->caller = null;
		}
	}
	
	function display() {
		if(\PHPUnit::display() == "html") {
			$this->html();
		} else {
			$this->console();
		}
	}
	
	function console() {
		echo $this->padding.$this->str_array[1].": ".$this->line.PHP_EOL;
		echo $this->padding.$this->str_array[2].": ".$this->function.'(';
		$count = count($this->args);
		if($count > 0) foreach($this->args as $a) {
			if ($a==true) {
				echo $this->str_array[3];
			} elseif($a==false) {
				echo $this->str_array[4];
			} else {
				echo $a;
			}
			$count--;
			if($count>0) {
				echo ', ';
			}
		}
		echo ")".PHP_EOL;
	}
	
	function html() {
		?><li><label title="<?php echo $this->file;?>" class="file"><?php echo strtoupper($this->str_array[0]);?></label><label title="<?php echo $this->str_array[1];?>" class="row"><?php
				echo $this->line;
			?></label><label><span title="<?php echo $this->str_array[2];?>" class="function"><?php
				echo $this->function.'(';
			?></span><span title="argument(s)"><?php
				$count = count($this->args);
				if($count > 0) foreach($this->args as $a) {
					?><span class="argument"><?php
						if ($a==true) {
							echo $this->str_array[3];
						} elseif($a==false) {
							echo $this->str_array[4];
						} else {
							echo $a;
						}
					?></span><?php
					$count--;
					if($count>0) {
						echo ', ';
					}
				}
		?></span><span class="function">)</span>;</label></li><?php
	}	
}

function include_extract($path,$array = array()) {
	if(file_exists($path)) {
		extract($array);
		if(isset($name)) $name = substr($name,0,1) == '\\' ? substr($name,1) : $name;
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
		$suffix = \PHPUnit::display();
		$array['passed'] = $this->passed;
		$array['errors'] = array();
		foreach($this->errors as $e) {
			$array['errors'] = string($e);
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
		} elseif($bool != null) {
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

	private $class = "";
	private $passed_count	= 0;
	private $methods = array();
	private $current_method = null;
	private $method_suffix = null;
	private $was_timed = false;
	
	function passed_count()	{ return $this->passed_count; }
	function failed_count()	{ return $this->method_count()-$this->passed_count(); }
	function method_count()	{ return count($this->methods); }
	
	function __construct($test_object) {
		$this->method_suffix = \PHPUnit::method_suffix();
		$methods = get_class_methods($test_object);
		if($methods == null) {
			throw new \Exception("Parameter is not an object.");
		} else {
			$this->name = get_class($test_object);
			foreach($methods as $method) {
				$reflectionMethod = new \ReflectionMethod($this->name,$method);
				if (substr($method,-strlen($this->method_suffix)) == $this->method_suffix && $reflectionMethod->getNumberOfParameters() == 0) {
					$test_method = new PHPUnit_TestMethod($test_object,$method);
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