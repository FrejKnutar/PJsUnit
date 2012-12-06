<?php
class PHPUnit {
	private static $initialization = true;
	private static $start_time = null;
	private static $failed_count = 0;
	private static $passed_count = 0;
	private static $passed = true;
	private static $functions = array();
	private static $current_function = null;
	private static $classes = array();
	private static $objects = array();
	private static $current_object = null;
	private static $css_file = "PHPUnit.css";
	private static $errors = array();
	private static $function_suffix = "_test";
	private static $class_suffix = "_test";
	private static $method_suffix = "_test";
	private static $design_prefix = "console";
	private static $set_up_name = "set_up";
	private static $tear_down_name = "tear_down";
	private static $time = 0;

	function __construct() {}

	function __destruct() {
		$functions = get_defined_functions();
		foreach($functions['user'] as $function) {
			if(substr($function, - \strlen(PHPUnit::$function_suffix)) == PHPUnit::$function_suffix) {
				PHPUnit::add_function("\\".$function);
			} 
		}
		foreach(get_declared_classes() as $class) {
			if(substr($class, - \strlen(PHPUnit::$class_suffix)) == PHPUnit::$class_suffix) {
				PHPUnit::add_class($class);
			} 
		}
		PHPUnit::test();
		echo PHPUnit::toString();
	}

	private static function toString() {
		$prefix = PHPUnit::design_prefix();
		$array['passed'] = PHPUnit::$passed;
		$array['functions'] = array();
		foreach(PHPUnit::$functions as $f) {
			$array['functions'][] = (string) $f;
		}
		$array['classes'] = array();
		foreach(PHPUnit::$classes as $c) {
			$array['classes'][] = (string) $c;
		}
		$array['objects'] = array();
		foreach(PHPUnit::$objects as $o) {
			$array['objects'][] = (string) $o;
		}
		$array['time'] = PHPUnit::$time;
		$array['string'] = "";
		$array['passed_count'] = PHPUnit::$passed_count;
		$array['failed_count'] = PHPUnit::$failed_count;
		$array['tests'] = count(PHPUnit::$functions) + count(PHPUnit::$objects) + count(PHPUnit::$classes);
		$dir = dirname(__FILE__);
		$path=$dir."/PHPUnit/design/".$prefix.'_'.__CLASS__.".php";
		return PHPUnit\include_extract($path,$array);
	}

	public function __set($name, $value) {
		if(method_exists(__CLASS__, $name) && property_exists(__CLASS__, $name)) {
			$refl = new \ReflectionMethod(__CLASS__, $name);
	    if($refl->isPublic()) {
	        return PHPUnit::$name($value);
	    } else {
	    	throw new \Exception("Access to undeclared static property ".__CLASS__."::".$name.'.');
	    }
		} else {
			throw new \Exception("Access to undeclared static property ".__CLASS__."::".$name.'.');
		}
	}

	public function __get($name) {
		if(method_exists(__CLASS__, $name) && property_exists(__CLASS__, $name)) {
			$refl = new \ReflectionMethod(__CLASS__, $name);
	    if($refl->isPublic() && $refl->isStatic()) {
	        return PHPUnit::$name();
	    } else {
	    	throw new \Exception("Access to undeclared static property ".__CLASS__."::".$name.'.');
	    }
		} else {
			throw new \Exception("Access to undeclared static property ".__CLASS__."::".$name.'.');
		}
	}

	static function function_suffix($suffix = null) {
		if($suffix != null) {
			if(is_string($suffix)) {
				PHPUnit::$function_suffix = $suffix;
			} else {
				throw new \Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($suffix)." was given.");
			}
		}
		return PHPUnit::$function_suffix;
	}	

	static function class_suffix($suffix = null) {
		if($suffix != null) {
			if(is_string($suffix)) {
				PHPUnit::$class_suffix = $suffix;
			} else {
				throw new \Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($suffix)." was given.");
			}
		}
		return PHPUnit::$class_suffix;
	}

	static function object_suffix($suffix = null) {
		if($suffix != null) {
			if(is_string($suffix)) {
				PHPUnit::$object_suffix = $suffix;
			} else {
				throw new \Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($suffix)." was given.");
			}
		}
		return PHPUnit::$object_suffix;
	}

	static function method_suffix($suffix = null) {
		if($suffix != null) {
			if(is_string($suffix)) {
				PHPUnit::$method_suffix = $suffix;
			} else {
				throw new \Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($suffix)." was given.");
			}
		}
		return PHPUnit::$method_suffix;
	}
	
	static function design_prefix($prefix = null) {
		if($prefix != null) {
			if(gettype($prefix) == "string") {
				PHPUnit::$design_prefix = $prefix;
			} else {
				throw new Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($prefix)." was given.");
			}
		}
		return PHPUnit::$design_prefix;
	}
	
	static function set_up_name($name=null) {
		if($name != null) {
			if(gettype($name) == "string") {
				PHPUnit::$set_up_name = $name;
			} else {
				throw new Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($name)." was given.");
			}
		}
		return PHPUnit::$set_up_name;	
	}

	static function tear_down_name($name=null) {
		if($name != null) {
			if(gettype($name) == "string") {
				PHPUnit::$tear_down_name = $name;
			} else {
				throw new Exception(__CLASS__."::".__METHOD__." takes a string as argument, ".gettype($name)." was given.");
			}
		}
		return PHPUnit::$tear_down_name;	
	}

	static function test() {
		foreach(PHPUnit::$classes as $class) {
			PHPUnit::$current_object = $class;
			if($class->test()) {
				PHPUnit::$passed_count++;
			} else {
				PHPUnit::$failed_count++;
				PHPUnit::$passed = false;
			}
			PHPUnit::$time += $class->time;
		}
		foreach(PHPUnit::$objects as $object) {
			PHPUnit::$current_object = $object;
			if($object->test()) {
				PHPUnit::$passed_count++;
			} else {
				PHPUnit::$failed_count++;
				PHPUnit::$passed = false;
			}
			PHPUnit::$time += $object->time;
		}
		foreach(PHPUnit::$functions as $function) {
			PHPUnit::$current_function = $function;
			if($function->test()) {
				PHPUnit::$passed_count++;
			} else {
				PHPUnit::$failed_count++;
				PHPUnit::$passed = false;
			}
			PHPUnit::$time += $function->time;
		}
		PHPUnit::$current_object = null;
		PHPUnit::$current_function = null;
	}

	static function add_function($name) {
		if(function_exists($name)) {
			foreach(PHPUnit::$functions as $function){
				if($function->name == $name) {
					return $function;
				}
			}
			$reflection = new \ReflectionFunction($name);
			if($reflection->getNumberOfRequiredParameters() == 0) {
				$function = new PHPUnit\Test_Function($name);
				PHPUnit::$functions[] = $function;
				return $function;	
			}
		}
		return false;
	}

	static function add_class($class_name) {
		if(class_exists($class_name)) {
			foreach(PHPUnit::$classes as $class) {
				if($class->name == $class_name) {
					return $class;
				}
			}
			$class = new PHPUnit\Test_Object(new $class_name,true);
			PHPUnit::$classes[] = $class;
			return $class;
		}
		return false;
	}

	static function add_object($object) {
		if(is_object($object)) {
			$test_object = new PHPUnit\Test_Object($object);
			PHPUnit::$objects[] = $test_object;
			return test_object;
		}
		return false;
	}

	private static function current_add_error($error) {
		if(PHPUnit::$current_object != null && $error->class == PHPUnit::$current_object->name) {
			return PHPUnit::$current_object->add_error($error,true);
		} elseif(PHPUnit::$current_function != null) {
			$name = PHPUnit::$current_function->name;
			if ($name{0} == '\\') $name = substr($name, 1);
			if ($error->caller == $name) {
				return PHPUnit::$current_function->add_error($error, true);
			}
		}
		if(isset($error->class)) {
			echo var_dump($error);
			$test_instance = PHPUnit::add_class($error->class);
			$test_instance->add_method($error->caller,false);
		} else {
			$caller = $error->caller;
			$test_instance = PHPUnit::add_function($caller);
			$test_instance->run_test = false;
		}
		return $test_instance->add_error($error, true);
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
		if(isset($debug_backtrace[$i+1])) {
			$caller = $debug_backtrace[$i+1];
			if((PHPUnit::$current_object == null && PHPUnit::$current_function == null) ||
				(PHPUnit::$current_object != null && isset($caller["class"]) && $caller["class"] != PHPUnit::$current_object->name) || 
				(PHPUnit::$current_function != null && $caller["function"] == PHPUnit::$current_function->name)) {
				
				if(isset($caller['class'])) {
					$class = PHPUnit::add_class($caller['class']);
					$class->add_method($caller['function'],false);
				} else {
					$function = PHPUnit::add_function($caller['function']);
					$function->run_test = false;
				}
			}
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
		$error = new PHPUnit\Error($error);
		PHPUnit::current_add_error($error);
	}
	
	static function assert_true($bool) {
		if($bool === false) {
			PHPUnit::assertion_failed();
			return true;
		} else {
			PHPUnit::assertion_passed();
			return false;
		}
	}
	
	static function assert_false($bool) {
		if($bool === true) {
			PHPUnit::assertion_failed();
			return true;
		} else {
			PHPUnit::assertion_passed();
			return false;
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
$PHPUnit = new PHPUnit();
?>