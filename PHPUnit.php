<?php
/**
 * A <b>static</b> class that automatically tests desired code. See readme for more information on functionality.
 * @authorFrej P. Knutar
 * @staticThis class is static. Creating PHPUnit objects will not add additional functionality, but only call the destructor additional times.
 * @category Unit Testing
 * @exampleTo change attributes call the get and set methods:</br>
 * <b>PHPUnit</b>::attribute_name(<i>$desired_value</i>) </br>
 * note that not all attributes can be changed or returned.
 * @exampleCalling a method:</br><b>PHPUnit</b>::method_name(<i>$parameter</i>);
 * @exampleAdding a class to PHPUnit:</br><b>PHPUnit</b>::add_return_class(<i>"Class_Name"</i>);
 * @exampleAdding an object to PHPUnit:</br><b>PHPUnit</b>::add_return_object(new Class_Name());
 * @exampleAdding a function to PHPUnit:</br><b>PHPUnit</b>::add_return_function(<i>"function_name"</i>);
 * @tutorialif attributes have the values:</br>
 * <i>class_suffix</i>="_test"</br>
 * <i>function_suffix</i>="_test"</br>
 * <i>method_suffix</i>="_test"</br>
 * <i>build_up_name</i>="build_up"</br>
 * <i>tear_down_name</i>="tear_down"</br>
 * The <b>bold</b> classes, functions and methods will be added automatically to PHPUnit on shut down:</br>
 * (class) <b>Class_test</b> </br>
 * (class) ClassTest </br>
 * (method) <b>$added_obj->build_up()</b><br>
 * (method) <b>$added_obj->method_test</b><br>
 * (method) $added_obj->methodTest<br>
 * (method) <b>$added_obj->tear_down()</b><br>
 * (function) <b>fun_test()</b><br>
 * (function) funtest()
 */
class PHPUnit {
	/**
	 * the amount of functions, classes and objects that failed one, or more, of their their test cases.
	 * @varint
	 */
	private static $failed_count = 0;
	/**
	 * the amount of functions, classes and objects that passed all of their their test cases.
	 * @varint
	 */
	private static $passed_count = 0;
	/**
	 * True if all unit tests have passed, else false.
	 * @varbool
	 */
	private static $passed = true;
	/**
	 * an array holding all functions that are, or has been, to be tested.
	 * @vararray()
	 */
	private static $functions = array();
	/**
	 * The function that is currently being tested.
	 * @var\PHPUnit\Test_Function
	 */
	private static $current_function = null;
	/**
	 * An array holding all classes that are, or has been, to be tested.
	 * @vararray()
	 */
	private static $classes = array();
	/**
	 * The class that is currently being tested.
	 * @var\PHPUnit\Test_Class
	 */
	private static $current_class = null;
	/**
	 * An array holding all objects that are, or has been, to be tested.
	 * @vararray()
	 */
	private static $objects = array();
	/**
	 * The object that is currently being tested.
	 * @var\PHPUnit\Test_Object
	 */
	private static $current_object = null;
	/**
	 * The suffix that function names must have in order to be added automatically to be tested.
	 * @varString
	 */
	private static $function_suffix = "_test";
	/**
	 * The suffix that class names must have in order to be added automatically to be tested.
	 * @varString
	 */
	private static $class_suffix = "_test";
	/**
	 * The suffix that method names of classes and objects that are to be tested must have in order to be added automatically to be tested.
	 * @varString
	 */
	private static $method_suffix = "_test";
	/**
	 * The prefix that files must have in order to be used to display data about PHPUnit, functions, classes, objects and errors.
	 * @varString
	 * @example the value <b>console</b> will make objects of this class call the file <b>design/console_PHPUnit.php</b> when converted to a string.
	 */
	private static $design_prefix = "console";
	/**
	 * The name of the method that will be called first, if it exist and required no parameters,  by the library/test engine of all classes and objects that are to be tested.
	 * @varString
	 * @example the value <b>set_up</b> will make an object, that is to be tested, with a method called <b>set_up()</b> being called before any other of the methods of the object have been called.   
	 */
	private static $set_up_name = "set_up";
	/**
	 * The name of the method that will be called last, if it exist and required no parameters,  by the library/test engine of all classes and objects that are to be tested.
	 * @varString
	 * @examplethe value <b>tear_down</b> will make an object, that is to be tested, with a method called <b>tear_down()</b> being called after all test methods of the object have been called.   
	 */
	private static $tear_down_name = "tear_down";
	/**
	 * The time it took to run all unit tests.
	 * @varint   
	 */
	private static $time = 0;
	/**
	 * Stores all defined assertion methods that are callable.
	 * @varfunction[]
	 */
	private static $assertion_methods = array();

	private static $destroyed = false;

	/**
	 * Constructor of the PHPUnit class.
	 */
	function __construct() {}
	
	/**
	 * Destructor of the PHPUnit class. This will run every time a PHPUnit object is destroyed, even when shutting down.
	 * This method automatically adds classes and functions to the static variables and run the tests that are to be tested.
	 * The method also ouputs data about the tests to the std_out stream.
	 */
	function __destruct() {
		if(PHPUnit::$destroyed == false) {
			PHPUnit::$destroyed = true;
			$functions = get_defined_functions();
			foreach($functions['user'] as $function) {
				if(substr($function, - \strlen(PHPUnit::$function_suffix)) == PHPUnit::$function_suffix) {
					PHPUnit::add_return_function("\\".$function);
				} 
			}
			foreach(get_declared_classes() as $class) {
				if(substr($class, - \strlen(PHPUnit::$class_suffix)) == PHPUnit::$class_suffix) {
					PHPUnit::add_return_class($class);
				} 
			}
			PHPUnit::test();
			echo PHPUnit::toString();
		}
	}
	
	/**
	 * Method that is called when trying to convert the object into a String.
	 * To alter how the data is formatted see the file <b>design/console_PHPUnit.php</b>.
	 * @varString
	 */
	public function __toString() {
		return PHPUnit::toString();
	}
	
	/**
	 * Method that is called, internally, when trying to convert the object into a String.
	 * To alter how the data is formatted see the file <b>design/console_PHPUnit.php</b>.
	 * @varString
	 */	
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
	
	/**
	 * Generic "set" method of the class. Changes, and returns, the value of the desired attribute.
	 * @varDifferent depending on the attribute.
	 * @returnThe attribute that is to be set.
	 * @param$name The name of the attribute that should be changed.
	 * @param$value The value that the attribute should be changed to.
	 * @throwsException if the variable does not exist or if the attribute cant be changed externally.
	 * @example<b>$phpunit</b>->method_suffix = <i>"_test"</i>;
	 */
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
	
	/**
	 * Generic "get" method of the class. Returns the desired attribute if possible.
	 * @varDifferent depending on the attribute.
	 * @returnthe attribute that is to be fetched.
	 * @param$name The name of the attribute.
	 * @throwsException if the variable does not exist or if the attribute cant be fetched externally.
	 * @example<b>$phpunit</b>->design_prefix;
	 */
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
	static function __callStatic($name, $arguments) {
		if(count($arguments) == 1 && ((is_string($arguments[0]) && function_exists($arguments[0])) || (is_object($arguments[0]) && ($arguments[0] instanceof Closure)))) {
			$temp_arr = [$name=>$arguments[0]];
			PHPUnit::$assertion_methods = array_merge(PHPUnit::$assertion_methods,$temp_arr);
			return isset(PHPUnit::$assertion_methods[$name]) && PHPUnit::$assertion_methods[$name] == $arguments[0];
		} elseif(isset(PHPUnit::$assertion_methods[$name])) {
			$bool = call_user_func_array(PHPUnit::$assertion_methods[$name],$arguments);
			if(is_bool($bool) && $bool) {
				PHPUnit::assertion_passed();
			} else {
				PHPUnit::assertion_failed();
			}
			return $bool;
		} else {
			throw new Exception("The assertion method '$name' is not defined.");
		}
	}
	/**
	 * Get and set method of the static attribute <b>$function_suffix</b>.
	 * Changes the value of the attribute to the input String parameter if given. Always returns the  attribute.
	 * @varString
	 * @param$suffix The String that the suffix should be changed to.
	 * @returnthe <b>$function_suffix</b> attribute.
	 * @example<b>PHPUnit::method_suffix</b>(<i>"_test"</i>); sets the value of the attribute to <i>"_test"</i> and the method will now return <i>"_test"</i>.
	 */
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
	/**
	 * Get and set method of the static attribute <b>$class_suffix</b>.
	 * Changes the value of the attribute to the input String parameter if given. Always returns the  attribute.
	 * @varString
	 * @param$suffix The String that the suffix should be changed to.
	 * @returnthe <b>class_suffix</b> attribute.
	 * @example<b>PHPUnit::class_suffix</b>(<i>"_test"</i>); sets the value of the attribute to <i>"_test"</i> and the method will now return <i>"_test"</i>.
	 */
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

	/**
	 * Get and set method of the static attribute <b>$method_suffix</b>.
	 * Changes the value of the attribute to the input String parameter if given. Always returns the  attribute.
	 * @varString
	 * @param$suffix The String that the suffix should be changed to.
	 * @returnthe <b>method_suffix</b> attribute.
	 * @example<b>PHPUnit::method_suffix</b>(<i>"_test"</i>); sets the value of the attribute to <i>"_test"</i> and the method will now return <i>"_test"</i>.
	 */
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
	
	/**
	 * Get and set method of the static attribute <b>$design_prefix</b>.
	 * Changes the value of the attribute to the input String parameter if given. Always returns the  attribute.
	 * @param$prefix The String that the prefix should be changed to.
	 * @varString
	 * @returnthe <b>$design_prefix</b> attribute.
	 * @example<b>PHPUnit::design_prefix</b>(<i>"html"</i>); sets the value of the attribute to <i>"html"</i> and the method will now return <i>"html"</i>.
	 */
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

	/**
	 * Get and set method of the static attribute <b>$set_up_name</b>.
	 * Changes the value of the attribute to the input String parameter if given. Always returns the  attribute.
	 * @paramif a String parameter is sent the <b>$set_up_name</b> attribute is set to that value.
	 * @varString
	 * @returnthe <b>$set_up_name</b> attribute.
	 * @example<b>PHPUnit::set_up_name</b>(<i>"set_up"</i>); sets the value of the attribute to <i>"set_up"</i> and the method will now return <i>"set_up"</i>.
	 */
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
	
	/**
	 * Get and set method of the static attribute <b>$tear_down_name</b>.
	 * Changes the value of the attribute to the input String parameter if given. Always returns the  attribute.
	 * @paramif a String parameter is sent the <b>$tear_down_name</b> attribute is set to that value.
	 * @varString
	 * @returnthe <b>$tear_down_name</b> attribute.
	 * @example <b>PHPUnit::tear_down_name</b>(<i>"tear_down"</i>); sets the value of the attribute to <i>"tear_down"</i> and the method will now return <i>"tear_down"</i>.
	 */
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

	/**
	 * Runs the tests of all functions, classes and objects. Errors from failed test cases are added to the corresponding method or function.
	 * @varbool
	 * @returntrue if all tests passed, else false.
	 */
	private static function test() {
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
		return PHPUnit::$passed;
	}

	/**
	 * Adds a function that is to be tested.
	 * @param$name A string containing the name of the the function that is to be tested.
	 * @varbool or PHPUnit\Test_Function.
	 * @returnfalse if the function doesn't exist else a test representation of the function.
	 */
	private static function add_return_function($name) {
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
	
	static function add_function($name) {
		return PHPUnit::add_return_function($name) !== false;
	}

	/**
	 * Adds a class that is to be tested.
	 * @param$class_name A String containing the name of the class that is to be tested.
	 * @varbool or PHPUnit\Test_Object.
	 * @returnfalse if there is no class with the parameter name else a test representation of the Class.
	 */
	private static function add_return_class($class_name) {
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

	static function add_class($class_name) {
		return PHPUnit::add_return_class($class_name) !== false;
	}

	/**
	 * Adds an object that is to be tested.
	 * @paramThe object that is to be tested.
	 * @varbool or PHPUnit\Test_Object.
	 * @returnfalse if the parameter isn't an object else a test representation of the Class.
	 */
	static function add_return_object($object) {
		if(is_object($object)) {
			$test_object = new PHPUnit\Test_Object($object);
			PHPUnit::$objects[] = $test_object;
			return $test_object;
		}
		return false;
	}
	
	static function add_object($object) {
		return PHPUnit::add_return_object($object) !== false;
	}

	/**
	 * Adds an error to a function or method.
	 * <b>Side effect</b>: adds the function or class, object and/or method where the error was encountered to PHHUnit.
	 * @param$error The error that is to be added.
	 * @varbool
	 * @returntrue if the error was added succesfully, else false.
	 */
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
			$test_instance = PHPUnit::add_return_class($error->class);
			$test_instance->add_method($error->caller,false);
		} else {
			$caller = $error->caller;
			$test_instance = PHPUnit::add_return_function($caller);
			$test_instance->run_test = false;
		}
		return $test_instance->add_error($error, true);
	}
	
	/**
	 * Called when an assertion passes.</br>
	 * <b>Side effect</b>: adds the function or class, object and/or method where the error was encountered to PHHUnit.
	 */
	static private function assertion_passed() {
		$i = 2;
		$debug_backtrace = debug_backtrace();
		if(isset($debug_backtrace[$i+1])) {
			$caller = $debug_backtrace[$i+1];
			if((PHPUnit::$current_object == null && PHPUnit::$current_function == null) ||
				(PHPUnit::$current_object != null && isset($caller["class"]) && $caller["class"] != PHPUnit::$current_object->name) || 
				(PHPUnit::$current_function != null && $caller["function"] == PHPUnit::$current_function->name)) {
				
				if(isset($caller['class'])) {
					$class = PHPUnit::add_return_class($caller['class']);
					$class->add_method($caller['function'],false);
				} else {
					$function = PHPUnit::add_return_function($caller['function']);
					$function->run_test = false;
				}
			}
		}
	}
	
	/**
	 * Called when an assertion fails.</br>
	 * <b>Side effect</b>: adds the function or class, object and/or method where the error was encountered to PHHUnit.
	 */
	static private function assertion_failed() {
		$i=2;
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
}
if(file_exists(dirname(__FILE__)."/PHPUnit/Test_Instance.php")) {
	include dirname(__FILE__)."/PHPUnit/Test_Instance.php";
} elseif(file_exists(dirname(__FILE__)."\PHPUnit\Test_Instance.php")) {
	include dirname(__FILE__)."\PHPUnit\Test_Instance.php";
}
if(file_exists(dirname(__FILE__)."/PHPUnit/assertion_methods.php")) {
	include dirname(__FILE__)."/PHPUnit/assertion_methods.php";
} elseif(file_exists(dirname(__FILE__)."\PHPUnit\assertion_methods.php")) {
	include dirname(__FILE__)."\PHPUnit\assertion_methods.php";
}
$PHPUnit = new PHPUnit();
?>