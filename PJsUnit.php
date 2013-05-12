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
    private static $_designPrefix = "console";
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
        if (self::$_instanceCount == 0) {
            if (file_exists(dirname(__FILE__).'/'.self::$_iniFile)) {
                self::parseIniFile(dirname(__FILE__).'/'.self::$_iniFile);
            }
        } else {
            throw new Exception(
                "Trying to create another instance of PJsUnit."
            );
        }
        self::$_instanceCount++;
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
        self::$_instanceCount--;
        if (self::$_instanceCount == 0) {
            $functions = get_defined_functions();
            $needle = PJsUnit\TestFunction::suffix();
            if ($needle == false) {
                $needle = PJsUnit\TestFunction::prefix();
                $suffix = false;
            } else {
                $suffix = true;
            }
            foreach ($functions['user'] as $function) {
                if ($suffix 
                    && substr($function, -strlen($needle)) == $needle
                ) {
                    self::addFunction($function);
                } elseif (!$suffix
                    && substr($function, 0, strlen($needle)) == $needle
                ) {
                    self::addFunction($function);
                }
            }
            $needle = PJsUnit\TestClass::suffix();
            if ($needle == false) {
                $needle = PJsUnit\TestClass::prefix();
                $suffix = false;
            } else {
                $suffix = true;
            }
            var_dump($suffix);
            foreach (get_declared_classes() as $class) {
                if ($suffix
                    && substr($class, -strlen($needle)) == $needle
                ) {
                    self::addClass($class);
                } elseif (!$suffix
                    && substr($class, 0, strlen($needle)) == $needle
                ) {
                    self::addClass($class);
                } 
            }
            self::_test();
            echo self::_toString();
        }
    }
    /**
     * Returns a textual representation of the objects, classes, methods and 
     * functions under test. Depending on the design prefix different 
     * representation may occur. The files that are loaded and returned will be 
     * located in the "src/design/" folder. Files starting with the design 
     * prefix will be the files that are called.
     * 
     * @return String The object converted to a String.
     *                
     */
    public function __toString()
    {
        return self::_toString();
    }
    /**
     * Returns a textual representation of the objects, classes, methods and 
     * functions under test. Depending on the design prefix different 
     * representation may occur. The files that are loaded and returned will be 
     * located in the "src/design/" folder. Files starting with the design 
     * prefix will be the files that are called.
     * 
     * @return String The object converted to a String.
     *                
     */
    private static function _toString()
    {
        $prefix = self::designPrefix();
        $array['passed'] = self::$_passed;
        $array['functions'] = array();
        foreach (self::$_functions as $f) {
            $array['functions'][] = (String) $f;
        }
        $array['classes'] = array();
        foreach (self::$_classes as $c) {
            $array['classes'][] = (String) $c;
        }
        $array['objects'] = array();
        foreach (self::$_objects as $o) {
            $array['objects'][] = (String) $o;
        }
        $array['time'] = self::$_time;
        $array['String'] = "";
        $array['passed_count'] = self::$_passedCount;
        $array['failed_count'] = self::$_failedCount;
        $array['tests'] = count(self::$_functions) 
                          + count(self::$_objects) 
                          + count(self::$_classes);
        $dir = dirname(__FILE__);
        $path=$dir."/src/design/".$prefix.'_'.__CLASS__.".php";
        return PJsUnit\includeExtract($path, $array);
    }
    /**
     * Magic __set method. Calls the corresponding set method for the parameter 
     * name with the parameter value as input parameter for the corresponding 
     * method.
     * 
     * @param String $name  The name of the get method that is to be called.
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
                return self::$name($value);
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
     * @param String $name The name of the get method that is to be called.
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
                return self::$name();
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
     * @param String $name      The name of the assertion method that should be 
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
        if (isset(self::$_assertionMethods[$name])) {
            $reflection = new ReflectionFunction(self::$_assertionMethods[$name]);
            if ($reflection->getNumberOfParameters() == count($arguments) -1) {
                $message = $arguments[count($arguments)-1];
                array_splice($arguments, count($arguments)-1, 1);
            }
            $bool = call_user_func_array(
                self::$_assertionMethods[$name], 
                $arguments
            );
            if (is_bool($bool) && $bool) {
                self::_assertionPassed();
            } elseif (isset($message)) {
                self::assertion_failed($message);
            } else {
                self::_assertionFailed();
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
     * "self::$name($arg1, $arg2, ..., $argn, $errorMessage);" The function that 
     * is added should return a boolean value. True indicates that the assertion 
     * passed while false indicates that the assertion failed. Errors will be added 
     * automatically if assertions fails.
     * 
     * @param String $name The name that the assertion function.
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
            if (isset(self::$_assertionMethods[$name])) {
                return false;
            } else {
                self::$_assertionMethods[$name] = $fun;
                return isset(self::$_assertionMethods[$name])
                       && self::$_assertionMethods[$name] == $fun;
            }
        }
    }
    /**
     * Potentially changes the prefix String that is required of function names to 
     * be added automatically to the test engine; making them functions under test.
     * 
     * @param String $prefix The new prefix of that function names must start with 
     *                       for them to be added and tested automatically by the 
     *                       test engine. If the parameter value is null the prefix
     *                       will not change.
     * 
     * @return String The prefix for functions that will be added automatically to 
     *                the test engine.
     */
    static function functionPrefix($prefix = null)
    {
        if ($prefix != null) {
            if (is_string($prefix)) {
                PJsUnit\TestFunction::prefix($prefix);
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($prefix)." was given."
                );
            }
        }
        return PJsUnit\TestFunction::prefix();
    }
    /**
     * Potentially changes the suffix String that is required of function names to 
     * be added automatically to the test engine; making them functions under test.
     * 
     * @param String $suffix The new suffix of that function names must end with 
     *                       for them to be added and tested automatically by the 
     *                       test engine. If the parameter value is null the 
     *                       function name will not change.
     * 
     * @return String The suffix for functions that will be added automatically to 
     *                the test engine.
     */
    static function functionSuffix($suffix = null)
    {
        if ($suffix != null) {
            if (is_string($suffix)) {
                PJsUnit\TestFunction::Suffix($suffix);
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($suffix)." was given."
                );
            }
        }
        return PJsUnit\TestFunction::Suffix($suffix);;
    }
    /**
     * Potentially changes the prefix String that is required of class names to be 
     * added automatically to the test engine; making them classes under test.
     * 
     * @param String $prefix The new prefix of that class names start end with for 
     *                       them to be added and tested automatically by the test 
     *                       engine. If the parameter value is null the sufix will 
     *                       not change.
     * 
     * @return String The prefix for classes that will be added automatically to 
     *                the test engine.
     */
    static function classPrefix($prefix = null)
    {
        if ($prefix != null) {
            if (is_string($prefix)) {
                PJsUnit\TestClass::prefix($prefix);
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($prefix)." was given."
                );
            }
        }
        return PJsUnit\TestClass::suffix($prefix);
    }
    /**
     * Potentially changes the suffix String that is required of class names to be 
     * added automatically to the test engine; making them classes under test.
     * 
     * @param String $suffix The new suffix of that class names must end with for 
     *                       them to be added and tested automatically by the test 
     *                       engine. If the parameter value is null the class name 
     *                       will not change.
     * 
     * @return String The suffix for classes that will be added automatically to 
     *                the test engine.
     */
    static function classSuffix($suffix = null)
    {
        if ($suffix != null) {
            if (is_string($suffix)) {
                PJsUnit\TestClass::suffix($suffix);
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($suffix)." was given."
                );
            }
        }
        return PJsUnit\TestClass::suffix($suffix);
    }
    /**
     * Potentially changes the prefix String that is required of method names to be 
     * added automatically to the test engine for objects and classes under test; 
     * making them methods under test.
     * 
     * @param String $prefix The new prefix of that method names for objects and 
     *                       classes under test must end with for them to be added 
     *                       and tested automatically by the test engine. If the 
     *                       parameter value is null the method prefix will not
     *                       change.
     * 
     * @return String The prefix for methods that will be added automatically to 
     *                the test engine.
     */
    static function methodPrefix($prefix = null)
    {
        if ($prefix != null) {
            if (is_string($prefix)) {
                PJsUnit\TestClass::prefix($prefix);
                PJsUnit\TestObject::prefix($prefix);
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($prefix)." was given."
                );
            }
        }
        return PJsUnit\TestClass::prefix();
    }
    /**
     * Potentially changes the suffix String that is required of method names to be 
     * added automatically to the test engine for objects and classes under test; 
     * making them methods under test.
     * 
     * @param String $suffix The new suffix of that method names for objects and 
     *                       classes under test must end with for them to be added 
     *                       and tested automatically by the test engine. If the 
     *                       parameter value is null the method name will not 
     *                       change.
     * 
     * @return String The suffix for methods that will be added automatically to 
     *                the test engine.
     */
    static function methodSuffix($suffix = null)
    {
        if ($suffix != null) {
            if (is_string($suffix)) {
                PJsUnit\TestClass::suffix($suffix);
                PJsUnit\TestObject::suffix($suffix);
            } else {
                throw new \Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($suffix)." was given."
                );
            }
        }
        return PJsUnit\TestClass::suffix();
    }
    /**
     * Potentially changes the value of the prefix of the files that contain the 
     * design code for displaying objects, classes, methods and function under test.
     * 
     * @param String $prefix The prefix of the php files in the src/design 
     *                       folder should have to be called by the objects 
     *                       containing classes, objects, methods and functions 
     *                       under test.
     * 
     * @return String The design prefix.
     */
    static function designPrefix($prefix = null)
    {
        if ($prefix != null) {
            if (gettype($prefix) == "String") {
                self::$_designPrefix = $prefix;
            } else {
                throw new Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($prefix)." was given."
                );
            }
        }
        return self::$_designPrefix;
    }
    /**
     * Potentially changes the method name that should be called automatically by 
     * the objects containing classes and objects under test before calling all the 
     * methods of the object or class under test that are to be tested.
     * 
     * @param String $name The new name of the set-up methods that are to be 
     *                     called automatically by the objects containing classes 
     *                     and objects under test. If the parameter value is null 
     *                     the method name will not change.
     * 
     * @return String The set-up method name.
     */
    static function setUpName($name = null)
    {
        if ($name != null) {
            if (is_string($name)) {
                PJsUnit\TestClass::setUpName($name);
                PJsUnit\TestObject::setUpName($name);
            } else {
                throw new Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($name)." was given."
                );
            }
        }
        return PJsUnit\TestClass::setUpName($name);
    }
    /**
     * Potentially changes the method name that should be called automatically by 
     * the objects containing classes and objects under test after calling all the 
     * methods of the object or class under test that are to be tested.
     * 
     * @param String $name The new name of the tear down method that are to be 
     *                     called automatically by the objects containing objects  
     *                     and classes under test. If the parameter value is null 
     *                     the method name will not change.
     * 
     * @return String The tear down method name.
     */
    static function tearDownName($name = null)
    {
        if ($name != null) {
            if (is_string($name)) {
                PJsUnit\TestClass::TearDownName($name);
                PJsUnit\TestObject::TearDownName($name);
            } else {
                throw new Exception(
                    __CLASS__."::".__METHOD__.
                    " takes a String as argument, ".gettype($name)." was given."
                );
            }
        }
        return PJsUnit\TestClass::TearDownName($name);
    }
    /**
     * Opens and parses the file at the location of the value of the parameter. 
     * Potentially changes the value to the prefixes and suffixes of functions 
     * classes and methods and the set-up and tear-down method names of classes
     * and objects
     * 
     * @param String $file The location of the ini file.
     * 
     * @return boolean true if all unit tests passed, else false.
     */
    static function parseIniFile($file)
    {
        if (file_exists($file)) {
            $sections = parse_ini_file($file, true);
            $classStr = "class";
            $methodStr = "method";
            $functionStr = "function";
            foreach ($sections as $sec => $tuple) {
                $sec = strtolower($sec);
                $generic = $sec == "pjsunit";
                foreach ($tuple as $method => $value) {
                    switch(strtolower($method)) {
                    case "prefix":
                        if ($generic || $sec == $classStr) {
                            self::classPrefix($value);
                        }
                        if ($generic || $sec == $methodStr) {
                            self::methodPrefix($value);
                        }
                        if ($generic || $sec == $functionStr) {
                            self::functionPrefix($value);
                        }
                        break;
                    case "suffix":
                        if ($generic || $sec == $classStr) {
                            self::classSuffix($value);
                        }
                        if ($generic || $sec == $methodStr) {
                            self::methodSuffix($value);
                        }
                        if ($generic || $sec == $functionStr) {
                            self::functionSuffix($value);
                        }
                        break;
                    case "setup":
                    case "setUpName":
                        if ($sec != $functionStr) {
                            self::setUpName($value);
                        }
                        break;
                    case "teardown":
                    case "tearDownName":
                        if ($sec != $functionStr) {
                            self::tearDownName($value);
                        }
                        break;
                    }
                }
            }
        } else {
            throw Exception("INI File \"$file\" not fount.");
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
        foreach (self::$_classes as $class) {
            self::$_currentObject = $class;
            if ($class->test()) {
                self::$_passedCount++;
            } else {
                self::$_failedCount++;
                self::$_passed = false;
            }
            self::$_time += $class->time;
        }
        foreach (self::$_objects as $object) {
            self::$_currentObject = $object;
            if ($object->test()) {
                self::$_passedCount++;
            } else {
                self::$_failedCount++;
                self::$_passed = false;
            }
            self::$_time += $object->time;
        }
        foreach (self::$_functions as $function) {
            self::$_currentFunction = $function;
            if ($function->test()) {
                self::$_passedCount++;
            } else {
                self::$_failedCount++;
                self::$_passed = false;
            }
            self::$_time += $function->time;
        }
        self::$_currentObject = null;
        self::$_currentFunction = null;
        return self::$_passed;
    }
    /**
     * Adds a function that is to become a function under test which will be tested 
     * by the test engine.
     * 
     * @param String  $name    The function name of the function that is to become 
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
            foreach (self::$_functions as $function) {
                if ($function->name == $name) {
                    return $function;
                }
            }
            $reflection = new \ReflectionFunction($name);
            if ($reflection->getNumberOfRequiredParameters() == 0) {
                $function = new PJsUnit\TestFunction($name, $runTest);
                self::$_functions[] = $function;
                return $function;    
            }
        }
        return false;
    }
    /**
     * Adds a function that is to become a function under test which will be tested 
     * by the test engine.
     * 
     * @param String $name The function name of the function that is to become 
     *                     the function under test and tested by the test 
     *                     engine.
     * 
     * @return boolean          true if the function was added succesfully, else 
     *                          false.
     */
    static function addFunction($name)
    {
        return self::_addFunction($name) !== false;
    }
    /**
     * Adds a class that is to become a class under test which will be tested by 
     * the test engine.
     * 
     * @param String  $class_name The class name of the class that is to become the 
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
            foreach (self::$_classes as $class) {
                if ($class->name == $class_name) {
                    return $class;
                }
            }
            $class = new PJsUnit\TestClass($class_name, $runTest);
            self::$_classes[] = $class;
            return $class;
        }
        return null;
    }
    /**
     * Adds a class that is to become a class under test which will be tested by 
     * the test engine.
     * 
     * @param String $class_name The class name of the class that is to become the 
     *                           class object under test and tested by the test 
     *                           engine.
     * 
     * @return boolean           true if the class was added succesfully, else false.
     */
    static function addClass($class_name)
    {
        return self::_addClass($class_name) !== null;
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
            foreach (self::$_objects as $obj) {
                if ($obj->objEquals($object)) {
                    return $obj;
                }
            }
            $test_object = new PJsUnit\TestObject($object);
            self::$_objects[] = $test_object;
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
        return self::_addObject($object) !== false;
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
        if (self::$_currentObject != null 
            && $error->class == self::$_currentObject->class
        ) {
            return self::$_currentObject->addError($error, true);
        } elseif (self::$_currentClass != null
            && $error->class == self::$_currentClass->class)
        {
            return self::$_currentClass->addError($error, true);
        } elseif (self::$_currentFunction != null) {
            $name = self::$_currentFunction->name;
            if ($name{0} == '\\') { 
                $name = substr($name, 1);
            }
            if (strtolower($error->caller) == strtolower($name)) {
                self::$_currentFunction->name = $error->caller;
                return self::$_currentFunction->addError($error, true);
            }
        }
        if ($error->class != null) {
            $test_instance = self::_addClass($error->class, false);
            $test_instance->addMethod($error->caller, false);
        } else {
            $caller = $error->caller;
            $test_instance = self::_addFunction($caller);
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
                if ((self::$_currentObject != null 
                    && $caller["class"] != self::$_currentObject->class)
                    || (self::$_currentClass != null 
                    && $caller["class"] != self::$_currentClass->class))
                {
                    $class = self::_addClass($caller['class'], false);
                    $class->addMethod($caller['function'], false);
                }
            } elseif (self::$_currentFunction != null
                && strtolower($caller["function"]) == strtolower(self::$_currentFunction->name))
            {
                var_dump($caller["function"]);
                self::$_currentFunction->name = $caller["function"];
            } else {
                $function = self::_addFunction($caller['function']);
                $function->runTest = false;
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
        self::_currentAddError($error);
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