<?php
/**
 * This file contains the main class of the the test engine whose class holds 
 * objects, classes and functions under test.
 * 
 * PHP version 5
 * 
 * @category Unit Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     https://github.com/FrejKnutar/PJsUnit
 */
/**
 * Class that represents a complete test engine. The class contains objects, 
 * classes and functions under test. All data from this class is stored statically 
 * so reaching data should be used by accessing the static methods for this class.
 * The class will automatically echo the results from the test to standard out.
 * 
 * @category Unit Testing
 * @package  PJsUnit
 * @author   Frej Knutar <frej.knutar@gmail.com>
 * @license  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * @link     http:https://github.com/FrejKnutar/PJsUnit
 */
class PJsUnit
{
    private static $_failedCount = 0;
    private static $_passedCount = 0;
    private static $_passed = true;
    private static $_functions = array();
    private static $_currentFunction = null;
    private static $_classes = array();
    private static $_currentClass = null;
    private static $_objects = array();
    private static $_currentObject = null;
    private static $_functionSuffix = "_test";
    private static $_classSuffix = "Test";
    private static $_methodSuffix = "Test";
    private static $_designPrefix = "console";
    private static $_setUpName = "setUp";
    private static $_tearDownName = "tearDown";
    private static $_time = 0;
    private static $_assertionMethods = array();
    private static $_instanceCount = 0;
    private static $_iniFile = "src/PJsUnit.ini";
    /**
     * Unused constructor. The implementation of the test engine requires no 
     * additional objects of this class to be created.
     * 
     * @throws Exception if an instance of this class is already created.
     * @return void
     *                
     */
    function __construct()
    {
        if (PJsUnit::$_instanceCount == 0) {
            if (file_exists(dirname(__FILE__).'/'.PJsUnit::$_iniFile)) {
                PJsUnit::parseIniFile(dirname(__FILE__).'/'.PJsUnit::$_iniFile);
            }
        } else {
            throw new Exception(
                "Trying to create another instance of PJsUnit."
            );
        }
        PJsUnit::$_instanceCount++;
    }
    /**
     * Iterates over all defined classes and functions and adds the ones that has 
     * the correct name suffix to the test engine, making them classes and 
     * functions under test. It then calls the test method and finally echos the 
     * result from the toString method to standard out.
     * 
     * @return void
     *                
     */
    function __destruct()
    {
        PJsUnit::$_instanceCount--;
        if (PJsUnit::$_instanceCount == 0) {
            $functions = get_defined_functions();
            foreach ($functions['user'] as $function) {
                if (substr(
                    $function, 
                    - strlen(PJsUnit::$_functionSuffix)
                ) == PJsUnit::$_functionSuffix
                ) {
                    PJsUnit::addFunction($function);
                } 
            }
            foreach (get_declared_classes() as $class) {
                if (substr(
                    $class, 
                    - strlen(PJsUnit::$_classSuffix)
                ) == PJsUnit::$_classSuffix
                ) {
                    PJsUnit::addClass($class);
                } 
            }
            PJsUnit::_test();
            echo PJsUnit::_toString();
        }
    }
    /**
     * Returns a textual representation of the objects, classes, methods and 
     * functions under test. Depending on the design prefix different 
     * representation may occur. The files that are loaded and returned will be 
     * located in the "src/design/" folder. Files starting with the design 
     * prefix will be the files that are called.
     * 
     * @return string The object converted to a string.
     *                
     */
    public function __toString()
    {
        return PJsUnit::_toString();
    }
    /**
     * Returns a textual representation of the objects, classes, methods and 
     * functions under test. Depending on the design prefix different 
     * representation may occur. The files that are loaded and returned will be 
     * located in the "src/design/" folder. Files starting with the design 
     * prefix will be the files that are called.
     * 
     * @return string The object converted to a string.
     *                
     */
    private static function _toString()
    {
        $prefix = PJsUnit::designPrefix();
        $array['passed'] = PJsUnit::$_passed;
        $array['functions'] = array();
        foreach (PJsUnit::$_functions as $f) {
            $array['functions'][] = (string) $f;
        }
        $array['classes'] = array();
        foreach (PJsUnit::$_classes as $c) {
            $array['classes'][] = (string) $c;
        }
        $array['objects'] = array();
        foreach (PJsUnit::$_objects as $o) {
            $array['objects'][] = (string) $o;
        }
        $array['time'] = PJsUnit::$_time;
        $array['string'] = "";
        $array['passed_count'] = PJsUnit::$_passedCount;
        $array['failed_count'] = PJsUnit::$_failedCount;
        $array['tests'] = count(PJsUnit::$_functions) 
                          + count(PJsUnit::$_objects) 
                          + count(PJsUnit::$_classes);
        $dir = dirname(__FILE__);
        $path=$dir."/src/design/".$prefix.'_'.__CLASS__.".php";
        return PJsUnit\includeExtract($path, $array);
    }
    /**
     * Magic __set method. Calls the corresponding set method for the parameter 
     * name with the parameter value as input parameter for the corresponding 
     * method.
     * 
     * @param string $name  The name of the get method that is to be called.
     * @param mixed  $value The input for the method that is to be called.
     * 
     * @return mixed the return value of the get method that was called.
     */
    public function __set($name, $value)
    {
        if (method_exists(__CLASS__, $name)
            && property_exists(__CLASS__, "_$name")
        ) {
            $refl = new ReflectionMethod(__CLASS__, $name);
            if ($refl->isPublic()) {
                return PJsUnit::$name($value);
            } else {
                throw new Exception(
                    "Access to undeclared static property ".__CLASS__."::$name."
                );
            }
        } else {
            throw new Exception(
                "Access to undeclared static property ".__CLASS__."::$name."
            );
        }
    }
    /**
     * Magic __get method. Calls the corresponding get method for the parameter name.
     * 
     * @param string $name The name of the get method that is to be called.
     * 
     * @return mixed the return value of the get method that was called.
     */
    public function __get($name)
    {
        if (method_exists(__CLASS__, $name)
            && property_exists(__CLASS__, "_$name")
        ) {
            $refl = new ReflectionMethod(__CLASS__, $name);
            if ($refl->isPublic() && $refl->isStatic()) {
                return PJsUnit::$name();
            } else {
                throw new Exception(
                    "Access to undeclared static property ".__CLASS__."::$name."
                );
            }
        } else {
            throw new Exception(
                "Access to undeclared static property ".__CLASS__."::$name."
            );
        }
    }
    /**
     * Magic __callStatic method. Either adds an assertion function to the test 
     * engine or calls an existing assertion function from the test engine 
     * depending on the argument parameter.
     * 
     * @param string $name      The name of the assertion method that should be 
     *                          added to or called by the test engine.
     * 
     * @param Array  $arguments Depending on the array different functionality will 
     *                          be executed. If the array has one element 
     *                          containing an anonymous function it will be added 
     *                          to the test engine and reached by calling the 
     *                          method with the name of parameter name. If the 
     *                          array contains multiple elements the assertion 
     *                          method with method name of parameter name will be 
     *                          called with the arguments in this array.
     * 
     * @return boolean true if the assertion function was added successfullu or the 
     *                 assertion method that was called returns true for the 
     *                 parameter array as arguments, else false.
     */
    static function __callStatic($name, $arguments)
    {
        if (isset(PJsUnit::$_assertionMethods[$name])) {
            $reflection = new ReflectionFunction(PJsUnit::$_assertionMethods[$name]);
            if ($reflection->getNumberOfParameters() == count($arguments) -1) {
                $message = $arguments[count($arguments)-1];
                array_splice($arguments, count($arguments)-1, 1);
            }
            $bool = call_user_func_array(
                PJsUnit::$_assertionMethods[$name], 
                $arguments
            );
            if (is_bool($bool) && $bool) {
                PJsUnit::_assertionPassed();
            } elseif (isset($message)) {
                PJsUnit::assertion_failed($message);
            } else {
                PJsUnit::_assertionFailed();
            }
            return $bool;
        } else {
            throw new Exception(
                "The method ".__CLASS__."::$name()' is not defined."
            );
        }
    }
    /**
     * Adds an assertion function to the test engine. If the function was added 
     * sucessfully The method can be reached statically by calling: 
     * "PJsUnit::$name($arg1, $arg2, ..., $argn, $errorMessage);" The function that 
     * is added should return a boolean value. True indicates that the assertion 
     * passed while false indicates that the assertion failed. Errors will be added 
     * automatically if assertions fails.
     * 
     * @param string $name The name that the assertion function.
     * @param mixed  $fun  The assertion function. The return type of the 
     *                     function should be boolean.
     * 
     * @return boolean true if the function was successfully added to the test 
     *                 engine, else false. 
     */
    function addAssertion($name, $fun)
    {
        if (((is_string($fun) 
            && function_exists($fun)) 
            || (is_object($fun) 
            && ($fun instanceof Closure)))
        ) {
            if (isset(PJsUnit::$_assertionMethods[$name])) {
                return false;
            } else {
                PJsUnit::$_assertionMethods[$name] = $fun;
                return isset(PJsUnit::$_assertionMethods[$name])
                       && PJsUnit::$_assertionMethods[$name] == $fun;
            }
        }
    }
    /**
     * Potentially changes the suffix string that is required of function names to 
     * be added automatically to the test engine; making them functions under test.
     * 
     * @param string $suffix The new suffix of that function names must end with 
     *                       for them to be added and tested automatically by the 
     *                       test engine. If the parameter value is null the 
     *                       function name will not change.
     * 
     * @return string The suffix for functions that will be added automatically to 
     *                the test engine.
     */
    static function functionSuffix($suffix = null)
    {
        if ($suffix != null) {
            if (is_string($suffix)) {
                PJsUnit::$_functionSuffix = $suffix;
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a string as argument, ".gettype($suffix)." was given."
                );
            }
        }
        return PJsUnit::$_functionSuffix;
    }    
    /**
     * Potentially changes the suffix string that is required of class names to be 
     * added automatically to the test engine; making them classes under test.
     * 
     * @param string $suffix The new suffix of that class names must end with for 
     *                       them to be added and tested automatically by the test 
     *                       engine. If the parameter value is null the class name 
     *                       will not change.
     * 
     * @return string The suffix for classes that will be added automatically to 
     *                the test engine.
     */
    static function classSuffix($suffix = null)
    {
        if ($suffix != null) {
            if (is_string($suffix)) {
                PJsUnit::$_classSuffix = $suffix;
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a string as argument, ".gettype($suffix)." was given."
                );
            }
        }
        return PJsUnit::$_classSuffix;
    }
    /**
     * Potentially changes the suffix string that is required of method names to be 
     * added automatically to the test engine for objects and classes under test; 
     * making them methods under test.
     * 
     * @param string $suffix The new suffix of that method names for objects and 
     *                       classes under test must end with for them to be added 
     *                       and tested automatically by the test engine. If the 
     *                       parameter value is null the method name will not 
     *                       change.
     * 
     * @return string The suffix for methods that will be added automatically to 
     *                the test engine.
     */
    static function methodSuffix($suffix = null)
    {
        if ($suffix != null) {
            if (is_string($suffix)) {
                PJsUnit::$_methodSuffix = $suffix;
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a string as argument, ".gettype($suffix)." was given."
                );
            }
        }
        return PJsUnit::$_methodSuffix;
    }
    /**
     * Potentially changes the value of the prefix of the files that contain the 
     * design code for displaying objects, classes, methods and function under test.
     * 
     * @param string $prefix The prefix of the php files in the src/design 
     *                       folder should have to be called by the objects 
     *                       containing classes, objects, methods and functions 
     *                       under test.
     * 
     * @return string The design prefix.
     */
    static function designPrefix($prefix = null)
    {
        if ($prefix != null) {
            if (gettype($prefix) == "string") {
                PJsUnit::$_designPrefix = $prefix;
            } else {
                throw new Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a string as argument, ".gettype($prefix)." was given."
                );
            }
        }
        return PJsUnit::$_designPrefix;
    }
    /**
     * Potentially changes the method name that should be called automatically by 
     * the objects containing classes and objects under test before calling all the 
     * methods of the object or class under test that are to be tested.
     * 
     * @param string $name The new name of the build up methods that are to be 
     *                     called automatically by the objects containing classes 
     *                     and objects under test. If the parameter value is null 
     *                     the method name will not change.
     * 
     * @return string The build up method name.
     */
    static function setUpName($name = null)
    {
        if ($name != null) {
            if (gettype($name) == "string") {
                PJsUnit::$_setUpName = $name;
            } else {
                throw new Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a string as argument, ".gettype($name)." was given."
                );
            }
        }
        return PJsUnit::$_setUpName;    
    }
    /**
     * Potentially changes the method name that should be called automatically by 
     * the objects containing classes and objects under test after calling all the 
     * methods of the object or class under test that are to be tested.
     * 
     * @param string $name The new name of the tear down method that are to be 
     *                     called automatically by the objects containing objects  
     *                     and classes under test. If the parameter value is null 
     *                     the method name will not change.
     * 
     * @return string The tear down method name.
     */
    static function tearDownName($name = null)
    {
        if ($name != null) {
            if (gettype($name) == "string") {
                PJsUnit::$_tearDownName = $name;
            } else {
                throw new Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a string as argument, ".gettype($name)." was given."
                );
            }
        }
        return PJsUnit::$_tearDownName;    
    }
    /**
     * Opens and parses the file at the location of the value of the parameter. 
     * Potentially changes the value to the static proporties class_suffix, 
     * method_suffix, function_suffix, design_prefix, build_up_name and 
     * tear_down_name.
     * 
     * @param string $file The location of the ini file.
     * 
     * @return boolean true if all unit tests passed, else false.
     */
    static function parseIniFile($file)
    {
        if (file_exists($file)) {
            $sections = parse_ini_file($file, true);
            $class_str = "class";
            $method_str = "method";
            $function_str = "function";
            foreach ($sections as $sec => $tuple) {
                $sec = strtolower($sec);
                $generic = strtolower($sec) == "pjsunit";
                foreach ($tuple as $method => $value) {
                    switch(strtolower($method)) {
                    case "suffix":
                        if ($generic || $sec == $class_str) {
                            PJsUnit::classSuffix($value);
                        }
                        if ($generic || $sec == $method_str) {
                            PJsUnit::methodSuffix($value);
                        }
                        if ($generic || $sec == $function_str) {
                            PJsUnit::functionSuffix($value);
                        }
                        break;
                    case "prefix":
                        if ($generic) {
                            PJsUnit::designPrefix($value);
                        }
                        break;
                    case "build_up_name":
                        if ($sec != $function_str) {
                            PJsUnit::build_up_name($value);
                        }
                        break;
                    case "tear_down_name":
                        if ($sec != $function_str) {
                            PJsUnit::tearDownName($value);
                        }
                        break;
                    }
                }
            }
        } else {
            throw Exception("File \"$file\" not fount.");
        }
    }
    /**
     * Iterates over all objects, classes and functions under test and calls the 
     * test method for every element. The static proporties _time, _passed_count, 
     * _failed_count and _passed will be updated with the time it took to run all 
     * the unit tests, the number of objects, classes and function under test that 
     * passed, the number of objects, classes and function under test that failed 
     * and if all unit tests passed respectively.
     * 
     * @return boolean true if all unit tests passed, else false.
     */
    private static function _test()
    {
        foreach (PJsUnit::$_classes as $class) {
            PJsUnit::$_currentObject = $class;
            if ($class->test()) {
                PJsUnit::$_passedCount++;
            } else {
                PJsUnit::$_failedCount++;
                PJsUnit::$_passed = false;
            }
            PJsUnit::$_time += $class->time;
        }
        foreach (PJsUnit::$_objects as $object) {
            PJsUnit::$_currentObject = $object;
            if ($object->test()) {
                PJsUnit::$_passedCount++;
            } else {
                PJsUnit::$_failedCount++;
                PJsUnit::$_passed = false;
            }
            PJsUnit::$_time += $object->time;
        }
        foreach (PJsUnit::$_functions as $function) {
            PJsUnit::$_currentFunction = $function;
            if ($function->test()) {
                PJsUnit::$_passedCount++;
            } else {
                PJsUnit::$_failedCount++;
                PJsUnit::$_passed = false;
            }
            PJsUnit::$_time += $function->time;
        }
        PJsUnit::$_currentObject = null;
        PJsUnit::$_currentFunction = null;
        return PJsUnit::$_passed;
    }
    /**
     * Adds a function that is to become a function under test which will be tested 
     * by the test engine.
     * 
     * @param string  $name    The function name of the function that is to become 
     *                         the function under test and tested by the test 
     *                         engine.
     * 
     * @param boolean $runTest true if the function under test should be executed  
     *                         by the test engine, else false.
     * 
     * @return PJsUnit\TestFunction The object containing the function under test.
     */
    private static function _addFunction($name, $runTest = true)
    {
        $name = strtolower($name);
        if (function_exists($name)) {
            foreach (PJsUnit::$_functions as $function) {
                if ($function->name == $name) {
                    return $function;
                }
            }
            $reflection = new \ReflectionFunction($name);
            if ($reflection->getNumberOfRequiredParameters() == 0) {
                $function = new PJsUnit\TestFunction($name, $runTest);
                PJsUnit::$_functions[] = $function;
                return $function;    
            }
        }
        return false;
    }
    /**
     * Adds a function that is to become a function under test which will be tested 
     * by the test engine.
     * 
     * @param string $name The function name of the function that is to become 
     *                     the function under test and tested by the test 
     *                     engine.
     * 
     * @return boolean          true if the function was added succesfully, else 
     *                          false.
     */
    static function addFunction($name)
    {
        return PJsUnit::_addFunction($name) !== false;
    }
    /**
     * Adds a class that is to become a class under test which will be tested by 
     * the test engine.
     * 
     * @param string  $class_name The class name of the class that is to become the 
     *                            class under test and tested by the test engine.
     * 
     * @param boolean $runTest    true if the methods that are to be tested by the 
     *                            class under test should be executed, else false.
     * 
     * @return PJsUnit\TestClass The object containing the class under test.
     */
    private static function _addClass($class_name, $runTest = true)
    {
        if (class_exists($class_name)) {
            foreach (PJsUnit::$_classes as $class) {
                if ($class->name == $class_name) {
                    return $class;
                }
            }
            $class = new PJsUnit\TestClass($class_name, $runTest);
            PJsUnit::$_classes[] = $class;
            return $class;
        }
        return null;
    }
    /**
     * Adds a class that is to become a class under test which will be tested by 
     * the test engine.
     * 
     * @param string $class_name The class name of the class that is to become the 
     *                           class object under test and tested by the test 
     *                           engine.
     * 
     * @return boolean           true if the class was added succesfully, else false.
     */
    static function addClass($class_name)
    {
        return PJsUnit::_addClass($class_name) !== null;
    }
    /**
     * Adds an object that is to become an object under test which will be tested 
     * by the test engine.
     * 
     * @param Object  $object  The object that is to become the object under test 
     *                         and tested by the test engine.
     * 
     * @param boolean $runTest true if the methods that are to be tested by the 
     *                         object under test should be executed, else false.
     * 
     * @return PJsUnit\TestObject The object that contains the object under test.
     */
    static function _addObject($object, $runTest = true)
    {
        if (is_object($object)) {
            foreach (PJsUnit::$_objects as $obj) {
                if ($obj->objEquals($object)) {
                    return $obj;
                }
            }
            $test_object = new PJsUnit\TestObject($object);
            PJsUnit::$_objects[] = $test_object;
            return $test_object;
        } else {
            return false;
        }
    }
    /**
     * Adds an object that is to become an object under test which will be tested 
     * by the test engine.
     * 
     * @param Object $object The object that is to become the object under test and 
     *                       tested by the test engine.
     * 
     * @return boolean       true if the object was added succesfully, else false.
     */
    static function addObject($object)
    {
        return PJsUnit::_addObject($object) !== false;
    }
    /**
     * Adds an error to the corresponding class, object or function under test 
     * where the error occured. If the class or function under test doesn't exist 
     * it will be created by the test engine.
     * 
     * @param PJsUnit\Error $error The error that is to be added to the 
     *                             corresponding object, class or function under 
     *                             test.
     * 
     * @return boolean             true if the error was succesfully added, else 
     *                             false.
     */
    private static function _currentAddError($error)
    {
        if (PJsUnit::$_currentObject != null 
            && $error->class == PJsUnit::$_currentObject->class
        ) {
            return PJsUnit::$_currentObject->addError($error, true);
        } elseif (PJsUnit::$_currentClass != null
            && $error->class == PJsUnit::$_currentClass->class)
        {
            return PJsUnit::$_currentClass->addError($error, true);
        } elseif (PJsUnit::$_currentFunction != null) {
            $name = PJsUnit::$_currentFunction->name;
            if ($name{0} == '\\') { 
                $name = substr($name, 1);
            }
            if ($error->caller == $name) {
                return PJsUnit::$_currentFunction->addError($error, true);
            }
        }
        if ($error->class != null) {
            $test_instance = PJsUnit::_addClass($error->class, false);
            $test_instance->addMethod($error->caller, false);
        } else {
            $caller = $error->caller;
            $test_instance = PJsUnit::_addFunction($caller);
            $test_instance->runTest = false;
        }
        return $test_instance->addError($error, true);
    }
    
    /**
     * Passes the current assertion for the object, class or function under test. 
     * If the class or function under test doesn't exist it will be created by the 
     * test engine.
     * 
     * @return void
     */
    static private function _assertionPassed()
    {
        $i = 2;
        $debug_backtrace = debug_backtrace();
        if (isset($debug_backtrace[$i+1])) {
            $caller = $debug_backtrace[$i+1];
            if (isset($caller["class"])) {
                if ((PJsUnit::$_currentObject != null 
                    && $caller["class"] != PJsUnit::$_currentObject->class)
                    || (PJsUnit::$_currentClass != null 
                    && $caller["class"] != PJsUnit::$_currentClass->class))
                {
                    $class = PJsUnit::_addClass($caller['class'], false);
                    $class->addMethod($caller['function'], false);
                }
            } else {
                if (PJsUnit::$_currentFunction != null
                    || $caller["function"] != PJsUnit::$_currentFunction->name)
                {
                    $function = PJsUnit::_addFunction($caller['function']);
                    $function->runTest = false;
                }
            }
        }
    }
    
    /**
     * Creates and adds an error to the object, class or function under test where  
     * the error was encountered. If the class or function under test doesn't exist 
     * it will be created by the test engine.
     * 
     * @return void
     */
    static private function _assertionFailed()
    {
        $i=2;
        $debug_backtrace = debug_backtrace();
        $error=$debug_backtrace[$i];
        if (isset($debug_backtrace[$i+1])) {
            $caller = $debug_backtrace[$i+1];
            $error['caller']=$caller['function'];
            if (isset($caller['type'])) {
                $error['type']=$caller['type'];
            } else {
                unset($error['type']);
            }
            if (isset($caller['class'])) {
                $error['class']=$caller['class'];
            } else {
                unset($error['class']);
            }
        } else {
            $caller = null;
            if (isset($error['class'])) {
                unset($error['class']);
            }
            $error['caller'] = null;
        }
        $error['passed']=false;
        $error = new PJsUnit\Error($error);
        PJsUnit::_currentAddError($error);
    }
}
if (file_exists(dirname(__FILE__)."/src/TestInstance.php")) {
    include dirname(__FILE__)."/src/TestInstance.php";
} elseif (file_exists(dirname(__FILE__)."\src\TestInstance.php")) {
    include dirname(__FILE__)."\src\TestInstance.php";
}
if (file_exists(dirname(__FILE__)."/src/assertion_methods.php")) {
    include dirname(__FILE__)."/src/assertion_methods.php";
} elseif (file_exists(dirname(__FILE__)."\src\assertion_methods.php")) {
    include dirname(__FILE__)."\src\assertion_methods.php";
}
$PJsUnit = new PJsUnit();
?>