<?php
/**
 * This class is a static class. All methods that are to be called, externally, from this class are static.
 * Depending on information in the <b>PHPUnit.ini</b> file the functionality will differ. Classes and functions will be added automatically to the Class.
 * Functions and object that calls <i>Assertion</i> methods will be added automatically to the Class.
 * @author Frej P. Knutar
 * @static This class is static. Creating PHPUnit objects will not add additional functionality, but only call the descrucotr additional times.
 * @category Unit Testing
 * @example Calling a method: <b>PHPUnit</b>::method_name(<i>$parameter</i>).
 */
class PHPUnit {
	/**
	 * the amount of functions, classes and objects that failed one, or more, of their their test cases.
	 * @var int
	 */
	private static $failed_count = 0;
	/**
	 * the amount of functions, classes and objects that passed all of their their test cases.
	 * @var int
	 */
	private static $passed_count = 0;
	/**
	 * True if all unit tests have passed, else false.
	 * @var bool
	 */
	private static $passed = true;
	/**
	 * an array holding all functions that are, or has been, to be tested.
	 * @var array()
	 */
	private static $functions = array();
	/**
	 * The function that is currently being tested.
	 * @var \PHPUnit\Test_Function
	 */
	private static $current_function = null;
	/**
	 * An array holding all classes that are, or has been, to be tested.
	 * @var array()
	 */
	private static $classes = array();
	/**
	 * An array holding all objects that are, or has been, to be tested.
	 * @var array()
	 */
	private static $objects = array();
	/**
	 * The Object or class that is currently being tested.
	 * @var \PHPUnit\Test_Object
	 */
	private static $current_object = null;
	/**
	 * The suffix that function names must have in order to be added automatically to be tested.
	 * @var String
	 */
	private static $function_suffix = "_test";
	/**
	 * The suffix that class names must have in order to be added automatically to be tested.
	 * @var String
	 */
	private static $class_suffix = "_test";
	/**
	 * The suffix that method names of classes and objects that are to be tested must have in order to be added automatically to be tested.
	 * @var String
	 */
	private static $method_suffix = "_test";
	/**
	 * The prefix that files must have in order to be used to display data about PHPUnit, functions, classes, objects and errors.
	 * @var String
	 * @example the value <b>console</b> will make objects of this class call the file <b>design/console_PHPUnit.php</b> when converted to a string.
	 */
	private static $design_prefix = "console";
	/**
	 * The name of the method that will be called first, if it exist and required no parameters,  by the library/test engine of all classes and objects that are to be tested.
	 * @var String
	 * @example the value <b>set_up</b> will make an object, that is to be tested, with a method called <b>set_up()</b> being called before any other of the methods of the object have been called.   
	 */
	private static $set_up_name = "set_up";
	/**
	 * The name of the method that will be called last, if it exist and required no parameters,  by the library/test engine of all classes and objects that are to be tested.
	 * @var String
	 * @example the value <b>tear_down</b> will make an object, that is to be tested, with a method called <b>tear_down()</b> being called after all test methods of the object have been called.   
	 */
	private static $tear_down_name = "tear_down";
	/**
	 * The time it took to run all unit tests.
	 * @var int   
	 */
	private static $time = 0;

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
	
	/**
	 * Method that is called when trying to convert the object into a String.
	 * To alter how the data is formatted see the file <b>design/console_PHPUnit.php</b>.
	 */
	public function __toString() {
		return PHPUnit::toString();
	}
	
	/**
	 * Method that is called, internally, when trying to convert the object into a String.
	 * To alter how the data is formatted see the file <b>design/console_PHPUnit.php</b>.
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
	 * Method that is called when trying to change, set, an attribute (class variable), externally.
	 * The method calls the appropriate method to change the attribute.
	 * @var Different depending on the attribute.
	 * @return The attribute that is to be set.
	 * @throws Exception if the variable does not exist or if the attribute cant be changed externally.
	 * @example <b>$phpunit->method_suffix</b> = <i>"_test"</i>; calls <b>PHPUnit::method_suffix</b>(<i>"_test"</i>);
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
	 * Method that is called when trying to fetch, or get, an attribute (class variable), externally.
	 * The method calls the appropriate method ad returns the value of that method.
	 * @var Different depending on the attribute.
	 * @return the attribute that is to be fetched.
	 * @throws Exception if the variable does not exist or if the attribute cant be fetched externally.
	 * @example <b>$phpunit->method_suffix</b> calls <b>PHPUnit::method_suffix</b>();
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

	/**
	 * Returns the <b>$function_suffix</b> attribute.
	 * @param if a String parameter is sent the <b>$function_suffix</b> attribute is set to that value.
	 * @var String
	 * @return the <b>$function_suffix</b> attribute.
	 * @example <b>PHPUnit::method_suffix</b>(<i>"_test"</i>); sets the value of the attribute to <i>"_test"</i> and the method will now return <i>"_test"</i>.
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
	 * Returns the <b>$class_suffix</b> attribute.
	 * @param if a String parameter is sent the <b>$class_suffix</b> attribute is set to that value.
	 * @var String
	 * @return the <b>class_suffix</b> attribute.
	 * @example <b>PHPUnit::class_suffix</b>(<i>"_test"</i>); sets the value of the attribute to <i>"_test"</i> and the method will now return <i>"_test"</i>.
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
	 * Returns the <b>$method_suffix</b> attribute.
	 * @param if a String parameter is sent the <b>$method_suffix</b> attribute is set to that value.
	 * @var String
	 * @return the <b>method_suffix</b> attribute.
	 * @example <b>PHPUnit::method_suffix</b>(<i>"_test"</i>); sets the value of the attribute to <i>"_test"</i> and the method will now return <i>"_test"</i>.
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
	 * Returns the <b>$design_prefix</b> attribute.
	 * @param if a String parameter is sent the <b>$design_prefix</b> attribute is set to that value.
	 * @var String
	 * @return the <b>$design_prefix</b> attribute.
	 * @example <b>PHPUnit::design_prefix</b>(<i>"html"</i>); sets the value of the attribute to <i>"html"</i> and the method will now return <i>"html"</i>.
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
	 * Returns the <b>$set_up_name</b> attribute.
	 * @param if a String parameter is sent the <b>$set_up_name</b> attribute is set to that value.
	 * @var String
	 * @return the <b>$set_up_name</b> attribute.
	 * @example <b>PHPUnit::set_up_name</b>(<i>"set_up"</i>); sets the value of the attribute to <i>"set_up"</i> and the method will now return <i>"set_up"</i>.
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
	 * Returns the <b>$tear_down_name</b> attribute.
	 * @param if a String parameter is sent the <b>$tear_down_name</b> attribute is set to that value.
	 * @var String
	 * @return the <b>$tear_down_name</b> attribute.
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
	 * @var bool
	 * @return true if all tests passed, else false.
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
	 * Adds a function to the Class.
	 * @param the name of the function that is to be added.
	 * @var bool or PHPUnit\Test_Function.
	 * @return false if the function doesn't exist else a test representation of the function.
	 */
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
	
	/**
	 * Adds a class to the Class.
	 * @param The name of the class that is to be tested.
	 * @var bool or PHPUnit\Test_Object.
	 * @return false if there is no class with the parameter name else a test representation of the Class.
	 */
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

	/**
	 * Adds an object to the Class.
	 * @param The object that is to be tested.
	 * @var bool or PHPUnit\Test_Object.
	 * @return false if the parameter isn't an object else a test representation of the Class.
	 */
	static function add_object($object) {
		if(is_object($object)) {
			$test_object = new PHPUnit\Test_Object($object);
			PHPUnit::$objects[] = $test_object;
			return $test_object;
		}
		return false;
	}
	
	/**
	 * Adds an error to a function or method.
	 * <b>Side effect</b>: adds the function or class, object and/or method where the error was encountered to PHHUnit.
	 * @param The error that is to be added.
	 * @var bool.
	 * @return true if the error was added succesfully, else false.
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
	
	/**
	 * Called when an assertion passes.
	 * <b>Side effect</b>: adds the function or class, object and/or method where the assertion was called to PHHUnit.
	 */
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
	
	/**
	 * Called when an assertion fails.
	 * <b>Side effect</b>: adds the function or class, object and/or method where the assertion was called to PHHUnit.
	 */
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
	
	/**
	 * Evaluates the parameter. If the parameter is evaluated to <i>true</i> the assertion passes else the assertion fails.
	 * A failed assertion adds an error to the method or function to the function or method where the assertion was called. 
	 * @param a bool that is to be evaluated.
	 * <b>Side effect</b>: adds the function or class, object and/or method where the assertion was called to PHHUnit.
	 * @method Assertion
	 * @var bool
	 * @return true if the assertion passed, else false.
	 */
	static function assert_true($bool) {
		if($bool === false) {
			PHPUnit::assertion_failed();
			return true;
		} else {
			PHPUnit::assertion_passed();
			return false;
		}
	}
	
	/**
	 * Evaluates the parameter. If the parameter is evaluated to <i>true</i> the assertion passes else the assertion fails.
	 * A failed assertion adds an error to the method or function to the function or method where the assertion was called.
	 * @param a bool that is to be evaluated.
	 * <b>Side effect</b>: adds the function or class, object and/or method where the assertion was called to PHHUnit.
	 * @method Assertion
	 * @var bool
	 * @return true if the assertion passed, else false.
	 */
	static function assert_false($bool) {
		if($bool === true) {
			PHPUnit::assertion_failed();
			return true;
		} else {
			PHPUnit::assertion_passed();
			return false;
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