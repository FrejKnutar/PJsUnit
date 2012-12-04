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
		if(PHPUnit::display() == "html") {
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

class PHPUnit {
	private static $initialization = true;
	private static $start_time = null;
	private static $failed_count = 0;
	private static $passed_count = 0;
	private static $objects = array();
	private static $current_object = null;
	private static $functions = array();
	private static $current_function = null;
	private static $css_file = "PHPUnit.css";
	private static $standalone_functions = 0;
	private static $method_suffix = "_test";
	private static $class_suffix = "_test";
	private static $object_suffix = "_test";
	private static $function_suffix = "_test";
	private static $display = "console";
	private static $errors = array();
	private static $str_array = array(
		"by: Frej Knutar",
		"Classes: ",
		"Functions: ",
		"Standalone functions: ",
		"Passed: ",
		"Failed: ",
		"Time: "
	);

	function __destruct() {
		foreach(get_declared_classes() as $class) {
			if(substr($class, - \strlen(PHPUnit::$class_suffix)) == PHPUnit::$class_suffix) {
				if(strpos($class,"\\") == false) {
					$class = "\\".$class;
				} 
			} 
		}
		$functions = get_defined_functions();
		foreach($functions['user'] as $function) {
			if(substr($function, - \strlen(PHPUnit::$function_suffix)) == PHPUnit::$function_suffix) {
				if(strpos($function,"\\") == false) {
					$function = "\\".$function;
				}
			} 
		}
	}

	static function method_suffix($method_suffix = null) {
		if($method_suffix != null) {
			if(gettype($method_suffix) == "string") {
				PHPUnit::$method_suffix = $method_suffix;
			} else {
				throw new \Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($display)." was given.");
			}
		} else {
			return PHPUnit::$method_suffix;
		}
	}
	
	static function display($display = null) {
		if($display != null) {
			if(gettype($display) == "string") {
				$display = strtolower($display);
				switch($display) {
					case "html":
					case "h":
						PHPUnit::$display = "html";
						break;
					case "console":
					case "c":
						PHPUnit::$display = "console";
						break;
					default:
						throw new Exception(__CLASS__."::".__METHOD__." doesn't recognice string '".$display."'.");
				}
			} else {
				throw new Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($display)." was given.");
			}
		} else {
			return PHPUnit::$display;
		}
	}
	
	function __construct($parameter = null) {
		if($parameter != null) {
			$this->test($parameter);
		}
	}
	
	static function test($parameter = null) {
		if($parameter == null) {
			$debug_backtrace = debug_backtrace();
			$caller = $debug_backtrace[1];
			if(isset($caller['class']) && $caller['class'] != null) {
				$class = $caller['class'];
				$object = new $class;
				PHPUnit::$current_object = new PHPUnit_TestObject($object);
				PHPUnit::$objects[] = PHPUnit::$current_object;
				PHPUnit::test_object();
			} else {
				$function = $caller['function'];
				PHPUnit::$current_function = new PHPUNIT_TestFunction($function);
				PHPUnit::$functions[] = PHPUnit::$current_function;
				PHPUnit::test_function(false);
			}
		} else {
			if(strtolower(gettype($parameter)) == "object") {
				PHPUnit::$current_object = new PHPUnit_TestObject($parameter);
				PHPUnit::$objects[] = PHPUnit::$current_object;
				PHPUnit::test_object();
			} else {
				PHPUnit::$current_function = new PHPUNIT_TestFunction($parameter);
				PHPUnit::$functions[] = PHPUnit::$current_function;
				PHPUnit::test_function();
			}
		}
		PHPUnit::$current_object = null;
		PHPUnit::$current_function = null;
	}
	
	private static function test_object() {
		PHPUnit::$current_object->test();
		if(PHPUnit::$current_object->passed()) {
			PHPUnit::$passed_count++;
		} else {
			PHPUnit::$failed_count++;
		}
	}
	
	private static function test_function($run = true) {
		PHPUnit::$current_function->test($run);
		if(PHPUnit::$current_function->passed()) {
			PHPUnit::$passed_count++;
		} else {
			PHPUnit::$failed_count++;
		}
	}
	private static function addFunction($name,$isfunction = true) {
		foreach(PHPUnit::$functions as $function){
			if($function->name() == $name) {
				return $function;
			}
		}
		PHPUnit::$standalone_functions++;
		$function = new PHPUnit_testFunction($name);
		if(!$isfunction) {
			$function->type("Assertions in file");
		}
		PHPUnit::$functions[] = $function;
		return $function;
	}
	private static function current_add_error($error) {
		if(PHPUnit::$current_object != null && isset($error["class"]) && $error["class"] == PHPUnit::$current_object->name()) {
			PHPUnit::$current_object->method_error(new Error($error),true);
		} elseif(PHPUnit::$current_function != null && $error['caller'] == PHPUnit::$current_function->name()) {
			PHPUnit::$current_function->add_error($error);
			PHPUnit::$current_function->passed(false);
		} else {
			$isfunction = true;
			if(isset($error['class'])) {
				$caller = $error['class']."->".$error['function'];
			} elseif(isset($error['class'])) {
				unset($error['class']);
				$caller = $error['function'];
			}
			$function = PHPUnit::addFunction($caller,$isfunction);
			$function->add_error($error);
			$function->passed(false);
		}
	}
	
	static function display_results() {
		PHPUnit::initialization();
		if(PHPUnit::display() == "html") {
			?><ol><?php
		}
		foreach(PHPUnit::$objects as $object) {
			$object->display();
		}
		foreach(PHPUnit::$functions as $function) {
			$function->display();
		}
		foreach(PHPUnit::$errors as $error) {
			$error->display();
		}
		if(PHPUnit::display() == "html") {
			?></ol><?php
			PHPUnit::html();
			?></div><?php
		} else {
			PHPUnit::console();
		}
	}
	
	private static function console() {
		if(count(PHPUnit::$objects)>0) {
			echo PHPUnit::$str_array[1].count(PHPUnit::$objects).PHP_EOL;
		}
		if(count(PHPUnit::$functions)>0) {
			echo PHPUnit::$str_array[2].count(PHPUnit::$functions).PHP_EOL;;
		}
		if(PHPUnit::$standalone_functions>0) {
			echo PHPUnit::$str_array[3].PHPUnit::$standalone_functions.PHP_EOL;;
		}
		echo PHPUnit::$str_array[4].PHPUnit::$passed_count.' ('.(count(PHPUnit::$objects)>0 ? (PHPUnit::$passed_count/(count(PHPUnit::$objects) + count(PHPUnit::$functions)))*100 .'%' : 'NA').")".PHP_EOL;
		echo PHPUnit::$str_array[5].PHPUnit::$failed_count.' ('.(count(PHPUnit::$objects)>0 ? (PHPUnit::$failed_count/(count(PHPUnit::$objects) + count(PHPUnit::$functions)))*100 .'%' : 'NA').")".PHP_EOL;
		$time = PHPUnit_timeToString(microtime(true) - PHPUnit::$start_time);
		if($time != '0') {
			echo PHPUnit::$str_array[6].$time." s".PHP_EOL;
		}
	}
	
	private static function html() {
		?><div class="PHPUnitResults"><?php
		if(count(PHPUnit::$objects)>0) {
			?><p><label><?php echo PHPUnit::$str_array[1];?></label>&nbsp<?php
				echo count(PHPUnit::$objects);
			?></p><?php
		}
		if(count(PHPUnit::$functions)>0) {
			?><p><label><?php echo PHPUnit::$str_array[2];?></label>&nbsp<?php
				echo count(PHPUnit::$functions);
			?></p><?php
		}
		if(PHPUnit::$standalone_functions>0) {
			?><p><label><?php echo PHPUnit::$str_array[3];?></label>&nbsp<?php
				echo PHPUnit::$standalone_functions;
			?></p><?php
		}
		?><p><label><?php echo PHPUnit::$str_array[4];?></label>&nbsp<?php
			echo PHPUnit::$passed_count;
			echo ' (';
			echo count(PHPUnit::$objects)>0 ? (PHPUnit::$passed_count/(count(PHPUnit::$objects) + count(PHPUnit::$functions)))*100 .'%' : 'NA';
			echo ')';
		?></p><p><label><?php echo PHPUnit::$str_array[5];?></label>&nbsp<?php
			echo PHPUnit::$failed_count;
			echo ' (';
			echo count(PHPUnit::$objects)>0 ? (PHPUnit::$failed_count/(count(PHPUnit::$objects) + count(PHPUnit::$functions)))*100 .'%' : 'NA';
			echo ')';
		?></p><?php 
		$time = PHPUnit_timeToString(microtime(true) - PHPUnit::$start_time);
		if($time != '0') {
			?><p><label><?php echo PHPUnit::$str_array[6];?></label>&nbsp<?php
				echo $time." s";
			?></p><?php
		}
		?></div><?php
	}
	
	static private function assertion_passed() {
		$i = 1;
		$debug_backtrace = debug_backtrace();
		$error=$debug_backtrace[$i];
		if(isset($debug_backtrace[$i+1])) {
			$caller = $debug_backtrace[$i+1];
			$error['caller']=$caller['function'];
			$error['type']=$caller['type'];
			if(isset($caller['class'])) {
				$error['class']=$caller['class'];
			} else {
				unset($error['class']);
			}
		} else {
			$caller = null;
			if(isset($error['class'])) {
				unset($error['class']);
			}
			$error['caller'] = null;
		}
		
		
		if((PHPUnit::$current_object == null && PHPUnit::$current_function == null) ||
			(PHPUnit::$current_object != null && isset($caller["class"]) && $caller["class"] != PHPUnit::$current_object->name()) || 
			(PHPUnit::$current_function != null && $caller["function"] == PHPUnit::$current_function->name())) {
			
			$debug_backtrace=debug_backtrace();
			$error=$debug_backtrace[$i];
			if(isset($debug_backtrace[$i+1])) {
				$caller = $debug_backtrace[$i+2];
				$error['caller']=$caller['function'];
			} else {
				$caller = null;
				$error['caller']=null;
			}
			$isfunction = true;
			if(isset($caller['class'])) {
				$caller = $caller['class']."->".$caller['function'];
			} elseif(isset($error['class'])) {
				unset($error['class']);
				$caller = $error['function'];
			}
			if(isset($caller) && isset($caller['function'])) {
				$error['caller'] = $caller['function'];
			} else {
				$caller = $error['file'];
				$isfunction = false;
			}
			$function = PHPUnit::addFunction($caller,$isfunction);
		}
	}
	
	static private function assertion_failed() {
		$i=1;
		$debug_backtrace = debug_backtrace();
		$error=$debug_backtrace[$i];
		if(isset($debug_backtrace[$i+1])) {
			$caller = $debug_backtrace[$i+1];
			$error['caller']=$caller['function'];
			if(isset($caller['type'])) {
				$error['type']=$caller['type'];
			} else {
				unset($error['type']);
			}
			if(isset($caller['class'])) {
				$error['class']=$caller['class'];
			} else {
				unset($error['class']);
			}
		} else {
			$caller = null;
			if(isset($error['class'])) {
				unset($error['class']);
			}
			$error['caller'] = null;
		}
		$error['passed']=false;
		PHPUnit::current_add_error($error);
	}
	
	static function assertTrue($bool) {
		if($bool === false) {
			PHPUnit::assertion_failed();
		} else {
			PHPUnit::assertion_passed();
		}
	}
	static function assertFalse($bool) {
		if($bool === true) {
			PHPUnit::assertion_failed();
		} else {
			PHPUnit::assertion_passed();
		}
	}
	
	static private function initialization() {
		if(PHPUnit::display() == "html") {
			PHPUnit::html_initialization();
		} else {
			PHPUnit::console_initialization();
		}
		PHPUnit::$start_time = microtime(true);
		PHPUnit::$initialization = false;
	}
	
	static private function console_initialization() {
		if(PHPUnit::$initialization) {
			echo strtoupper(__CLASS__).PHP_EOL;
			echo strtoupper(PHPUnit::$str_array[0]).PHP_EOL;
		}
	}
	
	static private function html_initialization() {
		if(PHPUnit::$initialization) {
			
			$path = __FILE__;
			$pos = strrpos($path,'/');
			if($pos == false) {
				$pos = strrpos($path,'\\');
			}
			$path = substr($path,0,$pos+1);
			
			if(file_exists($path.PHPUnit::$css_file)) {
				?><style type="text/css"><?php
					echo file_get_contents($path.PHPUnit::$css_file);
				?></style><?php
			}
			?><script type="text/javascript">
				
				// Dean Edwards/Matthias Miller/John Resig

				if (document.addEventListener) {
					document.addEventListener("DOMContentLoaded", PHPUnit_init, false);
				}

				/*@cc_on @*/
				/*@if (@_win32)
					document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
					var script = document.getElementById("__ie_onload");
					script.onreadystatechange = function() {
					if (this.readyState == "complete") {
						PHPUnit_init(); // call the onload handler
					}
					};
				/*@end @*/

				if (/WebKit/i.test(navigator.userAgent)) {
					var _timer = setInterval(function() {
					if (/loaded|complete/.test(document.readyState)) {
						PHPUnit_init();
					}
					}, 10);
				}

				window.onload = PHPUnit_init;
				
				function PHPUnit_Purge(d) {
					var a = d.attributes, i, l, n;
					if (a) {
						for (i = a.length - 1; i >= 0; i -= 1) {
							n = a[i].name;
							if (typeof d[n] === 'function') {
								d[n] = null;
							}
						}
					}
					a = d.childNodes;
					if (a) {
						l = a.length;
						for (i = 0; i < l; i += 1) {
							PHPUnit_Purge(d.childNodes[i]);
						}
					}
				}
				function PHPUnit_init() {
					if (arguments.callee.done) return;
					arguments.callee.done = true;
					if (_timer) clearInterval(_timer);
					var getElementsByClassName = function(className) {
							var hasClassName = new RegExp("(?:^|\\s)" + className + "(?:$|\\s)");
							var allElements = document.getElementsByTagName("*");
							var results = [];

							var element;
							for (var i = 0; (element = allElements[i]) != null; i++) {
								var elementClass = element.className;
								if (elementClass && elementClass.indexOf(className) != -1 && hasClassName.test(elementClass))
									results.push(element);
							}
							return results;
						}
					var PHPUnitResults = getElementsByClassName("PHPUnitResults");
					var PHPUnitDivs = getElementsByClassName("PHPUnit");
					var PHPUnitDiv = null;
					var PHPUnitResult = null;
					var ol,PHPUnitOl;
					
					if(PHPUnitDivs.length > 0) {
						PHPUnitDiv = PHPUnitDivs[0];
						if(PHPUnitDiv.getElementsByTagName("OL").length > 0) {
							PHPUnitOl = PHPUnitDiv.getElementsByTagName("OL")[0];
						} else {
							PHPUnitOL = document.createElement("OL");
							PHPUnitDiv.appendChild(PHPUnitOL);
						}
						for(var i=1;i<PHPUnitDivs.length;i++) {
							ol=PHPUnitDivs[i].getElementsByTagName("OL");
							for(var j=0; j<ol.length; j++) {
								for(var k=0; k<ol[j].childNodes.length;k++) {
									PHPUnitOl.appendChild(ol[j].childNodes[k]);
								}
								PHPUnit_Purge(ol[j]);
								ol[j].parentNode.removeChild(ol[j]);
							}
							PHPUnit_Purge(PHPUnitDivs[i]);
							PHPUnitDivs[i].parentNode.removeChild(PHPUnitDivs[i]);
						}
					}
					if(PHPUnitResults.length > 0) {
						for(var i=0;i<PHPUnitResults.length -1;i++) {
							PHPUnit_Purge(PHPUnitResults[i]);
							PHPUnitResults[i].parentNode.removeChild(PHPUnitResults[i]);
						}
						PHPUnitResult = PHPUnitResults[PHPUnitResults.length - 1];
					}
					if(PHPUnitDiv != null && PHPUnitResult != null) {
						PHPUnitDiv.appendChild(PHPUnitResult);
					}
				}
			</script>
			<div class="PHPUnit">
			<h1>PHPUnit</h1>
			<h4>by: Frej Knutar</h4>
			<?php
		} else {
			?><div class="PHPUnit"><?php
		}
	}
}
if(file_exists(dirname(__FILE__)."/PHPUnit/Test_Instance.php")) {
	include dirname(__FILE__)."/PHPUnit/Test_Instance.php";
} elseif(file_exists(dirname(__FILE__)."\PHPUnit\Test_Instance.php")) {
	include dirname(__FILE__)."\PHPUnit\Test_Instance.php";
}
$GLOBALS['PHPUnit'] = new PHPUnit();
?>