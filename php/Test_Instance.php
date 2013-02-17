<?php
/**
 * This files contais all logic and design regarding objects that test methods, 
 * classes, functions and objects.
 * 
 * PHP version 5
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
namespace PHPUnit;
/**
 * Includes a file and extracts variables which the file is then capable of using.
 * 
 * @param \string $path  The path to where the file is located.
 * @param arrat   $array the array that is to be extracted.
 * 
 * @return string returns the returnvalue of the include call.
 *                If the file wasn't found an error string is returned.
 */
function includeExtract($path, array $array = array())
{
    if (isset($array['name'])) {
        $array['name'] = substr($array['name'], 0, 1) == '\\' ? 
                         substr($array['name'], 1) : 
                         $array['name'];
    }
    if (file_exists($path)) {
        extract($array);
        unset($array);
        return include $path;
    } else {
        if (strpos($path, '/') == false) {
            $path = str_replace("/", "\\", $path);
        } else {
            $path = str_replace("\\", "/", $path);
        }
        if (file_exists($path)) {
            extract($array);
            unset($array);
            return include $path;
        }
    }
    return "File at location '$path' not found.".PHP_EOL;    
}
/**
 * Class that represents an assertion error.
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
class Error
{
    private $_file = null;
    private $_row = null;
    private $_line = null;
    private $_function = null;
    private $_arguments = array();
    private $_passed = null;
    private $_caller = null;
    private $_class = null;
    private $_type = null;
    /**
     * Sole constructor of the class.
     * Constructs a TestError from the Exception Array parameter. 
     * 
     * @param array $error The array containging the error data that.
     * 
     * @return
     */
    function __construct($error)
    {
        $this->_file = $error["file"];
        $this->_row = (int) $error["line"];
        $this->_function = $error["function"];
        $this->_arguments = $error["args"];
        $this->_passed = $error["passed"];
        $file = file($this->_file);
        $this->line = trim($file[$this->_row-1]);
        if (isset($error["caller"])) {
            $this->caller = $error["caller"];
        }
        if (isset($error["class"])) {
            $this->class = $error["class"];
        }
        if (isset($error["type"])) {
            $this->type = $error["type"];
        }
    }
    /**
     * Returns a textual representation of the object
     * Browse the corresponding file in PHPUnit/design/*prefix*_*Type*.
     * 
     * @return string The object converted to a string.
     *                
     */
    function __toString()
    {
        $prefix = \PHPUnit::design_prefix();
        $array = array();
        $array['type'] = (string) __CLASS__;
        $array["file"] = $this->_file;
        $array["line"] = $this->line;
        $array["row"] = $this->_row;
        $array["function"] = $this->_function;
        $array["class"] = $this->class;
        $array["type"] = $this->type;
        $array["arguments"] = array();
        foreach ($this->_arguments as $arg) {
            $array["arguments"][] = print_r($arg, true);
        }
        $array["passed"] = $this->_passed;
        $array["caller"] = $this->caller;
        $array['string'] = "";
        $type = strtolower(stripslashes(str_replace(__NAMESPACE__, '', __CLASS__)));
        $dir = dirname(__FILE__);
        $path=$dir."/design/".$prefix."_".$type.".php";
        return includeExtract($path, $array);
    }
    /**
     * Magic method __get.
     * 	
     * @param string $name the property name of the property that is to be fetched.
     * 
     * @return mixed       Returns the value of property $name if it exists, 
     *                     else false.
     */
    function __get($name)
    {
        if (property_exists(__CLASS__, $name)) {
            return $this->$name;
        }
    }
}
/**
 * Skeleton code of objects, classes, functions and methods that are to be
 * tested by the program.
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
abstract class TestInstance
{
    protected $name = null;
    protected $passed = true;
    protected $time = null;
    protected $type;
    protected $run_test = true;

    /**
     * Returns a textual representation of the object
     * Browse the corresponding file in PHPUnit/design/*prefix*_*Type*.
     * 
     * @return string The object converted to a string.
     *                
     */
    function __toString()
    {
        $prefix = \PHPUnit::design_prefix();
        $array['passed'] = $this->_passed;
        $array['errors'] = array();
        foreach ($this->errors as $e) {
            $array['errors'][] = (string) $e;
        }
        $array['type'] = $this->type;
        $array['name'] = substr($this->name, 0, 1) == '\\' ? substr($this->name, 1) 
                                                           : $this->name;
        $array['time'] = $this->time;
        $array['string'] = "";
        $type = strtolower($this->type);
        $dir = dirname(__FILE__);
        $path=$dir."/design/".$prefix."_".$type.".php";
        return includeExtract($path, $array);
    }
    /**
     * Magic method __get.
     * 	
     * @param string $name the property name of the property that is to be fetched.
     * 
     * @return mixed       Returns the value of property $name if it exists, 
     *                     else false.
     */
    function __get($name)
    {
        if (property_exists(__CLASS__, $name)) {
            return $this->$name;
        }
    }
    /**
     * Magic method __set. Sets either the $run_test property or the $passed property
     * 	
     * @param string  $name  the property name of the property that is to be set.
     * 
     * @param boolean $value the new value of the proporty.
     * 
     * @return boolean      true if the property with name $name was updated 
     *                      successfully else false.
     */
    function __set($name, bool $value)
    {
        switch($name) {
        case "passed":
            $this->_passed = $value;
            return true;
        case "run_test":
            $this->run_test = $value;
            return true;
        }
        return false;
    }
    /**
     * Runs the function. If the function dosn't pass it's assertions 
     * errors will be added to the function.
     * 
     * @param boolean $run_test true if the function should be executed.
     * 
     * @return boolean          returns the $passed property.
     *                              .
     */
    function test($run_test = true)
    {
        $start = microtime(true);
        if ($this->run_test && $run_test) {
            call_user_func_array($this->name, []);
        }
        $this->time = microtime(true) - $start;
        return $this->_passed;
    }
    /**
     * Skeleton code for the addError method.
     * 
     * @param TestObject $error  the error that is to be added.
     * 
     * @param boolean    $failed true if the test instance failed 
     *                           to pass because of the error
     *                           else false.
     * 
     * @return boolean           true if the error was added
     *                           successfully, else false.
     */
    function addError(TestError $error, $failed)
    {
        return false;
    }
}
/**
 * Class that represents an object that is to be
 * tested by the program.
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
class TestObject extends TestInstance
{
    protected $type = "Object";
    protected $class = null;
    protected $object = null;
    protected $passed_count    = 0;
    protected $methods = array();
    protected $current_method = null;
    protected $was_timed = false;
    
    /**
     * Magic method __get.
     * 	
     * @param string $name the property name of the property that is to be fetched.
     * 
     * @return mixed       Returns the value of property $name if it exists, 
     *                     else false.
     */
    function __get($name)
    {
        if (property_exists(__CLASS__, $name)) {
            return $this->$name;
        }
        return null;
    }
    /**
     * Magic method __set. Calls the __set() of TestInstance.
     * Setting the property run_test will also set that property of every 
     * method that is to be tested.
     * 	
     * @param string $name  the property name of the property that is to be set.
     * 
     * @param mixed  $value the new value of the proporty.
     * 
     * @return boolean      true if the property with name $name was updated 
     *                      successfully else false.
     */
    function __set($name, bool $value)
    {
        parent::__set($name, $value);
        if ($name == "run_test") {
            if (is_bool($value)) {
                foreach ($this->methods as $m) {
                    $m->$name = $value;
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Sole constructor of the class.
     * 	
     * @param Object $test_object the object that is to be tested.
     * 
     * @param string $object_name the name of the object.
     * 
     * @return
     */
    function __construct($test_object, $object_name = null)
    {
        if (!is_object($test_object)) {
            throw new \Exception("Input parameter \$test_object is not an object");
        }
        $this->object = $test_object;
        $this->class = get_class($test_object);
        if ($object_name != null && is_string($object_name)) {
            $this->name = $object_name;
        } else {
            $this->name = $this->class;
        }
        $method_suffix = \PHPUnit::method_suffix();
        $methods = get_class_methods($test_object);
        $temp_methods = array();
        foreach ($methods as $method) {
            foreach ($this->methods as $m) {
                if ($m->name == $method) {
                    break(2);
                }
            }
            $reflectionMethod = new \ReflectionMethod($this->name, $method);
            if (substr($method, -strlen($method_suffix)) == $method_suffix
                && $reflectionMethod->getNumberOfRequiredParameters() == 0
            ) {
                $test_method = new TestMethod($test_object, $method);
                $temp_methods[] = $test_method;
            }
        }
        foreach ($temp_methods as $m) {
            $this->methods[] = $m;
        }
    }
    /**
     * Returns a textual representation of the object
     * Browse the corresponding file in PHPUnit/design/*prefix*_*Type*.
     * 
     * @return string The object converted to a string.
     *                
     */
    function __toString()
    {
        $prefix = \PHPUnit::design_prefix();
        $array['passed'] = $this->_passed;
        $array['methods'] = array();
        foreach ($this->methods as $m) {
            $array['methods'][] = (string) $m;
        }
        $array['type'] = $this->type;
        $array['name'] = $this->name;
        $array['time'] = $this->time;
        $array['passed_count'] = $this->_passed_count;
        $array['failed_count'] = count($this->methods) - $this->_passed_count;
        $array['string'] = "";
        $type = strtolower($this->type);
        $dir = dirname(__FILE__);
        $path=$dir."/design/".$prefix."_".$type.".php";
        return includeExtract($path, $array);
    }
    /**
     * Creates and pushes a TestMethod to the $methods array property.
     * The object must contain the method that is to be added.
     * 
     * @param string  $method   The name of the method that is to be
     *                          added.
     * 
     * @param boolean $run_test true if the method should be executed
     *                          when being tested.
     * 
     * @return boolean          true if the error was added
     *                          successfully, else false.
     */
    function addMethod($method, $run_test = true)
    {
        if ($this->object == null || method_exists($this->object, $method)) {
            $method = new TestMethod($this->object, $method);
            $method->run_test = $run_test;
            $this->methods[] = $method;
            return true;
        }
        return false;
    }
    /**
     * Adds an error to the method that the error was encountered in.
     * 
     * @param TestObject $error  the error that is to be added.
     * 
     * @param boolean    $failed true if the object failed 
     *                           to pass because of the error
     *                           else false.
     * 
     * @return boolean           true if the error was added
     *                           successfully, else false.
     */
    function addError(TestError $error, $failed=true)
    {
        try {
            if ($this->current_method != null
                && $error->caller == $this->current_method->name
            ) {
                $method = $this->current_method;
            } else {
                foreach ($this->methods as $m) {
                    if ($m->name ==  $error->caller) {
                        $method = $m;
                        break;
                    }
                }
            }
            if ($failed) {
                $this->_passed = false;
            }
            if (isset($method)) {
                return $method->addError($error, $failed);
            }
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
        return false;
    }
    /**
     * Runs the methods of the object. If the method dosn't pass it's assertions 
     * errors will be added to the assertion where they were encountered.
     * 
     * @param boolean $run_test true if the objects methods should be executed.
     * 
     * @return boolean          true if the $passed property of every instance in
     *                          the $method_array is true. Else false.
     *                              .
     */
    function test($run_test=true)
    {
        $time = microtime(true);
        $set_up_name = \PHPUnit::set_up_name();
        $tear_down_name = \PHPUnit::tear_down_name();
        if ($this->run_test && $run_test
            && method_exists($this->object, $set_up_name)
        ) {
            $reflection_method = new \ReflectionMethod($this->name, $set_up_name);
            if ($reflection_method->getNumberOfRequiredParameters() == 0) {
                $this->object->$set_up_name();
            }
        }
        foreach ($this->methods as $method) {
            $this->current_method = $method;
            if ($method->test($run_test)) {
                $this->_passed_count++;
            }
        }
        if ($this->run_test && $run_test
            && method_exists($this->object, $tear_down_name)
        ) {
            $reflection_method = new \ReflectionMethod($this->name, $tear_down_name);
            if ($reflection_method->getNumberOfRequiredParameters() == 0) {
                $this->object->$tear_down_name();
            }
        }
        $this->time = microtime(true) - $time;
        return $this->_passed;
    }
    /**
     * Matches the pointer of the current object with the 
     * pointer of the parameter object.
     * 	
     * @param Object $obj the object which the current
     *                    object should be matched against.
     * 
     * @return boolean    true if the pointers of the objects
     *                    point to the same object.
     */
    public function objEquals(Object $obj)
    {
        return $obj === $this->object;
    }
}
/**
 * Class that represents a class that is to be
 * tested by the program.
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
class TestClass extends TestObject
{
    protected $type = "Class";
    /**
     * Sole constructor of the class.
     * 	
     * @param string  $class_name the name of the class that
     *                            is to be tested.
     * @param boolean $run_test   true if methods should be
     *                            tested by default.
     * 
     * @return
     */
    function __construct($class_name, $run_test)
    {
        if (class_exists(!$class_name)) {
            throw new \Exception("The class '$class_name' does not exist.");
        }
        if ($run_test === true) {
            if (method_exists($this->name, "__construct")) {
                $construct = new \ReflectionMethod($this->name, "__construct");
                if ($construct->getNumberOfRequiredParameters() != 0) {
                    throw new \Exception(
                        "The constructor of class '$class_name' requires parameters."
                    );
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
/**
 * Class that represents a function that is to be
 * tested by the program.
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
class TestFunction extends TestInstance
{
    protected $errors = array();
    protected $type   = "Function";
    /**
     * Sole constructor of the class.
     * 	
     * @param function $function the function that is to be
     *                           tested
     * 
     * @return
     */
    function __construct($function)
    {
        if (function_exists($function)) {
            $this->name = $function;
        } else {
            throw new \Exception(
                "Trying to create test interface for undefined function '$function'"
            );
        }
    }
    /**
     * Adds an error to the function.
     * 
     * @param TestObject $error  the error that is to be added.
     * 
     * @param boolean    $failed true if the function failed 
     *                           to pass because of the error
     *                           else true.
     * 
     * @return boolean           true if the error was added
     *                           successfully, else false.
     */
    function addError(TestError $error, $failed=true)
    {
        try {
            parent::addError($error);
            $name = $this->name;
            if ($name{0} == '\\') {
                $name = substr($name, 1);
            }
            if ($error->caller == $name) {
                $this->errors[] = $error;
                if ($failed) {
                    $this->_passed = false;
                }
                return true;
            } else {
                throw new \Exception(
                    "Illegal argument. ".
                    "The argument error wasn't encountered in function \"".
                    $this->name.'" but in function "'.$error->caller.'.'
                );
            }
        }
        catch(\Exception $e) {
            throw $e;
            return false;
        }
    }
}

/**
 * Class that represents a method that is to be
 * tested by the program.
 * 
 * @category Unit_Testing
 * @package  PHPUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
class TestMethod extends TestFunction
{
    private $_test_object = null;
    /**
     * Only constructor of the class.
     * 
     * @param TestObject $test_object the object that
     *                                holds the method which 
     *                                is to be tested.
     * 
     * @param string     $method      The name of the method 
     *                                that is to be tested.
     * 
     * @return
     */
    function __construct($test_object, $method)
    {
        if ($test_object == null || method_exists($test_object, $method)) {
            $this->name = $method;
            $this->_test_object = $test_object;
            $this->type = "Method";
        } else {
            throw new \Exception(
                "Trying to create test interface for undefined method '"
                .get_class($test_object)."->$method'"
            );
        }
    }
    /**
     * Executes the current method.
     * If the method dosn't pass it's assertions 
     * errors will be added to the object.
     * 
     * @param boolean $run_test true if the method
     *                          that the object holds
     *                          shall be executed.
     *                          false will prevent
     *                          the current method to
     *                          be executed.
     * 
     * @return value of the $passed proporty.
     */
    function test($run_test = true)
    {
        if ($this->run_test && $run_test) {
            $method = $this->name;
            $start = microtime(true);
            $this->_test_object->$method();
            $this->time = microtime(true) - $start;
        }
        return $this->_passed;
    }
}
?>