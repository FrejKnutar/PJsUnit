if(typeof(PJsUnit) == 'undefined') {
	var PJsUnit = (function () {
		var echo = (typeof(console) != 'undefined' && console.log) ? console.log : print;
		var _failedCount = 0;
		var _passedCount = 0;
		var _passed = true;
		var _functions = [];
		var _objects = [];
		var _currentFunction = null
        var _currentObject = null;
		var _functionSuffix = "_test";
		var _classSuffix = "_test";
		var _methodSuffix = "_test";
		var _setUpName = "set_up";
		var _tearDownName = "tear_down";
		var _time = 0;
		var _fbold = 'font-weight:bold;';
		var _fitalic = 'font-style:italic;';
		var _fnormal = 'font-weight:normal;font-style:normal;';
		var _red = 'color:#DD1144;';
		var _teal = 'color:#009999;';
		var _green = 'color:#2222CC;';
		var _black = 'color:#000000;';
		var _cnormal = _black + _fnormal;
		var _cboolean = _black + _fbold;
		var _cstring = _red + _fnormal;
		var _cnumber = _teal + _fnormal;
		var _carray = _teal + _fbold;
		var _cobject = _teal + _fbold;
		var getClass = function() {
			var funcNameRegex = /function (.{1,})\(/;
			var results = (funcNameRegex).exec(this.constructor.toString());
			return (results && results.length > 1) ? results[1] : "[Anonymous]";
		}
		var IllegalArgumentException = function(expectedType, receivedType) {
			var name = "IllegalArgumentException";
			var message = "Bad argument given; expected argument with type \"" + expectedType + "\" but received \"" + receivedType + "\".";
			var toString = function() {
				return '[' + name + "] " + message;
			}
			return {
				name: name, 
				level: "Show Stopper", 
				message: message, 
				toString: toString
			};
		}
		var NullPointerException = function() {
			var name = "NullPointerException";
			var message = "Null pointer argument given; received argument with value \"null\".";
			var toString = function() {
				return '[' + name + "] " + message;

			}
			return {
				name: name, 
				level: "Show Stopper", 
				message: message, 
				toString: toString
			};
		}
		var UndefinedArgumentException = function(argumentName, argumentIndex) {
			var name = "UndefinedArgumentException";
			var message = "Argument missing; expected argument \"" + name + "\" as argument number " + argumentIndex + ".";
			var toString = function() {
				return '[' + name + "] " + message;

			}
			return {
				name: name, 
				level: "Show Stopper", 
				message: message, 
				toString: toString
			};
		}
		var _errorToString = function() {
			var argarray = [null];
			var argstr = '(';
			if(this.args() && this.args().length > 0) {
				for(var i=0;i<this.args.length;i++) {
					switch(typeof(this.args[i])) {
						case "object":
							argstr+="%c{"+typeof(this.args[i])+"}%c";
							argarray.push(_cobject);
							argarray.push(_cnormal);
							break;
						case "array":
							argstr+="%c["+typeof(this.args[i])+'('+this.args[i].length+")]%c";
							argarray.push(_carray);
							argarray.push(_cnormal);
							break;
						case "string":
							argstr+='%c"'+this.args[i]+'"%c';
							argarray.push(_cstring);
							argarray.push(_cnormal);
							break;
						case "number":
							argstr+="%c"+this.args[i]+"%c";
							argarray.push(_cnumber);
							argarray.push(_cnormal);
							break;
						case "boolean":
							argstr+="%c"+this.args[i]+"%c";
							argarray.push(_cboolean);
							argarray.push(_cnormal);
							break;
						default:
							argstr+=this.args[i];
					}
					if(i<(this.args.length-1)) {
						argstr+=", ";
					}
				}
			}
			argstr+=')';
			if(typeof(this.callee()) == "undefined") {
				argarray[0] = "The assertion '"+this.assertion()+argstr+"' passed";
			} else {
				argarray[0] = "'"+this.callee()+"' failed the assertion '"+this.assertion()+argstr+"'.";
			}
			return argarray[0];
		}
		var TestError = function(assertionName, calleeName, argumentObject) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("assertionName", 1);
			if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) == "undefined") throw new UndefinedArgumentException("calleeName", 2);
			if(typeof(arguments[1]) != "string") throw new IllegalArgumentException("string", typeof(arguments[1]));
			if(arguments[1] == null) throw new NullPointerException();
			if(typeof(arguments[2]) != "undefined" && typeof(arguments[2]) != "object") throw new IllegalArgumentException("object", typeof(arguments[2]));

			var _assertion = assertionName;
			var _callee = calleeName;
			var _args = arguments;

			var assertion = function() {return _assertion;};
			var callee = function() {return _callee;};
			var args = function() {return _args;};
			var toString = function() {
				return _errorToString.apply({assertion: assertion, callee: callee, args: args});
			}
			return {
				assertion: assertion, 
				callee: callee, 
				args: args, 
				toString: toString
			};
		}
		var _methodToString = function() {
			var str = "Method " + this.name + "()";
			if(this.passed) {
				str += " Passed";
			} else {
				str += " Failed"
			}
			this.errors.map(function(error) {
				str += "\n    " + error;
			});
			return str;
		}
		var TestMethod = function(methodFun, methodName) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("methodFun", 1);
			if(typeof(arguments[0]) != "function") throw new IllegalArgumentException("function", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) == "undefined") throw new UndefinedArgumentException("methodName", 2);
			if(typeof(arguments[1]) != "string") throw new IllegalArgumentException("string", typeof(arguments[1]));
			if(arguments[1] == null) throw new NullPointerException();
			var _method = methodFun;
			var _name = methodName;
            var _passed = true;
			var _errors = [];
			var _time = 0;
			
			var method = function() {
				return _method;
			}
			var name = function() {
				return _name;
			}
			var passed = function() {
                return _passed;
            }
			var addError = function(error) {
                _passed = false;
				_errors.push(error);
			}
			var time = function(time) {
				if(typeof(time) == "undefined") return _time;
				if(typeof(time) === 'number' && time % 1 == 0) {
					_time=time;
					return time;
				} else if(time === null) {
					throw new NullPointerException();
				} else {
					throw new IllegalArgumentException("int", typeof(time));
				}
			}
			var toString = function() {
				var error_strings = [];
				var obj;
				_errors.map(function (error) {
					error_strings.push(error.toString());
				});
				obj = {
					name: _name, 
					passed: _passed, 
					time: _time, 
					errors: error_strings
				}
				return _methodToString.apply(obj, []);
			}
			return {
				method: method, 
				name: name, 
                passed: passed, 
				addError: addError, 
				time: time, 
				toString: toString
			};
		}
		var TestEvent = function(fun) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("fun", 1);
			if(typeof(arguments[0]) != "function") throw new IllegalArgumentException("function", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			var _value = Object();
			var onchange = fun;

			var passed = function() {
				_value = true;
				onchange();
			}
			var failed  = function() {
				_value = false;
				onchange();
			}
			var value = function() {
				return _value;
			}
			return {
				onchange: onchange, 
				passed: passed, 
				failed: failed, 
				value: value
			};
		}
		var _objectToString = function() {
			var str = "Object " + this.name;
			if(this.passed) {
				str += " Passed";
			} else {
				str += " Failed"
			}
			this.methods.map(function(method) {
				str +="\n  "+method;
			});
			return str;
		}
		var TestObject = function(obj, name) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("obj", 1);
			if(typeof(arguments[0]) != "object") throw new IllegalArgumentException("object", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) != "undefined") {
				if(typeof(arguments[1]) != "string") throw new IllegalArgumentException("string", typeof(arguments[1]));
				if(arguments[1] == null) throw new NullPointerException();
			}
			var _obj = null;
			var _name = name;
			var _methods = [];
			var _currentMethod = null;
			var _wasTimed = false;
			var _time = 0;
            var _passed = true;
                        
			var construct = function(obj) {
				_obj = obj;
				if(typeof(obj[_setUpName]) == "function") {
					_methods.push(new TestMethod(obj[_setUpName], _setUpName));
				}
				for (method in obj) {
					if(typeof(obj[method]) == "function" && method.length >= _methodSuffix.length && method.substr(-_methodSuffix.length) == _methodSuffix) {
						_methods.push(new TestMethod(obj[method], method));
					}
				}
				if(typeof(obj[_tearDownName]) == "function") {
					_methods.push(new TestMethod(obj[_tearDownName], _tearDownName));
				}
				if(typeof(_name) == "undefined") {
					_name = getClass.apply(_obj,[]);
				}
			}
			var test = function(runTest) {
				if(typeof(arguments[0]) != "undefined") {
					if(typeof(arguments[0]) != "boolean") throw new IllegalArgumentException("boolean", typeof(arguments[1]));
					if(arguments[0] == null) throw new NullPointerException();
				}
				var time;
				var totalTime = 0;
				if(typeof(runTest) == "undefined") runTest = true;
				_methods.map(function(method) {
					_currentMethod = method;
					time = new Date().getTime();
					_currentMethod.method().apply(_obj, []);
					time = new Date().getTime() - time;
					_currentMethod.time(time);
					totalTime+=time;
				});
				_time=totalTime;
                return _passed;
			}
			var name = function() {
				return _name;
			}
            var passed = function() {
                return _passed;
            }
			var currentMethodName = function() {
				if(typeof(_currentMethod) != "undefined" && _currentMethod != null) {
					return _currentMethod.name();
				} else {
					return null;
				}
			}
			var addError = function(error, method_name) {
				if(typeof(arguments[0]) != "object") throw new IllegalArgumentException("object", typeof(arguments[0]));
				if(arguments[0] == null) throw new NullPointerException();
				if(typeof(arguments[1]) != "undefined") {
					if(typeof(arguments[1]) != "boolean") throw new IllegalArgumentException("boolean", typeof(arguments[1]));
					if(arguments[1] == null) throw new NullPointerException();
				}
				var returnvar = false;
				if(typeof(method_name) == "undefined" || method_name == currentMethodName()) {
					_currentMethod.addError(error)
                    _passed = false;
					returnvar = true;
				} else {
					_methods.map(function(method) {
						if(method.name() == method_name) {
							method.addError(error);
                                                        _passed = false;
							returnvar = true;
						}
					});
				}
				return returnvar;
			}
			var toString = function() {
				var method_strings = [];
				var obj;
				_methods.map(function (method) {
					method_strings.push(method.toString());
				});
				obj = {
					name: _name, 
					passed: _passed, 
					time: _time, 
					methods: method_strings
				}
				return _objectToString.apply(obj, []);
			}
			construct(obj);
			return {
				test: test, 
				name: name, 
                passed: passed, 
				currentMethodName: currentMethodName, 
				addError: addError, 
				toString: toString
			};
		}
		var functionSuffix = function(suffix) {
			if(typeof(arguments[0]) != "undefined") {
				if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
				if(arguments[0] == null) throw new NullPointerException();
			}
			if(typeof(suffix) == "string") {
				_functionSuffix = suffix;
			}
			return _functionSuffix;
		}
		var classSuffix = function(suffix) {
			if(typeof(arguments[0]) != "undefined") {
				if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
				if(arguments[0] == null) throw new NullPointerException();
			}
			if(typeof(suffix) == "string") {
				_classSuffix = suffix;
			}
			return _classSuffix;	
		}
		var methodSuffix = function(suffix) {
			if(typeof(arguments[0]) != "undefined") {
				if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
				if(arguments[0] == null) throw new NullPointerException();
			}
			if(typeof(suffix) == "string") {
				_methodSuffix = suffix;
			}
			return _methodSuffix;	
		}
		var setUpName = function(name) {
			if(typeof(arguments[0]) != "undefined") {
				if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
				if(arguments[0] == null) throw new NullPointerException();
			}
			if(typeof(name) == "string") {
				_setUpName = name;
			}
			return _setUpName;	
		}
		var tearDownName = function(name) {
			if(typeof(arguments[0]) != "undefined") {
				if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
				if(arguments[0] == null) throw new NullPointerException();
			}
			if(typeof(name) == "string") {
				_tearDownName = name;
			}
			return _tearDownName;	
		}
		var addObject = function(obj) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("obj", 1);
			if(typeof(arguments[0]) != "object") throw new IllegalArgumentException("object", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			_objects.push(new TestObject(obj));
		}
		var addObject = function(obj) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("obj", 1);
			if(typeof(arguments[0]) != "object") throw new IllegalArgumentException("object", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			_objects.push(new TestObject(obj));
		}
		var _assertionPassed = function(assertion, callee, args) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("assertion", 1);
			if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) == "undefined") throw new UndefinedArgumentException("callee", 2);
			if(typeof(arguments[1]) != "string") throw new IllegalArgumentException("string", typeof(arguments[1]));
			if(arguments[1] == null) throw new NullPointerException();
			if(typeof(arguments[2]) == "undefined") throw new UndefinedArgumentException("args", 3);
			if(typeof(arguments[2]) != "object") throw new IllegalArgumentException("object", typeof(arguments[1]));
			if(arguments[2] == null) throw new NullPointerException();
		}
		var _assertionFailed = function(assertion, callee, args) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("assertion", 1);
			if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) == "undefined") throw new UndefinedArgumentException("callee", 2);
			if(typeof(arguments[1]) != "string") throw new IllegalArgumentException("string", typeof(arguments[1]));
			if(arguments[1] == null) throw new NullPointerException();
			if(typeof(arguments[2]) == "undefined") throw new UndefinedArgumentException("args", 3);
			if(typeof(arguments[2]) != "object") throw new IllegalArgumentException("object", typeof(arguments[1]));
			if(arguments[2] == null) throw new NullPointerException();
			if(this == null) return;
			this.addError(new TestError(assertion, callee, args));
		}
		var addAssertion = function(name, fun) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("name", 1);
			if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) == "undefined") throw new UndefinedArgumentException("fun", 2);
			if(typeof(arguments[1]) != "function") throw new IllegalArgumentException("function", typeof(arguments[1]));
			if(arguments[1] == null) throw new NullPointerException();
			var assertFun = function() {
				var callee;
				var current = null;
				var args = arguments;
				var passed = fun.apply(null, arguments);
				if(typeof(arguments.callee.caller.name) != "undefined" && arguments.callee.caller.name != "") {
					if(typeof(_currentFunction) != "undefined" && _currentFunction.name() == arguments.callee.caller) {
						callee = _currentFunction.name();
						current = _currentFunction;
					} else {
						callee = arguments.callee.caller.name;
					}
				} else {
					if(_currentObject != null) {
						callee = _currentObject .currentMethodName();
						current = _currentObject ;
					} else {
						callee = _currentFunction.name();
					}
				}
				if(passed === true) {
					_assertionPassed.apply(current, [name, callee, args]);
				} else if(passed === false) {
					_assertionFailed.apply(current, [name, callee, args]);
				}
			};
			this[name] = assertFun;
		}

		var addEvent = function(name, fun) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("name", 1);
			if(typeof(arguments[0]) != "string") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(arguments[1]) == "undefined") throw new UndefinedArgumentException("fun", 2);
			if(typeof(arguments[1]) != "function") throw new IllegalArgumentException("function", typeof(arguments[1]));
			if(arguments[1] == null) throw new NullPointerException();
			var callee;
			var current = null;
			var eventFun = function() {
				var args = arguments;
					if(typeof(arguments.callee.caller.name) != "undefined" && arguments.callee.caller.name != "") {
					if(typeof(_currentFunction) && _currentFunction.name() != "undefined" == arguments.callee.caller) {
						callee = _currentFunction.name();
						current = _currentFunction;
					} else {
						callee = arguments.callee.caller.name;
					}
				} else {
					if(_currentObject != null) {
						callee = _currentObject.currentMethodName();
						current = _currentObject;
					} else {
						callee = _currentFunction.name();
					}
				}
				var eventListener = new TestEvent(function() {
					if(eventListener.value() === true) {
						_assertionPassed.apply(current, [name, callee, args]);
					} else if(eventListener.value() === false) {
						_assertionFailed.apply(current, [name, callee, args]);
					}
				});
				fun.apply(eventListener, arguments);
			}
			this[name] = eventFun;
		}
		var test = function(runTest) {
			if(typeof(arguments[0]) != "undefined") {
				if(typeof(arguments[0]) != "boolean") throw new IllegalArgumentException("boolean", typeof(arguments[0]));
			}
			if(typeof(runTest) == "undefined") runTest = true;
			_objects.map(function(obj) {
				_currentObject = obj;
				if(obj.test(runTest)) {
                    _passedCount++;
                } else {
                    _failedCount++;
                }
			});
			_currentObject = null;
			_functions.map(function(fun) {
				_currentFunction = fun;
				if(fun.test(runTest)) {
                    _passedCount++;
                } else {
                    _failedCount++;
                }
			});
			_currentFunction = null;
		}
		var toString = function() {
			str = "";
			_objects.map(function(object) {
				str += object.toString();
			});
			_functions.map(function(fun) {
				str += fun.toString();
			});
			return str;
		}
		var errorToString = function(fun) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("fun", 1);
			if(typeof(arguments[0]) != "function") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(fun) == "function") {
				_errorToString = fun;
			} else {
				throw new IllegalArgumentException("function", typeof(fun));
			}
		}
		var methodToString = function(fun) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("fun", 1);
			if(typeof(arguments[0]) != "function") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(fun) == "function") {
				_methodToString = fun;
			} else {
				throw new IllegalArgumentException("function", typeof(fun));
			}
		}
		var objectToString = function(fun) {
			if(typeof(arguments[0]) == "undefined") throw new UndefinedArgumentException("fun", 1);
			if(typeof(arguments[0]) != "function") throw new IllegalArgumentException("string", typeof(arguments[0]));
			if(arguments[0] == null) throw new NullPointerException();
			if(typeof(fun) == "function") {
				_objectToString = fun;
			} else {
				throw new IllegalArgumentException("function", typeof(fun));
			}
		}
		return {
			functionSuffix: functionSuffix, 
			classSuffix: classSuffix, 
			methodSuffix: methodSuffix, 
			setUpName: setUpName, 
			tearDownName: tearDownName, 
			addAssertion: addAssertion, 
			addEvent: addEvent, 
			addObject: addObject, 
			test: test, 
			toString: toString
		}
	})();
}