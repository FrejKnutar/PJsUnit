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

		var _errorToString = function() {
			var argarray = [null];
			var argstr = '(';
			console.log(this);
			if(this.args() && this.args().length > 0) {
				for(var i=0;i<this.args.length;i++) {
					echo(this.args[i]);
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
				argarray[0] = ("The assertion '"+this.assertion()+argstr+"' passed");
			} else {
				argarray[0] = ("'"+this.callee()+"' failed the assertion '"+this.assertion()+argstr+"'.");
			}
			echo.apply(null, argarray);
		}

		var TestError = function(assertion, callee, args) {
			var _assertion = assertion;
			var _callee = callee;
			var _args = args;

			var getAssertion = function() {return _assertion;};
			var getCallee = function() {return _callee;};
			var getArgs = function() {return _args;};
			var toString = function() {
				return _errorToString.apply({assertion: getAssertion, callee: getCallee, args: getArgs});
			}
			return {
				assertion: getAssertion,
				callee: getCallee,
				args: getArgs,
				toString: toString
			};
		}
		var TestMethod = function(method, name) {
			var _method = method;
			var _name = name;
                        var _passed = true;
			var _errors = [];
			var _time = 0;
			
			var getMethod = function() {
				return _method;
			}

			var getName = function() {
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
				}
				throw "Bad argument. integer expected but \"" + typeof(time) + "\" given ";
			}
			var toString = function() {
				var error_strings = [];
				_errors.map(function (error) {
					error_string.push(error.toString);
				});
				return _method_toString.apply(this,[_name,error_strings]);
			}
			return {
				method: getMethod,
				name: getName,
                                passed: passed,
				addError: addError,
				time: time,
				toString: toString
			};
		}

		var TestEvent = function(fun) {
			if(typeof(fun) != "function") {
				throw "Function Event requires the parameter to be a function";
			}
			var _passed = Object();
			var _onchange = fun;

			var passed = function() {
				_passed = true;
				_onchange();
			}
			var failed  = function() {
				_passed = false;
				_onchange();
			}
			var value = function() {
				return _passed;
			}
			return {
				failed: failed,
				passed: passed,
				onchange: _onchange,
				value: value
			};
		}

		var TestObject = function(obj,name) {
			var _name = name;
			var _methods = [];
			var _obj = null;
			var _currentMethod = null;
			var _wasTimed = false;
			var _time = 0;
                        var _passed = true;
                        
			var construct = function(obj) {
				_obj = obj;
				if(typeof(obj[_setUpName]) == "function") {
					_methods.push(new TestMethod(obj[_setUpName],_setUpName));
				}
				for (method in obj) {
					if(typeof(obj[method]) == "function" && method.length >= _methodSuffix.length && method.substr(-_methodSuffix.length) == _methodSuffix) {
						_methods.push(new TestMethod(obj[method],method));
					}
				}
				if(typeof(obj[_tearDownName]) == "function") {
					_methods.push(new TestMethod(obj[_tearDownName],_tearDownName));
				}
			}
			var test = function(runTest) {
				var time;
				var totalTime = 0;
				if(typeof(runTest) == "undefined") runTest = true;
				_methods.map(function(method) {
					_currentMethod = method;
					time = new Date().getTime();
					_currentMethod.method().apply(_obj,[]);
					//eval("_obj" + _currentMethod.name() + "();");
					time = new Date().getTime() - time;
					_currentMethod.time(time);
					totalTime+=time;
				});
				_time=totalTime;
                                return _passed;
			}
			var getName = function() {
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
			var addError = function(error,method_name) {
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
			construct(obj);
			return {
				test: test,
				name: getName,
                                passed: passed,
				currentMethodName: currentMethodName,
				addError: addError
			};
		}

		var time = 0;
		var functionSuffix = function(suffix) {
			if(typeof(suffix) == "string") {
				_functionSuffix = suffix;
			}
			return _functionSuffix;
		}
		var classSuffix = function(suffix) {
			if(typeof(suffix) == "string") {
				_classSuffix = suffix;
			}
			return _classSuffix;	
		}
		var methodSuffix = function(suffix) {
			if(typeof(suffix) == "string") {
				_methodSuffix = suffix;
			}
			return _methodSuffix;	
		}
		var setUpName = function(name) {
			if(typeof(name) == "string") {
				_setUpName = name;
			}
			return _setUpName;	
		}
		var tearDownName = function(name) {
			if(typeof(name) == "string") {
				_tearDownName = name;
			}
			return _tearDownName;	
		}
		var addObject = function(obj) {
			_objects.push(new TestObject(obj));
		}

		var assertionPassed = function(assertion, callee, args) {
			var argarray = [null];
			var argstr = '(';
			if(args.length > 0) {
				for(var i=0;i<args.length;i++) {
					switch(typeof(args[i])) {
						case "object":
							argstr+="%c{"+typeof(args[i])+"}%c";
							argarray.push(_cobject);
							argarray.push(_cnormal);
							break;
						case "array":
							argstr+="%c["+typeof(args[i])+'('+args[i].length+")]%c";
							argarray.push(_carray);
							argarray.push(_cnormal);
							break;
						case "string":
							argstr+='%c"'+args[i]+'"%c';
							argarray.push(_cstring);
							argarray.push(_cnormal);
							break;
						case "number":
							argstr+="%c"+args[i]+"%c";
							argarray.push(_cnumber);
							argarray.push(_cnormal);
							break;
						case "boolean":
							argstr+="%c"+args[i]+"%c";
							argarray.push(_cboolean);
							argarray.push(_cnormal);
							break;
						default:
							argstr+=args[i];
					}
					if(i<(args.length-1)) {
						argstr+=", ";
					}
				}
			}
			argstr+=')';
			if(typeof(callee) == "undefined") {
				argarray[0] = ("The assertion '"+assertion+argstr+"' passed");
			} else {
				argarray[0] = ("'"+callee+"' passed the assertion '"+assertion+argstr+"'.");
			}
			echo.apply(null, argarray);
		}
		var assertionFailed = function(assertion, callee, args) {
			if(this == null) return;
			this.addError(new TestError(assertion,callee,args));
		}
		var addAssertion = function(name,fun) {
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
					assertionPassed.apply(current, [name, callee, args]);
				} else if(passed === false) {
					assertionFailed.apply(current, [name, callee, args]);
				}
			};
			this[name] = assertFun;
		}

		var addEvent = function(name,fun) {
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
						assertionPassed.apply(current, [name, callee, args]);
					} else if(eventListener.value() === false) {
						assertionFailed.apply(current, [name, callee, args]);
					}
				});
			fun.apply(eventListener, arguments);
			}
		this[name] = eventFun;
		}
		var test = function(runTest) {
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
		return {
			functionSuffix: functionSuffix,
			classSuffix: classSuffix,
			methodSuffix: methodSuffix,
			setUpName: setUpName,
			tearDownName: tearDownName,
			addAssertion: addAssertion,
			addEvent: addEvent,
			addObject: addObject,
			test: test
		}
	})();
}
PJsUnit.addAssertion("assertTrue",function(b) {return b===true;});
var fun = function(url,status) {
	var e = this;
	var xhr;
	xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if(xhr.status == status) {
			e.passed();
		}
	};
	xhr.open("GET",url,true);
}
PJsUnit.addEvent("xhrStatus",fun);

function Test_Class() {
	var send;
	this.set_up = function() {
		xhr = new XMLHttpRequest();
		console.log("Set Up");
	}
	this.tear_down = function() {
		console.log("Tear Down");
	}
	this.method_test = function() {
		PJsUnit.xhrStatus("file:///home/frej/linux/WEBSCR/index.html",0);
		PJsUnit.assertTrue(false);
	}
}

var obj = new Test_Class();
PJsUnit.addObject(obj);
PJsUnit.test();