if (typeof PJsUnit === 'undefined') {
    var PJsUnit = (function () {
        var echo = (typeof console !== 'undefined' && console.log) ? console.log : print,
            _failedCount = 0,
            _passedCount = 0,
            _passed = true,
            _functions = [],
            _objects = [],
            _currentFunction = null,
            _currentObject = null,
            _functionSuffix = '_test',
            _classSuffix = '_test',
            _methodSuffix = '_test',
            _setUpName = 'set_up',
            _tearDownName = 'tear_down',
            _time = 0,
            getClass = function () {
                var funcNameRegex = /function (.{1,})\(/,
                    results = (funcNameRegex).exec(this.constructor.toString());
                return (results && results.length > 1) ? results[1] : '[Anonymous]';
            },
            getFunctionName = function () {
                var funcNameRegex = /function (.{1,})\(/,
                    results = (funcNameRegex).exec(this.toString());
                return (results && results.length > 1) ? results[1] : '[Anonymous]';
            },
            IllegalArgumentException = function (expectedType, receivedType) {
                var name = 'IllegalArgumentException',
                    message =
                        'Bad argument given; expected argument with type "' +
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
            NullPointerException = function () {
                var name = 'NullPointerException',
                    message =
                        'Null pointer argument given; ' +
                        'received argument with value "null".',
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
            UndefinedArgumentException = function (argumentName, argumentIndex) {
                var name = 'UndefinedArgumentException',
                    message =
                        'Argument missing; expected argument "' +
                        name + '" as argument number ' +
                        argumentIndex + '.',
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
                if (typeof this.callee() === 'undefined') {
                    argarray[0] =
                        'The assertion "' +
                        this.assertion() + argstr +
                        '" failed';
                } else {
                    argarray[0] =
                        '"' + this.callee() +
                        '" failed the assertion "' +
                        this.assertion() + argstr + '".';
                }
                return argarray[0];
            },
            TestError = function (assertionName, calleeName, argumentObject) {
                if (typeof assertionName === 'undefined') {
                    throw new UndefinedArgumentException(
                        'assertionName',
                        1
                    );
                }
                if (typeof assertionName !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof assertionName
                    );
                }
                if (assertionName === null) {
                    throw new NullPointerException();
                }
                if (typeof calleeName === 'undefined') {
                    throw new UndefinedArgumentException('calleeName', 2);
                }
                if (typeof calleeName !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof calleeName
                    );
                }
                if (calleeName === null) {
                    throw new NullPointerException();
                }
                if (typeof argumentObject !== 'undefined' && typeof argumentObject !== 'object') {
                    throw new IllegalArgumentException(
                        'object',
                        typeof argumentObject
                    );
                }
                var _assertion = assertionName,
                    _callee = calleeName,
                    _args = argumentObject,
                    assertion = function () {
                        return _assertion;
                    },
                    callee = function () {
                        return _callee;
                    },
                    args = function () {
                        return _args;
                    },
                    toString = function () {
                        return _errorToString.apply({
                            assertion: assertion,
                            callee: callee,
                            args: args()
                        });
                    };
                return {
                    assertion: assertion,
                    callee: callee,
                    args: args,
                    toString: toString
                };
            },
            _functionToString = function () {
                var str = 'Function ' + this.name + '()';
                if (this.passed) {
                    str += ' Passed';
                } else {
                    str += ' Failed';
                }
                this.errors.map(function (error) {
                    str += '\n  ' + error;
                });
                return str;
            },
            TestFunction = function (func, funcName) {
                if (typeof func === 'undefined') {
                    throw new UndefinedArgumentException('func', 1);
                }
                if (typeof func !== 'function') {
                    throw new IllegalArgumentException(
                        'function',
                        typeof func
                    );
                }
                if (func === null) {
                    throw new NullPointerException();
                }
                if (typeof funcName !== 'undefined') {
                    if (typeof funcName !== 'string') {
                        throw new IllegalArgumentException(
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
                    _name = (typeof funcName !== 'undefined') ? funcName : getFunctionName.apply(func),
                    fun = function () {
                        return _fun;
                    },
                    name = function () {
                        return _name;
                    },
                    passed = function () {
                        return _passed;
                    },
                    addError = function (error) {
                        _passed = false;
                        _errors.push(error);
                    },
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
                            throw new IllegalArgumentException(
                                'int',
                                typeof time
                            );
                        }
                    },
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
            _methodToString = function () {
                var str = 'Method ' + this.name + '()';
                if (this.passed) {
                    str += ' Passed';
                } else {
                    str += ' Failed';
                }
                this.errors.map(function (error) {
                    str += '\n    ' + error;
                });
                return str;
            },
            TestMethod = function (methodFun, methodName) {
                if (typeof methodFun === 'undefined') {
                    throw new UndefinedArgumentException('methodFun', 1);
                }
                if (typeof methodFun !== 'function') {
                    throw new IllegalArgumentException(
                        'function',
                        typeof methodFun
                    );
                }
                if (methodFun === null) {
                    throw new NullPointerException();
                }
                if (typeof methodName === 'undefined') {
                    throw new UndefinedArgumentException('methodName', 2);
                }
                if (typeof methodName !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof methodName
                    );
                }
                if (methodName === null) {
                    throw new NullPointerException();
                }
                var _method = methodFun,
                    _name = methodName,
                    _passed = true,
                    _errors = [],
                    _time = 0,
                    method = function () {
                        return _method;
                    },
                    name = function () {
                        return _name;
                    },
                    passed = function () {
                        return _passed;
                    },
                    addError = function (error) {
                        _passed = false;
                        _errors.push(error);
                    },
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
                            throw new IllegalArgumentException(
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
            TestEvent = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                var _value = {},
                    onchange = fun,
                    passed = function () {
                        _value = true;
                        onchange();
                    },
                    failed = function () {
                        _value = false;
                        onchange();
                    },
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
            _objectToString = function () {
                var str = 'Object ' + this.name;
                if (this.passed) {
                    str += ' Passed';
                } else {
                    str += ' Failed';
                }
                this.methods.map(function (method) {
                    str += '\n  ' + method;
                });
                return str;
            },
            TestObject = function (obj, objName) {
                if (typeof obj === 'undefined') {
                    throw new UndefinedArgumentException('obj', 1);
                }
                if (typeof obj !== 'object') {
                    throw new IllegalArgumentException(
                        'object',
                        typeof obj
                    );
                }
                if (obj === null) {
                    throw new NullPointerException();
                }
                if (typeof objName !== 'undefined') {
                    if (typeof objName !== 'string') {
                        throw new IllegalArgumentException(
                            'string',
                            typeof objName
                        );
                    }
                    if (objName === null) {
                        throw new NullPointerException();
                    }
                }
                var _obj = obj,
                    _name = (typeof objName !== 'undefined') ? objName : getClass.apply(_obj),
                    _setUp = null,
                    _tearDown = null,
                    _methods = [],
                    _currentMethod = null,
                    _wasTimed = false,
                    _time = 0,
                    _passed = true,
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
                    test = function (runTest) {
                        if (typeof runTest !== 'undefined') {
                            if (typeof runTest !== 'boolean') {
                                throw new IllegalArgumentException(
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
                    name = function () {
                        return _name;
                    },
                    passed = function () {
                        return _passed;
                    },
                    currentMethodName = function () {
                        if (typeof _currentMethod !== 'undefined'
                                && _currentMethod !== null) {
                            return _currentMethod.name();
                        } else {
                            return null;
                        }
                    },
                    addError = function (error, methodName) {
                        if (typeof error !== 'object') {
                            throw new IllegalArgumentException(
                                'object',
                                typeof error
                            );
                        }
                        if (error === null) {
                            throw new NullPointerException();
                        }
                        if (typeof methodName !== 'undefined') {
                            if (typeof methodName !== 'boolean') {
                                throw new IllegalArgumentException(
                                    'boolean',
                                    typeof methodName
                                );
                            }
                            if (methodName === null) {
                                throw new NullPointerException();
                            }
                        }
                        var returnvar = false;
                        if (typeof methodName === 'undefined'
                                || methodName === currentMethodName()) {
                            _currentMethod.addError(error);
                            _passed = false;
                            returnvar = true;
                        } else {
                            _methods.map(function (method) {
                                if (method.name() === methodName) {
                                    method.addError(error);
                                    _passed = false;
                                    returnvar = true;
                                }
                            });
                        }
                        return returnvar;
                    },
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
            functionSuffix = function (suffix) {
                if (typeof suffix !== 'undefined') {
                    if (typeof suffix !== 'string') {
                        throw new IllegalArgumentException(
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
            classSuffix = function (suffix) {
                if (typeof suffix !== 'undefined') {
                    if (typeof suffix !== 'string') {
                        throw new IllegalArgumentException(
                            'string',
                            typeof suffix
                        );
                    }
                    if (suffix === null) {
                        throw new NullPointerException();
                    }
                }
                if (typeof suffix === 'string') {
                    _classSuffix = suffix;
                }
                return _classSuffix;
            },
            methodSuffix = function (suffix) {
                if (typeof suffix !== 'undefined') {
                    if (typeof suffix !== 'string') {
                        throw new IllegalArgumentException(
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
            setUpName = function (name) {
                if (typeof name !== 'undefined') {
                    if (typeof name !== 'string') {
                        throw new IllegalArgumentException(
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
            tearDownName = function (name) {
                if (typeof name !== 'undefined') {
                    if (typeof name !== 'string') {
                        throw new IllegalArgumentException(
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
            addObject = function (obj) {
                if (typeof obj === 'undefined') {
                    throw new UndefinedArgumentException('obj', 1);
                }
                if (typeof obj !== 'object') {
                    throw new IllegalArgumentException(
                        'object',
                        typeof obj
                    );
                }
                if (obj === null) {
                    throw new NullPointerException();
                }
                _objects.push(new TestObject(obj));
            },
            addFunction = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                _functions.push(new TestFunction(fun));
            },
            _assertionPassed = function (assertion, callee, args) {
                if (typeof assertion === 'undefined') {
                    throw new UndefinedArgumentException('assertion', 1);
                }
                if (typeof assertion !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof assertion
                    );
                }
                if (assertion === null) {
                    throw new NullPointerException();
                }
                if (typeof callee === 'undefined') {
                    throw new UndefinedArgumentException(
                        'callee',
                        2
                    );
                }
                if (typeof callee !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof callee
                    );
                }
                if (callee === null) {
                    throw new NullPointerException();
                }
                if (typeof args === 'undefined') {
                    throw new UndefinedArgumentException('args', 3);
                }
                if (typeof args !== 'object') {
                    throw new IllegalArgumentException(
                        'object',
                        typeof args
                    );
                }
                if (args === null) {
                    throw new NullPointerException();
                }
            },
            _assertionFailed = function (assertion, callee, args) {
                if (typeof assertion === 'undefined') {
                    throw new UndefinedArgumentException(
                        'assertion',
                        1
                    );
                }
                if (typeof assertion !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof assertion
                    );
                }
                if (assertion === null) {
                    throw new NullPointerException();
                }
                if (typeof callee === 'undefined') {
                    throw new UndefinedArgumentException(
                        'callee',
                        2
                    );
                }
                if (typeof callee !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof callee
                    );
                }
                if (callee === null) {
                    throw new NullPointerException();
                }
                if (typeof args === 'undefined') {
                    throw new UndefinedArgumentException('args', 3);
                }
                if (typeof args !== 'object') {
                    throw new IllegalArgumentException(
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
                this.addError(new TestError(assertion, callee, args));
            },
            addAssertion = function (name, fun) {
                if (typeof name === 'undefined') {
                    throw new UndefinedArgumentException('name', 1);
                }
                if (typeof name !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof name
                    );
                }
                if (name === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 2);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                var assertFun = function () {
                    var callee,
                        current = null,
                        args = arguments,
                        passed = fun.apply(null, arguments);
                    if (typeof assertFun.caller !== 'undefined'
                            && assertFun.caller.name !== null
                            && assertFun.caller.name !== '') {
                        if (typeof _currentFunction !== null
                                && _currentFunction.name() === assertFun.caller.name) {
                            callee = _currentFunction.name();
                            current = _currentFunction;
                        } else {
                            callee = assertFun.caller.name;
                        }
                    } else if (_currentObject !== null) {
                        callee = _currentObject.currentMethodName();
                        current = _currentObject;
                    } else {
                        callee = _currentFunction.name();
                    }
                    if (passed === true) {
                        _assertionPassed.apply(current, [name, callee, args]);
                    } else if (passed === false) {
                        _assertionFailed.apply(current, [name, callee, args]);
                    }
                };
                this[name] = assertFun;
            },
            addEvent = function (name, fun) {
                if (typeof name === 'undefined') {
                    throw new UndefinedArgumentException('name', 1);
                }
                if (typeof name !== 'string') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof name
                    );
                }
                if (name === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 2);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'function',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                var callee,
                    current = null,
                    eventFun = function () {
                        var args = arguments,
                            eventListener;
                        if (typeof eventFun.caller !== 'undefined' &&
                                eventFun.caller.name !== '') {
                            if (typeof _currentFunction !== 'undefined' &&
                                    _currentFunction.name() === eventFun.caller.name) {
                                callee = _currentFunction.name();
                                current = _currentFunction;
                            } else {
                                callee = eventFun.caller.name;
                            }
                        } else if (_currentObject !== null) {
                            callee = _currentObject.currentMethodName();
                            current = _currentObject;
                        } else {
                            callee = _currentFunction.name();
                        }
                        eventListener = new TestEvent(function () {
                            if (eventListener.value() === true) {
                                _assertionPassed.apply(current, [name, callee, args]);
                            } else if (eventListener.value() === false) {
                                _assertionFailed.apply(current, [name, callee, args]);
                            }
                        });
                        fun.apply(eventListener, arguments);
                    };
                this[name] = eventFun;
            },
            test = function (runTest) {
                if (typeof runTest !== 'undefined') {
                    if (typeof runTest !== 'boolean') {
                        throw new IllegalArgumentException(
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
            toString = function () {
                var str = '';
                _objects.map(function (object) {
                    str += object.toString();
                });
                if (_objects.length > 0 && _functions.length > 0) {
                    str += '\n';
                }
                _functions.map(function (fun) {
                    str += fun.toString();
                });
                return str;
            },
            errorToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'function') {
                    _errorToString = fun;
                } else {
                    throw new IllegalArgumentException('function', typeof fun);
                }
            },
            methodToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'function') {
                    _methodToString = fun;
                } else {
                    throw new IllegalArgumentException('function', typeof fun);
                }
            },
            objectToString = function (fun) {
                if (typeof fun === 'undefined') {
                    throw new UndefinedArgumentException('fun', 1);
                }
                if (typeof fun !== 'function') {
                    throw new IllegalArgumentException(
                        'string',
                        typeof fun
                    );
                }
                if (fun === null) {
                    throw new NullPointerException();
                }
                if (typeof fun === 'function') {
                    _objectToString = fun;
                } else {
                    throw new IllegalArgumentException('function', typeof fun);
                }
            };
        return {
            functionSuffix: functionSuffix,
            classSuffix: classSuffix,
            methodSuffix: methodSuffix,
            setUpName: setUpName,
            tearDownName: tearDownName,
            addAssertion: addAssertion,
            addEvent: addEvent,
            addObject: addObject,
            addFunction: addFunction,
            test: test,
            toString: toString
        };
    })();
}
