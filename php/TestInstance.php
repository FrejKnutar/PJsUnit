<?php
/**
 * This file contain classes whose objects hold objects, 
 * classes, methods and functions under test.
 * 
 * PHP version 5
 * 
 * @category Unit_Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
namespace PJsUnit;
/**
 * Includes a file and extracts variables which the file is then capable of using.
 * 
 * @param string $path  The path to where the file is located.
 * @param array  $array the array that is to be extracted.
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
 * Class that represents an assertion error. An assertion error 
 * is an error that occured while unit testing an object, class, 
 * method or function under test.
 * 
 * @category Unit_Testing
 * @package  PJsUnit
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
     * Constructs an Error from the Exception Array 
     * parameter.
     * 
     * @param array $error The array containing the error data. Index and their 
     *                     types that must be included: file, line (int), function, 
     *                     args, passed and file. Optional indexes and their types: 
     *                     caller, class and type.
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
     * Returns a textual representation of the object. Depending on the static 
     * string proporty design_prefix in the class PJsUnit different representation 
     * may occur. The file that is loaded and returned will be located in
     * "PJsUnit/design/prefix_error.php" where prefix is the value of
     * the proporty design_prefix in PJsUnit.
     * 
     * @return string The object converted to a string.
     *                
     */
    function __toString()
    {
        $prefix = \PJsUnit::designPrefix();
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
     * Magic method __get. Returns the value of proporty with proporty name $name 
     * if that proporty exists.
     * 	
     * @param string $name the property name of the property that is to be returned.
     * 
     * @return mixed       Returns the value of property $name if it exists.
     */
    function __get($name)
    {
        if (property_exists(__CLASS__, $name)) {
            return $this->$name;
        }
    }
}
/**
 * Skeleton code conatining methods and parameters for objects that are to conatin 
 * objects, classes, methods and functions under test.
 * 
 * @category Unit_Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
abstract class TestInstance
{
    protected $name = null;
    protected $passed = true;
    protected $time = null;
    protected $type;
    protected $run_test = true;

    /**
     * Returns a textual representation of the object. Depending on the static 
     * string proporty design_prefix in the class PJsUnit different representation 
     * may occur. The file that is loaded and returned will be located in
     * "PJsUnit/design/prefix_CLASS.php" where prefix is the value of
     * the proporty design_prefix in PJsUnit.
     * 
     * @return string The object converted to a string.
     *                
     */
    function __toString()
    {
        $prefix = \PJsUnit::designPrefix();
        $array['passed'] = $this->_passed;
        $array['errors'] = array();
        foreach ($this->errors as $e) {
            $array['errors'][] = (string) $e;
        }
        $array['type'] = $this->type;
        if (substr($this->name, 0, 1) == '\\') {
            $array['name'] = substr($this->name, 1);
        } else {
            $array['name'] = $this->name;
        }
        $array['time'] = $this->time;
        $array['string'] = "";
        $type = strtolower($this->type);
        $dir = dirname(__FILE__);
        $path=$dir."/design/".$prefix."_".$type.".php";
        return includeExtract($path, $array);
    }
    /**
     * Magic method __get. Returns the value of proporty with proporty name $name 
     * if that proporty exists.
     *  
     * @param string $name the property name of the property that is to be returned.
     * 
     * @return mixed       Returns the value of property $name if it exists.
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
    function __set($name, $value)
    {
        if (is_bool($value)) {
            switch($name) {
            case "passed":
                $this->_passed = $value;
                return true;
            case "run_test":
                $this->runTest = $value;
                return true;
            }
        }
        return false;
    }
    /**
     * Runs the function. If the function dosn't pass it's assertions errors will 
     * be added to the function.
     * 
     * @param boolean $run_test true if the object, class, method or function under 
     *                          test should be executed.
     * 
     * @return boolean          returns the $passed property. True indicates that 
     *                          the test was executed without errors, false 
     *                          indicates that there were errors when executing the 
     *                          test.
     */
    function test($run_test = true)
    {
        $start = microtime(true);
        if ($this->runTest && $run_test) {
            call_user_func_array($this->name, []);
        }
        $this->time = microtime(true) - $start;
        return $this->_passed;
    }
    /**
     * Skeleton code for the addError method. This method should add an error to 
     * the object, class, method or function under test.
     * 
     * @param TestObject $error  the error that is to be added to the object, 
     *                           class, method or function under test.
     * 
     * @param boolean    $failed true if the object, class, method or function 
     *                           instance didn't pass because of the added error 
     *                           else false.
     * 
     * @return boolean           true if the error was added successfully, else 
     *                           false.
     */
    function addError(Error $error, $failed = true)
    {
        return false;
    }
}
/**
 * Class that represents an object under test. An object under test is any object 
 * not created by the the test engine but is to be tested.
 * 
 * @category Unit_Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
class TestObject extends TestInstance
{
    protected $type = "Object";
    protected $class = null;
    protected $object = null;
    protected $passed_count = 0;
    protected $methods = array();
    protected $current_method = null;
    protected $was_timed = false;

    /**
     *  Creates an instance of the class that is to 
     * hold an object that is to be tested. The object that is to be hold is 
     * reffered to as the object under test.
     *  
     * @param Object $test_object the object that is to be tested. The object that 
     *                            is to be the object under test.
     * 
     * @param string $object_name the name of the object that is to be tested.
     * 
     * @return void
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
        $methodSuffix = \PJsUnit::methodSuffix();
        $methods = get_class_methods($test_object);
        $temp_methods = array();
        foreach ($methods as $method) {
            foreach ($this->methods as $m) {
                if ($m->name == $method) {
                    break(2);
                }
            }
            $reflectionMethod = new \ReflectionMethod($this->name, $method);
            if (substr($method, -strlen($methodSuffix)) == $methodSuffix
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
     * Magic method __set. Tries to change the value of the property with name 
     * $name to $value. If the parameter that is to changed is run_test the 
     * parameter of all methods under test in the methods proporty array will be 
     * changed as well.
     * 	
     * @param string $name  the property name of the property that is to be set.
     * 
     * @param mixed  $value the new value of the proporty.
     * 
     * @return boolean      true if the property with name $name was updated 
     *                      successfully else false.
     */
    function __set($name, $value)
    {
        if ($name == "run_test") {
            if (is_bool($value)) {
                $this->runTest = $value;
                foreach ($this->methods as $m) {
                    $m->$name = $value;
                }
                return true;
            }
        } else {
            parent::__set($name, $value);
        }
        return false;
    }
    /**
     * Returns a textual representation of the object
     * Browse the corresponding file in PJsUnit/design/*prefix*_*Type*.
     * 
     * @return string The object converted to a string.
     *                
     */
    function __toString()
    {
        $prefix = \PJsUnit::designPrefix();
        $array['passed'] = $this->_passed;
        $array['methods'] = array();
        foreach ($this->methods as $m) {
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
        return includeExtract($path, $array);
    }
    /**
     * Creates and pushes a TestMethod to the methods array property. The object 
     * under test must contain a method with the name $method which requires no 
     * parameters.
     * 
     * @param string  $method   The name of the method that is to be added to the 
     *                          object under test.
     * 
     * @param boolean $run_test true if the method should be executed when it is 
     *                          tested by the test engine.
     * 
     * @return boolean          true if the method was added successfully, else 
     *                          false.
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
     * Adds an error to the element in the methods array proporty that contains 
     * method under test where the error was encountered.
     * 
     * @param TestObject $error  the error that is to be added.
     * 
     * @param boolean    $failed true if the object under test failed to pass 
     *                           because of the added error, else false.
     * 
     * @return boolean           true if the error was added to the corresponding 
     *                           method under test successfully, else false.
     */
    function addError(Error $error, $failed=true)
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
     * Iterates over the elements in the methods array proporty and calls the test 
     * method for every iterated element. If the object under test contain a method 
     * which name is equal to the proporty set_up_name of PJsUnit it will be called 
     * before the iteration. If the object under test contain a method which name 
     * is equal to the proporty tear_down_name of PJsUnit it will be called after 
     * the iteration. The parameter passed_count will be updated with the number of 
     * methods under test that passed.
     * 
     * @param boolean $run_test true if the methods under test contained in the 
     *                          elements in the methods proporty should be 
     *                          executed, else false.
     * 
     * @return boolean          true if the passed property of every element in
     *                          the methods array parameter is true. Else false.
     */
    function test($run_test=true)
    {
        $time = microtime(true);
        $set_up_name = \PJsUnit::setUpName();
        $tear_down_name = \PJsUnit::tearDownName();
        if ($this->runTest && $run_test
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
                $this->passed_count++;
            }
        }
        if ($this->runTest && $run_test
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
     * Matches the pointer of the current object with the pointer of the parameter 
     * object.
     * 	
     * @param Object $obj The object which the current object should be matched 
     *                    against.
     * 
     * @return boolean    true if the pointers of the objects point to the same 
     *                    object, else false.
     */
    public function objEquals($obj)
    {
        return $obj === $this->object;
    }
}
/**
 * Class that represents a class under test. A class under test is an object, 
 * created by the test engine, under test. The class must contain a constructor 
 * that doesn't require any parameters.
 * 
 * @category Unit_Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
class TestClass extends TestObject
{
    protected $type = "Class";
    /**
     *  Creates an object that holds a class under test.
     * 	
     * @param string  $class_name the name of the class that is to be tested.
     * @param boolean $run_test   true if the methods under test that this class 
     *                            contains should be executed when the class under 
     *                            test is tested, else false.
     * 
     * @return
     */
    function __construct($class_name, $run_test = true)
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
 * Class that represents a function under test. A function under test is a 
 * predefined function that is to be tested by the test engine.
 * 
 * @category Unit_Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
class TestFunction extends TestInstance
{
    protected $errors = array();
    protected $type   = "Function";
    /**
     * Creates an object that holds a function under test.
     * 	
     * @param function $function the name of the function that is to become the 
     *                           function under test.
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
     * Adds an error to the function under test. The error must have occured while 
     * the function under test was executed.
     * 
     * @param TestObject $error  the error that is to be added.
     * 
     * @param boolean    $failed true if the function under test failed to pass 
     *                           because of the error that was added, else false.
     * 
     * @return boolean           true if the error was added successfully, else 
     *                           false.
     */
    function addError(Error $error, $failed=true)
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
 * Class that represents a method under test. A method under test is a method that 
 * is to be tested by the test engine and where the method is a defined method of 
 * either an object under test or a class under test.
 * 
 * @category Unit_Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
class TestMethod extends TestFunction
{
    private $_test_object = null;
    /**
     * Creates an object that holds a method under test. The method must either be 
     * contained by the object parameter.
     * 
     * @param Object $test_object the object that holds the method that is to 
     *                            become the method under test.
     * 
     * @param string $method      The name of the method that is to become the 
     *                            method under test.
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
     * Tests the method under test and adds error that occured in the method under 
     * test to the errors array proporty.
     * 
     * @param boolean $run_test true if the method under test shall be executed, 
     *                          else false.
     * 
     * @return value of the $passed proporty.
     */
    function test($run_test = true)
    {
        if ($this->runTest && $run_test) {
            $method = $this->name;
            $start = microtime(true);
            $this->_test_object->$method();
            $this->time = microtime(true) - $start;
        }
        return $this->_passed;
    }
}
?>