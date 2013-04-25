/**
 * This file contains a unit testing interface for JavaScript.
 * Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 * Author: Frej Knutar <frej.knutar@gmail.com>
 * Site https://github.com/FrejKnutar/PJsUnit
 */
if (typeof PJsUnit === 'undefined') {
    var PJsUnit = (function () {
        var _failedCount = 0,
            _passedCount = 0,
            _passed = true,
            _functions = [],
            _objects = [],
            _currentFunction = null,
            _currentObject = null,
            _functionSuffix = 'Test',
            _objectSuffix = 'Test',
            _methodSuffix = 'Test',
            _setUpName = 'setUp',
            _tearDownName = 'tearDown',
            _time = 0,
            _writeFun = (function () {
                try {
                    return console.log;
                } catch (e) {
                    return print;
                }
            }()),
            _writeFunObj = null;
            /*
             * If possible, returns the class name of the object. If there isn't 
             * one "[Anonymous]" is returned.
             * 
             * @return String name of the class. 
             */
            getClass = function () {
                var funcNameRegex = /function (.{1,})\(/,
                    results = (funcNameRegex).exec(this.constructor.toString());
                return (results && results.length > 1) ? results[1] : '[Anonymous]';
            },
            /*
             * If possible, returns the name of the function if there is one. If 
             * there isn't one "[Anonymous]" is returned.
             * 
             * @return String name of the function
             */
            getFunctionName = function () {
                var funcNameRegex = /function (.{1,})\(/,
                    results = (funcNameRegex).exec(this.toString());
                return (results && results.length > 1) ? results[1] : '[Anonymous]';
            },
            /*
             * Exception that is to be thrown if the expected parameter type isn't 
             * the expected type.
             * 
             * @param String expectedType The expected type of the parameter.
             * @param String ReceivedType The received type of the parameter.
             */
            IllegalParameterException = function (expectedType, receivedType) {
                var name = 'IllegalParameterException',
                    message =
                        'Bad parameter given; expected parameter with type "' +
                        expectedType + '" but received "' + receivedType + '".',
                    toString = function () {
                        return '[' + name + '] ' + message;
                    };
                return {
                    name: name,
                    level: 'Show Stopper',
                    message: message,
                    toString: toString
                };
            },
            /*
             * Exception that is to be thrown if the value of the parameter is null.
             */
            NullPointerException = function () {
                var name = 'NullPointerException',
                    message =
                        'Null pointer parameter given; ' +
                        'received parameter with value "null".',
                    toString = function () {
                        return '[' + name + '] ' + message;
                    };
                return {
                    name: name,
                    level: 'Show Stopper',
                    message: message,
                    toString: toString
                };
            },
            /*
             * Exception that is to be thrown if the parameter isn't defined.
             *
             * @param String parameterName  The name of the parameter.
             * @param int    parameterIndex The parameter position or index.
             */
            UndefinedParameterException = function (parameterName, parameterIndex) {
                var name = 'UndefinedParameterException',
                    message =
                        'Parameter missing; expected parameter "' +
                        name + '" as parameter number ' +
                        parameterIndex + '.',
                    toString = function () {
                        return '[' + name + '] ' + message;
                    };
                return {
                    name: name,
                    level: 'Show Stopper',
                    message: message,
                    toString: toString
                };
            },
            /*
             * Function that is called when representing an error as a String. 
             */
            _errorToString = function () {
                var argarray = [null],
                    argstr = '(',
                    i;
                if (this.args.length > 0) {
                    for (i = 0; i < this.args.length; i++) {
                        switch (typeof this.args[i]) {
                        case 'object':
                            argstr += '{' + typeof this.args[i] + '}';
                            break;
                        case 'array':
                            argstr +=
                                '[' + typeof this.args[i] +
                                '(length: ' + this.args[i].length + ')]';
                            break;
                        case 'string':
                            argstr += '"' + this.args[i] + '"';
                            break;
                        default:
                            argstr += this.args[i];
                        }
                        if (i < (this.args.length - 1)) {
                            argstr += ', ';
                        }
                    }
                }
                argstr += ')';
                if (typeof this.caller === 'undefined') {
                    argarray[0] =
                        'The assertion "' +
                        this.assertion() + argstr +
                        '" failed';
                } else {
                    argarray[0] =
                        '"' + this.caller +
                        '" failed the assertion "' +
                        this.assertion() + argstr + '".';
                }
                return argarray[0];
            },
            /*
             * Class that represents a TestError. A TestError occurs when an 
             * assertion fails to pass and is added to the method under test 
             * or function under test where the failed assertion occured. 
             */
            TestError = function (assertionName, calleeName, parameterObject) {
                if (typeof assertionName === 'undefined') {
                    throw new UndefinedParameterException(
                        'assertionName',
                        1
                    );
                }
                if (typeof assertionName !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof assertionName
                    );
                }
                if (assertionName === null) {
                    throw new NullPointerException();
                }
                if (typeof calleeName === 'undefined') {
                    throw new UndefinedParameterException('calleeName', 2);
                }
                if (typeof calleeName !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof calleeName
                    );
                }
                if (calleeName === null) {
                    throw new NullPointerException();
                }
                if (typeof parameterObject !== 'undefined' && typeof parameterObject !== 'object') {
                    throw new IllegalParameterException(
                        'object',
                        typeof parameterObject
                    );
                }
                var _assertion = assertionName,
                    _callee = calleeName,
                    _args = parameterObject,
                    /*
                     * Returns the name of the failed assertion.
                     */
                    assertion = function () {
                        return _assertion;
                    },
                    /*
                     * Returns the name of the function or method where the error 
                     * occured.
                     */
                    caller = function () {
                        return _callee;
                    },
                    /*
                     * Returns the argument object of the of the assertion function 
                     * where the error occured.
                     */
                    args = function () {
                        return _args;
                    },
                    /*
                     * Converts and returns the object as a String.
                     */
                    toString = function () {
                        return _errorToString.apply({
                            assertion: assertion,
                            caller: caller(),
                            args: args()
                        });
                    };
                return {
                    assertion: assertion(),
                    caller: caller(),
                    args: args(),
                    toString: toString
                };
            },
            /*
             * Function that is called when representing a function as a String.
             */
            _functionToString = function () {
                var str = 'Function ' + this.name + '()';
                if (this.passed) {
                    str += ' Passed';
                } else {
                    str += ' Failed';
                }
                i = this.errors.length;
                this.errors.map(function (error) {
                    str += '\n |-' + error;
                });
                return str;
            },
            /*
             * Class that represents functions under test.
             */
            TestFunction = function (func, funcName) {
                if (typeof func === 'undefined') {
                    throw new UndefinedParameterException('func', 1);
                }
                if (typeof func !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof func
                    );
                }
                if (func === null) {
                    throw new NullPointerException();
                }
                if (typeof funcName !== 'undefined') {
                    if (typeof funcName !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof funcName
                        );
                    }
                    if (funcName === null) {
                        throw new NullPointerException();
                    }
                }
                var _fun = func,
                    _passed = true,
                    _errors = [],
                    _time = 0,
                    _name = (typeof funcName === 'string') ? funcName : getFunctionName.apply(func),
                    /*
                     * Returns the function under test.
                     */
                    fun = function () {
                        return _fun;
                    },
                    /*
                     * Returns the name of the function under test.
                     */
                    name = function () {
                        return _name;
                    },
                    /*
                     * Returns true if the function under test has passed all 
                     * assertions, else false.
                     */
                    passed = function () {
                        return _passed;
                    },
                    /*
                     * Adds a TestError assertion error to the TestFunction.
                     */
                    addError = function (error) {
                        _passed = false;
                        _errors.push(error);
                    },
                    /*
                     * Returns the time it took to execute the function under test.
                     */
                    time = function () {
                        return _time;
                    },
                    /*
                     * Runs the test, potentially executing the function under test.
                     * All TestErrors that occurs when the function under test is 
                     * executed will be added to the TestFunction object.
                     */
                    test = function (runTest) {
                        if (typeof runTest === 'undefined') {
                            runTest = true;
                        }
                        if (runTest) {
                            _time = new Date().getTime();
                            _fun.apply(null);
                            _time = new Date().getTime() - _time;
                        }
                        return _passed;
                    },
                    /*
                     * Returns a String representation of the object.
                     */
                    toString = function () {
                        var error_strings = [],
                            obj;
                        _errors.map(function (error) {
                            error_strings.push(error.toString());
                        });
                        obj = {
                            name: _name,
                            passed: _passed,
                            time: _time,
                            errors: error_strings
                        };
                        return _functionToString.apply(obj);
                    };
                return {
                    fun: fun,
                    name: name,
                    passed: passed,
                    addError: addError,
                    time: time,
                    test: test,
                    toString: toString
                };
            },
            /*
             * Function that is called when represnting a TestMethod as a String.
             */
            _methodToString = function () {
                var str = 'Method ' + this.name + '()';
                if (this.passed) {
                    str += ' Passed';
                } else {
                    str += ' Failed';
                }
                this.errors.map(function (error) {
                    str += '\n |  | ' + error;
                });
                return str;
            },
            /*
             * Class that represents methods under test.
             */
            TestMethod = function (methodFun, caller) {
                if (typeof methodFun === 'undefined') {
                    throw new UndefinedParameterException('methodFun', 1);
                }
                if (typeof methodFun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof methodFun
                    );
                }
                if (methodFun === null) {
                    throw new NullPointerException();
                }
                if (typeof caller === 'undefined') {
                    throw new UndefinedParameterException('caller', 2);
                }
                if (typeof caller !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof caller
                    );
                }
                if (caller === null) {
                    throw new NullPointerException();
                }
                var _method = methodFun,
                    _name = caller,
                    _passed = true,
                    _errors = [],
                    _time = 0,
                    /*
                     * Returns the method under test.
                     */
                    method = function () {
                        return _method;
                    },
                    /*
                     * Returns the name of the method under test.
                     */
                    name = function () {
                        return _name;
                    },
                    /*
                     * Returns true if the method under test has passed all 
                     * assertions, else false.
                     */
                    passed = function () {
                        return _passed;
                    },
                    /*
                     * Adds a TestError error to the TestMethod.
                     */
                    addError = function (error) {
                        _passed = false;
                        _errors.push(error);
                    },
                    /*
                     * Potentially sets the time it took to execute the method 
                     * under test. Always returns the time property of the 
                     * TestMethod.
                     */
                    time = function (time) {
                        if (typeof time === 'undefined') {
                            return _time;
                        }
                        if (typeof time === 'number' && time % 1 === 0) {
                            _time = time;
                            return time;
                        } else if (time === null) {
                            throw new NullPointerException();
                        } else {
                            throw new IllegalParameterException(
                                'int',
                                typeof time
                            );
                        }
                    },
                    toString = function () {
                        var error_strings = [],
                            obj;
                        _errors.map(function (error) {
                            error_strings.push(error.toString());
                        });
                        obj = {
                            name: _name,
                            passed: _passed,
                            time: _time,
                            errors: error_strings
                        };
                        return _methodToString.apply(obj);
                    };
                return {
                    method: method,
                    name: name,
                    passed: passed,
                    addError: addError,
                    time: time,
                    toString: toString
                };
            },
            /*
             * Class that represents a pending assertion that will trigger by an 
             * event.
             */
            TestEvent = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                var _value = {},
                    onchange = fun,
                    /*
                     * Makes the assertion pass and executes the onchange function.
                     */
                    passed = function () {
                        _value = true;
                        onchange();
                    },
                    /*
                     * Makes the assertion fail and executes the onchange function.
                     */
                    failed = function () {
                        _value = false;
                        onchange();
                    },
                    /*
                     * Returns the value of the pending assertion.
                     */
                    value = function () {
                        return _value;
                    };
                return {
                    onchange: onchange,
                    passed: passed,
                    failed: failed,
                    value: value
                };
            },
            /*
             * Function that is called when converting a TestObject to a String.
             */
            _objectToString = function () {
                var str = 'Object ' + this.name;
                if (this.passed) {
                    str += ' Passed';
                } else {
                    str += ' Failed';
                }
                this.methods.map(function (method) {
                    str += "\n | " + method;
                });
                return str;
            },
            /*
             * Class that represents an Object under test.
             */
            TestObject = function (obj, objName) {
                if (typeof obj === 'undefined') {
                    throw new UndefinedParameterException('obj', 1);
                }
                if (typeof obj !== 'object') {
                    throw new IllegalParameterException(
                        'object',
                        typeof obj
                    );
                }
                if (obj === null) {
                    throw new NullPointerException();
                }
                if (typeof objName !== 'undefined') {
                    if (typeof objName !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof objName
                        );
                    }
                    if (objName === null) {
                        throw new NullPointerException();
                    }
                }
                var _obj = obj,
                    _name = (typeof objName === 'string') ? objName : getClass.apply(_obj),
                    _setUp = null,
                    _tearDown = null,
                    _methods = [],
                    _currentMethod = null,
                    _wasTimed = false,
                    _time = 0,
                    _passed = true,
                    /*
                     * Constructor of the class.
                     */
                    construct = function (obj) {
                        var method;
                        _obj = obj;
                        if (typeof obj[_setUpName] === 'function') {
                            _setUp = obj[_setUpName];
                        }
                        for (method in obj) {
                            if (typeof obj[method] === 'function'
                                    && method.length >= _methodSuffix.length
                                    && method.substr(-_methodSuffix.length) === _methodSuffix) {
                                _methods.push(new TestMethod(obj[method], method));
                            }
                        }
                        if (typeof obj[_tearDownName] === 'function') {
                            _tearDown = obj[_tearDownName];
                        }
                    },
                    /*
                     * Potentially executes all method under test of the object 
                     * under test. All failed assertion TestErrors will be added to  
                     * the TestMethod that hold the method under test where the 
                     * assertion failed.
                     */
                    test = function (runTest) {
                        if (typeof runTest !== 'undefined') {
                            if (typeof runTest !== 'boolean') {
                                throw new IllegalParameterException(
                                    'boolean',
                                    typeof runTest
                                );
                            }
                            if (runTest === null) {
                                throw new NullPointerException();
                            }
                        }
                        var time,
                            totalTime = 0;
                        if (typeof runTest === 'undefined') {
                            runTest = true;
                        }
                        time = new Date().getTime();
                        if(_setUp != null) {
                            _setUp.apply(_obj);
                        }
                        _methods.map(function (method) {
                            _currentMethod = method;
                            _currentMethod.method().apply(_obj);
                            _currentMethod.time(time);
                            totalTime += time;
                        });
                        if(_tearDown != null) {
                            _tearDown.apply(_obj);
                        }
                        time = new Date().getTime() - time;
                        _time = totalTime;
                        return _passed;
                    },
                    /*
                     * Returns the name of the object under test.
                     */
                    name = function () {
                        return _name;
                    },
                    /*
                     * Returns true if no assertion errors occured when executing 
                     * the methods under test of the object under test, else false. 
                     */
                    passed = function () {
                        return _passed;
                    },
                    /*
                     * Returns the name of the method under test that is currently 
                     * being executed.
                     */
                    currentMethodName = function () {
                        if (typeof _currentMethod !== 'undefined'
                                && _currentMethod !== null) {
                            return _currentMethod.name();
                        } else {
                            return null;
                        }
                    },
                    /*
                     * Adds an error to the TestMethod that holds the method under 
                     * test where the error occured.
                     */
                    addError = function (error, caller) {
                        if (typeof error !== 'object') {
                            throw new IllegalParameterException(
                                'object',
                                typeof error
                            );
                        }
                        if (error === null) {
                            throw new NullPointerException();
                        }
                        if (typeof caller !== 'undefined') {
                            if (typeof caller !== 'boolean') {
                                throw new IllegalParameterException(
                                    'boolean',
                                    typeof caller
                                );
                            }
                            if (caller === null) {
                                throw new NullPointerException();
                            }
                        }
                        var returnvar = false;
                        if (typeof caller === 'undefined'
                                || caller === currentMethodName()) {
                            _currentMethod.addError(error);
                            _passed = false;
                            returnvar = true;
                        } else {
                            _methods.map(function (method) {
                                if (method.name() === caller) {
                                    method.addError(error);
                                    _passed = false;
                                    returnvar = true;
                                }
                            });
                        }
                        return returnvar;
                    },
                    /*
                     * Returns a String representation of the object.
                     */
                    toString = function () {
                        var method_strings = [],
                            obj;
                        _methods.map(function (method) {
                            method_strings.push(method.toString());
                        });
                        obj = {
                            name: _name,
                            passed: _passed,
                            time: _time,
                            methods: method_strings
                        };
                        return _objectToString.apply(obj);
                    };
                construct(obj);
                return {
                    test: test,
                    name: name,
                    passed: passed,
                    currentMethodName: currentMethodName,
                    addError: addError,
                    toString: toString
                };
            },
            /*
             * Updates the suffix that functions must end with to be added to the 
             * test engine automatically. Always returns the function suffix.
             */
            functionSuffix = function (suffix) {
                if (typeof suffix !== 'undefined') {
                    if (typeof suffix !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof suffix
                        );
                    }
                    if (suffix === null) {
                        throw new NullPointerException();
                    }
                }
                if (typeof suffix === 'string') {
                    _functionSuffix = suffix;
                }
                return _functionSuffix;
            },
            /*
             * Updates the suffix that classes must end with to be added to the 
             * test engine automatically. Always returns the class suffix.
             */
            objectSuffix = function (suffix) {
                if (typeof suffix !== 'undefined') {
                    if (typeof suffix !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof suffix
                        );
                    }
                    if (suffix === null) {
                        throw new NullPointerException();
                    }
                }
                if (typeof suffix === 'string') {
                    _objectSuffix = suffix;
                }
                return _objectSuffix;
            },
            /*
             * Updates the suffix that methods must end with to be added to the 
             * test engine automatically. Always returns the method suffix.
             */
            methodSuffix = function (suffix) {
                if (typeof suffix !== 'undefined') {
                    if (typeof suffix !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof suffix
                        );
                    }
                    if (suffix === null) {
                        throw new NullPointerException();
                    }
                }
                if (typeof suffix === 'string') {
                    _methodSuffix = suffix;
                }
                return _methodSuffix;
            },
            /*
             * Updates the name of the methods that will be executed by the engine 
             * before the methods under test should be executed. Always returns the 
             * set up method name.
             */
            setUpName = function (name) {
                if (typeof name !== 'undefined') {
                    if (typeof name !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof name
                        );
                    }
                    if (name === null) {
                        throw new NullPointerException();
                    }
                }
                if (typeof name === 'string') {
                    _setUpName = name;
                }
                return _setUpName;
            },
            /*
             * Updates the name of the methods that will be executed by the engine 
             * after all the methods under test have been executed. Always returns 
             * the tear down method name.
             */
            tearDownName = function (name) {
                if (typeof name !== 'undefined') {
                    if (typeof name !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof name
                        );
                    }
                    if (name === null) {
                        throw new NullPointerException();
                    }
                }
                if (typeof name === 'string') {
                    _tearDownName = name;
                }
                return _tearDownName;
            },
            /*
             * Adds an object to the test engine. The method with the same name as 
             * the setUpName property will be executed first, then all methods
             * which names ends with the methodSuffix and lastly the method that 
             * has the name of the tearDownName proporty. All failed assertion
             * will be displayed when the all the former methods have been executed.
             */
            addObject = function (obj, objName) {
                if (typeof obj === 'undefined') {
                    throw new UndefinedParameterException('obj', 1);
                }
                if (typeof obj !== 'object') {
                    throw new IllegalParameterException(
                        'object',
                        typeof obj
                    );
                }
                if (obj === null) {
                    throw new NullPointerException();
                }
                if(typeof objName !== 'undefined') {
                    if(typeof objName !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof objName
                        );
                    }
                    if(objName === null) {
                        throw new NullPointerException();
                    }
                }
                _objects.push(new TestObject(obj, objName));
            },
            /*
             * Adds a function to the test engine. The function will be executed 
             * and assertion errors will be displayed if any are encountered while 
             * executing the function.
             */
            addFunction = function (fun, funName) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                if(typeof funName !== 'undefined') {
                    if(typeof funName !== 'string') {
                        throw new IllegalParameterException(
                            'string',
                            typeof funName
                        );
                    }
                    if(funName === null) {
                        throw new NullPointerException();
                    }
                }
                _functions.push(new TestFunction(fun, funName));
            },
            /*
             * Marks an assertion as passed.
             */
            _assertionPassed = function (assertion, caller, args) {
                if (typeof assertion === 'undefined') {
                    throw new UndefinedParameterException('assertion', 1);
                }
                if (typeof assertion !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof assertion
                    );
                }
                if (assertion === null) {
                    throw new NullPointerException();
                }
                if (typeof caller === 'undefined') {
                    throw new UndefinedParameterException(
                        'caller',
                        2
                    );
                }
                if (typeof caller !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof caller
                    );
                }
                if (caller === null) {
                    throw new NullPointerException();
                }
                if (typeof args === 'undefined') {
                    throw new UndefinedParameterException('args', 3);
                }
                if (typeof args !== 'object') {
                    throw new IllegalParameterException(
                        'object',
                        typeof args
                    );
                }
                if (args === null) {
                    throw new NullPointerException();
                }
            },
            /*
             * Marks an assertion as failed, adding an error to the corresponding 
             * function or method under test.
             */
            _assertionFailed = function (assertion, caller, args) {
                if (typeof assertion === 'undefined') {
                    throw new UndefinedParameterException(
                        'assertion',
                        1
                    );
                }
                if (typeof assertion !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof assertion
                    );
                }
                if (assertion === null) {
                    throw new NullPointerException();
                }
                if (typeof caller === 'undefined') {
                    throw new UndefinedParameterException(
                        'caller',
                        2
                    );
                }
                if (typeof caller !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof caller
                    );
                }
                if (caller === null) {
                    throw new NullPointerException();
                }
                if (typeof args === 'undefined') {
                    throw new UndefinedParameterException('args', 3);
                }
                if (typeof args !== 'object') {
                    throw new IllegalParameterException(
                        'object',
                        typeof args
                    );
                }
                if (args === null) {
                    throw new NullPointerException();
                }
                if (this === null) {
                    return;
                }
                this.addError(new TestError(assertion, caller, args));
            },
            /*
             * Adds an assertion function to the engine. The function can be 
             * reached by calling "PJsUnit.functionName(parameters);" where the 
             * functionName is the name parameter of this method. The input 
             * function should return true if the assertion passed and false if 
             * the assertion failed.
             */
            addAssertion = function (name, fun) {
                if (typeof name === 'undefined') {
                    throw new UndefinedParameterException('name', 1);
                }
                if (typeof name !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof name
                    );
                }
                if (name === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 2);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                var assertFun = function () {
                    var caller,
                        current = null,
                        args = arguments,
                        passed = fun.apply(null, arguments);
                    if (typeof assertFun.caller !== 'undefined'
                        && typeof assertFun.caller.name === 'string'
                        && assertFun.caller.name.lenth > 0)
                    {
                        if (typeof _currentFunction !== null
                            && _currentFunction.name() === assertFun.caller.name)
                        {
                            caller = _currentFunction.name();
                            current = _currentFunction;
                        } else {
                            caller = assertFun.caller.name;
                        }
                    } else if (_currentObject !== null) {
                        caller = _currentObject.currentMethodName();
                        current = _currentObject;
                    } else if (_currentFunction !== null) {
                        caller = _currentFunction.name();
                        current = _currentFunction;
                    } else {
                        caller = _currentFunction.name();
                    }
                    if (passed === true) {
                        _assertionPassed.apply(current, [name, caller, args]);
                    } else if (passed === false) {
                        _assertionFailed.apply(current, [name, caller, args]);
                    }
                };
                this[name] = assertFun;
            },
            /*
             * Adds a pending assertion function to the engine. The function can be 
             * reached by calling "PJsUnit.functionName(parameters);" where the 
             * functionName is the name parameter of this method. The function will 
             * be called with a TestEvent object. Calling "this.passed()" will make 
             * the pending assertion pass while calling "this.failed()" will make 
             * the pending assertion fail.
             */
            addEvent = function (name, fun) {
                if (typeof name === 'undefined') {
                    throw new UndefinedParameterException('name', 1);
                }
                if (typeof name !== 'string') {
                    throw new IllegalParameterException(
                        'string',
                        typeof name
                    );
                }
                if (name === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 2);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                var caller,
                    current = null,
                    eventFun = function () {
                        var args = arguments,
                            eventListener;
                        if (typeof eventFun.caller !== 'undefined'
                            && typeof eventFun.caller.name === 'string'
                            && typeof eventFun.caller.name.length > 0)
                        {
                            if (_currentFunction !== null &&
                                _currentFunction.name() === eventFun.caller.name)
                            {
                                caller = _currentFunction.name();
                                current = _currentFunction;
                            } else {
                                caller = eventFun.caller.name;
                            }
                        } else if (_currentObject !== null) {
                            caller = _currentObject.currentMethodName();
                            current = _currentObject;
                        } else if (_currentFunction !== null) {
                            caller = _currentFunction.name();
                            current = _currentFunction;
                        } else {
                            caller = _currentFunction.name();
                        }
                        eventListener = new TestEvent(function () {
                            if (eventListener.value() === true) {
                                _assertionPassed.apply(current, [name, caller, args]);
                            } else if (eventListener.value() === false) {
                                _assertionFailed.apply(current, [name, caller, args]);
                            }
                        });
                        fun.apply(eventListener, arguments);
                    };
                this[name] = eventFun;
            },
            /*
             * Executes all the test objects and test functions, adding assertion 
             * errors to the method or function where they occured.
             */
            test = function (runTest) {
                if (typeof runTest !== 'undefined') {
                    if (typeof runTest !== 'boolean') {
                        throw new IllegalParameterException(
                            'boolean',
                            typeof runTest
                        );
                    }
                }
                if (typeof runTest === 'undefined') {
                    runTest = true;
                }
                _objects.map(function (obj) {
                    _currentObject = obj;
                    if (obj.test(runTest)) {
                        _passedCount++;
                    } else {
                        _failedCount++;
                    }
                });
                _currentObject = null;
                _functions.map(function (fun) {
                    _currentFunction = fun;
                    if (fun.test(runTest)) {
                        _passedCount++;
                    } else {
                        _failedCount++;
                    }
                });
                _currentFunction = null;
            },
            /*
             * Returns the object as a String representation.
             */
            toString = function () {
                var str = '';
                _objects.map(function (object) {
                    str += object.toString() + '\n';
                });
                _functions.map(function (fun) {
                    str += fun.toString() + '\n';
                });
                return str;
            },
            /*
             * Changes the function that is used when converting an assertion error 
             * to a String to the parameter function. 
             */
            errorToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                } else {
                    _errorToString = fun;
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
            },
            /*
             * Changes the function that is used when converting a function under 
             * test to a String to the parameter function.
             */
            functionToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                } else {
                    _functionToString = fun;
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
            },
            /*
             * Changes the function that is used when converting a method under 
             * test to a String to the parameter function.
             */
            methodToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                } else {
                    _methodToString = fun;
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
            },
            /*
             * Changes the function that is used when converting an object under 
             * test to a String to the parameter function.
             */
            objectToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                } else {
                    _objectToString = fun;
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
            },
            writeFun = function (fun, obj) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedParameterException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalParameterException(
                        'function',
                        typeof fun
                    );
                } else {
                    _writeFun = fun;
                    _writeFunObj = obj;
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
            };
        /*
         * The following code will automatically check all defined variables and 
         * add objects and functions with the correct suffix to the test engine.
         * When all variables have been added testing will automatically start and 
         * the output will be printed with the writeFun function.
         */
        if(typeof window !== 'undefined') {
            var onDocumentLoad = function() {
                var element;
                for (element in window) {
                    if (typeof window[element] === 'object') {
                        if(element.substr(element.length - _objectSuffix.length) === _objectSuffix) {
                            PJsUnit.addObject(window[element], element);
                        }
                    } else if (typeof window[element] === 'function')
                        if(element.substr(element.length - _functionSuffix.length) === _functionSuffix) {
                            PJsUnit.addFunction(window[element], element);
                        }
                    }
                    PJsUnit.test();
                    _writeFun.apply(_writeFunObj, [PJsUnit.toString()]);
                }
            if (typeof window.addEventListener !== 'undefined') {
                window.addEventListener('load', onDocumentLoad, false);
            } else if (typeof window.attachEvent !== 'undefined') {
                window.attachEvent('onload', onDocumentLoad);
            }
        }
        return {
            functionSuffix: functionSuffix,
            objectSuffix: objectSuffix,
            methodSuffix: methodSuffix,
            setUpName: setUpName,
            tearDownName: tearDownName,
            errorToString: errorToString,
            functionToString: functionToString,
            objectToString: objectToString,
            methodToString: methodToString,
            addAssertion: addAssertion,
            addEvent: addEvent,
            addObject: addObject,
            addFunction: addFunction,
            test: test,
            writeFun: writeFun,
            toString: toString
        };
    })();
    PJsUnit.addAssertion(
        'assertTrue',
        function (a) {
            return a === true;
        }
    );
    PJsUnit.addAssertion(
        'assertFalse',
        function (a) {
            return a === false;
        }
    );
    if (typeof window !== 'undefined') {
        if(window.XMLHttpRequest !== 'undefined') {
            PJsUnit.addEvent(
                'xhrStatusEquals',
                function(url, status, method) {
                    var e = this,
                        xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if(xhr.status === status) {
                            e.passed();
                        } else if(xhr.status >= 400) {
                            e.failed();
                        }
                    };
                    if(method === 'undefined') {
                        method = 'GET';
                    }
                    xhr.open(method, url, true);
                }
            );
        }
    }
}
