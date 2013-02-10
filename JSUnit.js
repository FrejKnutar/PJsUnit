if(typeof(JSUnit) == 'undefined') {
	var JSUnit = (function () {
		var echo = (console && console.log) ? console.log : print;
		var _failed_count = 0;
		var _passed_count = 0;
		var _passed = true;
		var _functions = [];
		var _classes = [];
		var _objects = [];
		var _current_function,_current_object,_current_object;
		var _function_suffix = "_test";
		var _class_suffix = "_test";
		var _method_suffix = "_test";
		var _design_prefix = "console";
		var _set_up_name = "set_up";
		var _tear_down_name = "tear_down";
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
		var _vobject = _teal + _fbold;

		var _error_to_string = function() {
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

		var Test_Error = function(assertion, callee, args) {
			var self = this;
			var _assertion = assertion;
			var _callee = callee;
			var _args = args;

			var get_assertion = function() {return _assertion;};
			var get_callee = function() {return _callee;};
			var get_args = function() {return _args;};
			var to_string = function() {
				return _error_to_string.apply({assertion: get_assertion, callee: get_callee, args: get_args});
			}
			return {
				assertion: get_assertion,
				callee: get_callee,
				args: get_args,
				toString: to_string()
			};
		}
		var Test_Method = function(method, name) {
			var _method = method;
			var _name = name;
			var _errors = [];
			var _time = 0;
			
			var get_method = function() {
				return _method;
			}

			var get_name = function() {
				return _name;
			}
			
			var add_error = function(error) {
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
			var to_string = function() {
				var error_strings = [];
				_errors.map(function (error) {
					error_string.push(error.toString);
				});
				return _method_to_string.apply(this,[_name,error_strings]);
			}
			return {
				method: get_method,
				name: get_name,
				addError: add_error,
				time: time,
				toString: to_string
			};
		}

		var Test_Event = function(fun) {
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

		var Test_Object = function(obj,name) {
			var _name = name;
			var _methods = [];
			var _obj = null;
			var _current_method = null;
			var _was_timed = false;
			var _time = 0;
			var construct = function(obj) {
				_obj = obj;
				if(typeof(obj[_set_up_name]) == "function") {
					_methods.push(new Test_Method(obj[_set_up_name],_set_up_name));
				}
				for (method in obj) {
					if(typeof(obj[method]) == "function" && method.length >= _method_suffix.length && method.substr(-_method_suffix.length) == _method_suffix) {
						_methods.push(new Test_Method(obj[method],method));
					}
				}
				if(typeof(obj[_tear_down_name]) == "function") {
					_methods.push(new Test_Method(obj[_tear_down_name],_tear_down_name));
				}
			}
			var test = function(run_test) {
				var time;
				var totalTime = 0;
				if(typeof(run_test) == "undefined") run_test = true;
				_methods.map(function(method) {
					_current_method = method;
					time = new Date().getTime();
					_current_method.method().apply(_obj,[]);
					//eval("_obj" + _current_method.name() + "();");
					time = new Date().getTime() - time;
					_current_method.time(time);
					totalTime+=time;
				});
				_time=totalTime;
			}
			var name = function() {
				return _name;
			}
			var current_method_name = function() {
				if(typeof(_current_method) != "undefined" && _current_method != null) {
					return _current_method.name();
				} else {
					return null;
				}
			}
			var add_error = function(error,method_name) {
				var returnvar = false;
				if(typeof(method_name) == "undefined" || method_name == current_method_name()) {
					_current_method.addError(error)
					returnvar = true;
				} else {
					_methods.map(function(method) {
						if(method.name() == method_name) {
							method.addError(error);
							returnvar = true;
						}
					});
				}
				return returnvar;
			}
			construct(obj);
			return {
				test: test,
				name: name,
				currentMethodName: current_method_name,
				addError: add_error
			};
		}

		var time = 0;
		var function_suffix = function(suffix) {
			if(typeof(suffix) == "string") {
				_function_suffix = suffix;
			}
			return _function_suffix;
		}
		var class_suffix = function(suffix) {
			if(typeof(suffix) == "string") {
				_class_suffix = suffix;
			}
			return _class_suffix;	
		}
		var method_suffix = function(suffix) {
			if(typeof(suffix) == "string") {
				_method_suffix = suffix;
			}
			return _method_suffix;	
		}
		var design_prefix = function(prefix) {
			if(typeof(prefix) == "string") {
				_design_prefix = prefix;
			}
			return _design_prefix;	
		}
		var set_up_name = function(name) {
			if(typeof(name) == "string") {
				_set_up_name = name;
			}
			return _set_up_name;	
		}
		var tear_down_name = function(name) {
			if(typeof(name) == "string") {
				_tear_down_name = name;
			}
			return _tear_down_name;	
		}
		var add_object = function(obj) {
			_objects.push(new Test_Object(obj));
		}

		var assertion_passed = function(assertion, callee, args) {
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
		var assertion_failed = function(assertion, callee, args) {
			if(this == null) return;
			this.addError(new Test_Error(assertion,callee,args));
		}
		var add_assertion = function(name,fun) {
			var assert_fun = function() {
				var callee;
				var current = null;
				var args = arguments;
				var passed = fun.apply(null, arguments);
				if(typeof(arguments.callee.caller.name) != "undefined" && arguments.callee.caller.name != "") {
					if(typeof(current_function) != "undefined" && current_function.name() == arguments.callee.caller) {
						callee = current_function.name();
						current = current_function;
					} else {
						callee = arguments.callee.caller.name;
					}
				} else {
					if(current_object != null) {
						callee = current_object.currentMethodName();
						current = current_object;
					} else {
						callee = current_function.name();
					}
				}
				if(passed === true) {
					assertion_passed.apply(current, [name, callee, args]);
				} else if(passed === false) {
					assertion_failed.apply(current, [name, callee, args]);
				}
			};
			eval("this."+name+'='+assert_fun);
		}

		var add_event = function(name,fun) {
			var callee;
			var current = null;
			var event_fun = function() {
				var args = arguments;
					if(typeof(arguments.callee.caller.name) != "undefined" && arguments.callee.caller.name != "") {
					if(typeof(current_function) && current_function.name() != "undefined" == arguments.callee.caller) {
						callee = current_function.name();
						current = current_function;
					} else {
						callee = arguments.callee.caller.name;
					}
				} else {
					if(current_object != null) {
						callee = current_object.currentMethodName();
						current = current_object;
					} else {
						callee = current_function.name();
					}
				}
				var eventListener = new Test_Event(function() {
					if(eventListener.value() === true) {
						assertion_passed.apply(current, [name, callee, args]);
					} else if(eventListener.value() === false) {
						assertion_failed.apply(current, [name, callee, args]);
					}
				});
			fun.apply(eventListener, arguments);
			}
		eval("this."+name+'='+event_fun);	
		}
		var test = function(run_test) {
			if(typeof(run_test) == "undefined") run_test = true;
			_objects.map(function(obj) {
				current_object = obj;
				obj.test(run_test);
			});
			current_object = null;
			_functions.map(function(fun) {
				current_function = fun;
				fun.test(run_test);
			});
			current_function = null;
		}
		return {
			functionSuffix: function_suffix,
			classSuffix: class_suffix,
			methodSuffix: method_suffix,
			designPrefix: design_prefix,
			setUpName: set_up_name,
			tearDownName: tear_down_name,
			addAssertion: add_assertion,
			addEvent: add_event,
			addObject: add_object,
			test: test
		}
	})();
}
JSUnit.addAssertion("assertTrue",function(b) {return b===true;});
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
JSUnit.addEvent("xhrStatus",fun);

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
		JSUnit.xhrStatus("file:///home/frej/linux/WEBSCR/index.html",0);
		JSUnit.assertTrue(false);
	}
}

var obj = new Test_Class();
JSUnit.addObject(obj);
JSUnit.test();