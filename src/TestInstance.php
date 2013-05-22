<?php
/**
 * This file contain classes whose objects hold objects, 
 * classes, methods and functions under test.
 * 
 * PHP version 5
 * 
 * @category Unit Testing
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
 * @category Unit Testing
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
        $array["passed"] = $this->passed;
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
 * @category Unit Testing
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
    protected $runTest = true;
    static protected $needle = null;
    static protected $suffix = true;
    static protected $toString = null;

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
        $array['passed'] = $this->passed;
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
            if ($name == "name") {
                if ($this->name{0} == '\\') {
                    return substr($this->name, 1);
                } else {
                    return $this->name;
                }
            }
            return $this->$name;
        }
    }
    /**
     * Magic method __set. Sets either the $runTest property or the $passed property
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
                $this->passed = $value;
                return true;
            case "runTest":
                $this->runTest = $value;
                return true;
            }
        } elseif (is_string($value)) {
            switch($name) {
            case "prefix":
                $this->needle = $value;
                $this->suffix = false;
                return $this->needle == $value;
            case "suffix":
                $this->needle = $value;
                $this->suffix = true;
                return $this->needle == $value;
            }
        }
        return false;
    }
    /**
     * Runs the function. If the function dosn't pass it's assertions errors will 
     * be added to the function.
     * 
     * @param boolean $runTest true if the object, class, method or function under 
     *                          test should be executed.
     * 
     * @return boolean          returns the $passed property. True indicates that 
     *                          the test was executed without errors, false 
     *                          indicates that there were errors when executing the 
     *                          test.
     */
    function test($runTest = true)
    {
        $start = microtime(true);
        if ($this->runTest && $runTest) {
            call_user_func_array($this->name, array());
        }
        $this->time = microtime(true) - $start;
        return $this->passed;
    }
    /**
     * Changes the toString method that represent instances of the class as Strings
     * to the input function.
     * 
     * @param boolean $fun The function that is to be the toString ethod of the 
     *                     class
     * 
     * @return boolean     returns true if the function was changed successully, 
     *                     else false.
     */
    static function toString($fun) {
        if (((is_string($fun) 
            && function_exists($fun)) 
            || (is_object($fun) 
            && ($fun instanceof Closure)))
        ) {
            $this->toString = $fun;
            return true;
        }
        return false;
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
    static function prefix($value = null) {
        if ($value != null
            && is_string($value)) {
            self::$needle = $value;
            self::$suffix = false;
        }
        if (!self::$suffix) {
            return self::$needle;
        }
        return false;
    }
    static function suffix($value = null) {
        if ($value != null
            && is_string($value)) {
            self::$needle = $value;
            self::$suffix = true;
        }
        if (self::$suffix) {
            return self::$needle;
        }
        return false;
    }
}
/**
 * Class that represents an protected $toString = null;object under test. An object under test is any object 
 * not created by the the test engine but is to be tested.
 * 
 * @category Unit Testing
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
    protected $passedCount = 0;
    protected $methods = array();
    protected $currentMethod = null;
    protected $wasTimed = false;
    static protected $setUpName = "";
    protected $setUp = null;
    static protected $tearDownName = "";
    protected $tearDown = null;

    /**
     *  Creates set_up_namean instance of the class that is to 
     * hold an object that is to be tested. The object that is to be hold is 
     * reffered to as the object under test.
     *  
     * @param Object $testObject the object that is to be tested. The object that 
     *                            is to be the object under test.
     * 
     * @param string $objectName the name of the object that is to be tested.
     * 
     * @return void
     */
    function __construct($testObject, $objectName = null)
    {
        if (!is_object($testObject)) {
            throw new \Exception("Input parameter \$testObject is not an object");
        }
        $this->object = $testObject;
        $this->class = get_class($testObject);
        if ($objectName != null && is_string($objectName)) {
            $this->name = $objectName;
        } else {
            $this->name = $this->class;
        }
        $methods = get_class_methods($testObject);
        $tempMethods = array();
        $needle = TestMethod::suffix();
        if ($needle == false) {
            $needle = TestMethod::prefix();
            $suffix = false;
        } else {
            $suffix = true;
        }
        foreach ($methods as $method) {
            foreach ($this->methods as $m) {
                if ($m->name == $method) {
                    break(2);
                }
            }
            $reflectionMethod = new \ReflectionMethod($this->object, $method);
            if ($reflectionMethod->getNumberOfRequiredParameters() == 0) {
                if ($method == $this->setUpName) {
                    $this->setUp = $method;
                } elseif ($method == $this->tearDownName) {
                    $this->tearDown = $method;
                } elseif ($suffix) {
                    if (substr($method, -strlen($needle)) == $needle) {
                        $testMethod = new TestMethod($testObject, $method);
                        $tempMethods[] = $testMethod;
                    }
                } else {
                    if (substr($method, 0, strlen($needle)) == $needle) {
                        $testMethod = new TestMethod($testObject, $method);
                        $tempMethods[] = $testMethod;
                    }
                }
            }
        }
        $this->methods = $tempMethods;
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
            $ReflectionClass = new \ReflectionClass(__CLASS__);
            $property = $ReflectionClass->getProperty($name);
            if ($property->isStatic()) {
                return $this::$$name;
            } elseif ($name == "name") {
                if ($this->name{0} == '\\') {
                    return substr($this->name, 1);
                } else {
                    return $this->name;
                }
            } else {
                return $this->$name;
            }
        }
    }
    /**
     * Magic method __set. Tries to change the value of the property with name 
     * $name to $value. If the parameter that is to changed is runTest the 
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
        if (property_exists(__CLASS__, $name)) {
            if ($name == "runTest") {
                if (is_bool($value)) {
                    $this->runTest = $value;
                    foreach ($this->methods as $m) {
                        $m->$name = $value;
                    }
                    return true;
                }
            } else {
                return parent::__set($name, $value);
            }
        } else {
            return parent::__set($name, $value);
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
        $array['passed'] = $this->passed;
        $array['methods'] = array();
        foreach ($this->methods as $m) {
            $array['methods'][] = (string) $m;
        }
        $array['type'] = $this->type;
        $array['name'] = $this->name;
        $array['time'] = $this->time;
        $array['passedCount'] = $this->passedCount;
        $array['failedCount'] = count($this->methods) - $this->passedCount;
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
     * @param string  $method  The name of the method that is to be added to the 
     *                         object under test.
     * 
     * @param boolean $runTest true if the method should be executed when it is 
     *                         tested by the test engine.
     * 
     * @return boolean true if the method was added successfully, else false.
     */
    function addMethod($method, $runTest = true)
    {
        if ($this->object == null || method_exists($this->object, $method)) {
            $method = new TestMethod($this->object, $method);
            $method->runTest = $runTest;
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
            if ($this->currentMethod != null
                && $error->caller == $this->currentMethod->name
            ) {
                $method = $this->currentMethod;
            } else {
                foreach ($this->methods as $m) {
                    if ($m->name ==  $error->caller) {
                        $method = $m;
                        break;
                    }
                }
            }
            if ($failed) {
                $this->passed = false;
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
     * the iteration. The parameter passedCount will be updated with the number of 
     * methods under test that passed.
     * 
     * @param boolean $runTest true if the methods under test contained in the 
     *                         elements in the methods proporty should be 
     *                         executed, else false.
     * 
     * @return boolean         true if the passed property of every element in
     *                         the methods array parameter is true. Else false.
     */
    function test($runTest=true)
    {
        $time = microtime(true);
        if ($this->runTest && $runTest && $this->setUp != null) {
            call_user_func_array(array($this->object, $this->setUp), array());
        }
        foreach ($this->methods as $method) {
            $this->currentMethod = $method;
            if ($method->test($runTest)) {
                $this->passedCount++;
            }
        }
        if ($this->runTest && $runTest && $this->tearDown != null) {
            call_user_func_array(array($this->object, $this->tearDown), array());
        }
        $this->time = microtime(true) - $time;
        return $this->passed;
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
    static function setUpName($name = null) {
        if ($name != null
            && is_string($name)) {
            self::$setUpName = $name;
        }
    }
    static function tearDownName($name = null) {
        if ($name != null
            && is_string($name)) {
            self::$tearDownName = $name;
        }
    }
}
/**
 * Class that represents a class under test. A class under test is an object, 
 * created by the test engine, under test. The class must contain a constructor 
 * that doesn't require any parameters.
 * 
 * @category Unit Testing
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
     * @param string  $className the name of the class that is to be tested.
     * @param boolean $runTest    true if the methods under test that this class 
     *                            contains should be executed when the class under 
     *                            test is tested, else false.
     * 
     * @return
     */
    function __construct($className, $runTest = true)
    {
        if (class_exists(!$className)) {
            throw new \Exception("The class '$className' does not exist.");
        }
        if ($runTest === true) {
            if (method_exists($this->name, "__construct")) {
                $construct = new \ReflectionMethod($this->name, "__construct");
                if ($construct->getNumberOfRequiredParameters() != 0) {
                    throw new \Exception(
                        "The constructor of class '$className' requires parameters."
                    );
                }
            }
            $object = new $className();
            parent::__construct($object);
        } else {
            $this->class = $className;
            $this->name = $className;
        }
    }
}
/**
 * Class that represents a function under test. A function under test is a 
 * predefined function that is to be tested by the test engine.
 * 
 * @category Unit Testing
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
     * Magic method __set. Sets either the $runTest property or the $passed property
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
        if ($name == "name") {
            if (strtolower($value) == strtolower($this->name)) {
                $this->name = $value;
            } else {
                throw new \Exception(
                    "Trying to change the function name of defined function ".
                    "\"$this->name\" to \"$value\""
                );
            }
        } else {
            return parent::__set($name, $value);
        }
        return false;
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
            if ($this->name{0} == '\\') {
                $name = substr($this->name, 1);
            } else {
                $name = $this->name;
            }
            if (strtolower($error->caller) == strtolower($name)) {
                if ($this->name{0} == '\\') {
                    $this->name = '\\'.$error->caller;
                } else {
                    $this->name = $error->caller;
                }
                $this->errors[] = $error;
                if ($failed) {
                    $this->passed = false;
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
 * @category Unit Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
class TestMethod extends TestFunction
{
    private $_testObject = null;
    /**
     * Creates an object that holds a method under test. The method must either be 
     * contained by the object parameter.
     * 
     * @param Object $testObject the object that holds the method that is to 
     *                           become the method under test.
     * 
     * @param string $method     The name of the method that is to become the 
     *                           method under test.
     * 
     * @return
     */
    function __construct($testObject, $method)
    {
        if ($testObject == null || method_exists($testObject, $method)) {
            $this->name = $method;
            $this->_testObject = $testObject;
            $this->type = "Method";
        } else {
            throw new \Exception(
                "Trying to create test interface for undefined method '"
                .get_class($testObject)."->$method'"
            );
        }
    }
    /**
     * Tests the method under test and adds error that occured in the method under 
     * test to the errors array proporty.
     * 
     * @param boolean $runTest true if the method under test shall be executed, 
     *                         else false.
     * 
     * @return value of the $passed proporty.
     */
    function test($runTest = true)
    {
        if ($this->runTest && $runTest) {
            $method = $this->name;
            $start = microtime(true);
            $this->_testObject->$method();
            $this->time = microtime(true) - $start;
        }
        return $this->passed;
    }
}
?>