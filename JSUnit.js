function isset(a) {return typeof(a) !== 'undefined'};
function isInt(a) {return typeof(a) === 'number' && a % 1 == 0;};
function isStr(a) {return typeof(a) === 'string';};
function isBool(a) {return a === true || a === false;};
function isFun(a) {return typeof(a) === "function";};
function isObj(a) {return typeof(a) === "object";};

function echo(str) {
	if(isset(console) && isset(console.log)) {
		console.log(str);
	} else {
		print(str);
	}
}

if(typeof(JSUnit) == 'undefined') {
	var JSUnit = (function () {
		var failed_count = 0;
		var passed_count = 0;
		var passed = true;
		var functions = [];
		var classes = [];
		var objects = [];
		var current_function,current_object,current_object;
		var function_suffix = "_test";
		var class_suffix = "_test";
		var method_suffix = "_test";
		var design_prefix = "console";
		var set_up_name = "set_up";
		var tear_down_name = "tear_down";

		function Error(error) {
			var _file, _row, _line, _fun, _args, _passed, _caller, _cls, _type,  _tostr;
			function construct(error) {
				if(Array.isArray(error) && error.length >= 6) {
					if(isStr(error[0])) _file = error[0];
					if(isInt(error[1])) _row = error[1];
					if(isInt(error[2])) _line = error[2];
					if(isStr(error[3])) _fun = error[3];
					if(Array.isArray(error[4])) _args = error[4];
					if(isBool(error[5])) _passed = error[5];
					if(error.length>6) {
						if(isStr(error[0])) _caller = error[6];
						if(error.length>7) {
							if(isStr(error[0])) _cls = error[7];
							if(error.length>8) {
								if(isStr(error[0])) _type = error[8];
							}
						}
					}
				} else {
					throw "The parameter is not an Array.";
				}
				if(!isset(_file)) throw "The 1st element of the parameter is unset or not a string.";
				if(!isset(_row)) throw "The 2nd element of the parameter is unset or not an integer.";
				if(!isset(_line)) throw "The 3d element of the parameter is unset or not an integer.";
				if(!isset(_fun)) throw "The 4th element of the parameter is unset or not a string.";
				if(!isset(_args)) throw "The 5th element of the parameter is unset or not an Array.";
				if(!isset(_passed)) throw "The 6th element of the parameter is unset or not a boolean.";
			}
			construct(error);
			function toString(fun) {
				if(typeof(fun) != 'undefined') {
					tostr = fun;
				}
				return function(file,row,line,fun,args,passed,caller,cls,type) {toStr();}(_file,_row,_line,_fun,_args,_passed,_caller,_cls,_type);
			}
			this.file = function() {return _file;};
			this.row = function() {return _row;};
			this.line = function() {return _line;};
			this.fun = function() {return _fun;};
			this.cls = function() {return _cls;};
			this.type = function() {return _type;};
			this.args = function() {return _arguments;};
			this.passed = function() {return _passed;};
			this.caller = function() {return _caller;};
		}
		function Test_Method(method,name) {
			var _method = method;
			var _name = name;
			var _errors = [];
			var _time = 0;
			this.name = function() {
				return _name;
			}
			this.time = function(time) {
				if(!isset(time)) return _time;
				if(isInt(time)) {
					_time=time;
					return time;
				}
				throw "Bad argument. integer expected but \"" + typeof(time) + "\" given ";
			}
			function construct() {};
			this.add_error = function(error) {
				_errors.push(error);
			};
			construct();
		}
		function Test_Object(obj) {
			var _methods = [];
			var _obj = null;
			var _current_method = null;
			var _was_timed = false;
			var _time = 0;
			function construct(obj) {
				_obj = obj;
				if(typeof(obj[set_up_name]) == "function") {
					_methods.push(new Test_Method(obj[set_up_name],set_up_name));
				}
				for (method in obj) {
					if(typeof obj[method] == "function" && method.length >= method_suffix.length && method.substr(-method_suffix.length) == method_suffix) {
						_methods.push(new Test_Method(obj[method],method));
					}
				}
				if(typeof(obj[tear_down_name]) == "function") {
					_methods.push(new Test_Method(obj[tear_down_name],tear_down_name));
				}
			}
			this.test = function(run_test) {
				var time;
				var totalTime = 0;
				if(typeof(run_test) == "undefined") run_test = true;
				_methods.map(function(method) {
					_current_method = method;
					time = new Date().getTime();
					eval("_obj." + _current_method.name() + "();");
					time = new Date().getTime() - time;
					_current_method.time(time);
					totalTime+=time;
				});
				_time=totalTime;
			}
			this.current_method_name = function() {
				echo(_current_method);
				if(isset(_current_method) && _current_method != null) {
					return _current_method.name();
				} else {
					return null;
				}
			}
			construct(obj);
		}

		var time = 0;
		function gs_function_suffix(suffix) {
			if(typeof(suffix) == "string") {
				function_suffix = suffix;
			}
			return function_suffix;
		}
		function gs_class_suffix(suffix) {
			if(typeof(suffix) == "string") {
				class_suffix = suffix;
			}
			return class_suffix;	
		}
		function gs_method_suffix(suffix) {
			if(typeof(suffix) == "string") {
				method_suffix = suffix;
			}
			return method_suffix;	
		}
		function gs_design_prefix(prefix) {
			if(typeof(prefix) == "string") {
				design_prefix = prefix;
			}
			return design_prefix;	
		}
		function gs_set_up_name(name) {
			if(typeof(name) == "string") {
				set_up_name = name;
			}
			return set_up_name;	
		}
		function gs_tear_down_name(name) {
			if(typeof(name) == "string") {
				tear_down_name = name;
			}
			return tear_down_name;	
		}
		function gs_add_object(obj) {
			objects.push(new Test_Object(obj));
		}
		function assertion_passed(assertion,callee) {
			if(typeof(callee) == 'undefined') {
				echo("The assertion '"+assertion+"' passed");
			} else {
				echo("'"+callee+"' passed the assertion '"+assertion+"'.");
			}
		}
		function add_assertion(name,fun) {
			var assert_fun = function() {
				var str = "fun(";
				for(var i=0;i<arguments.length;i++) {
					str+=arguments[i];
					if(i<arguments.length -1 ) {
						str+=','
					}
				}
				str+=')';
				var bool = eval(str);
				var callee;
				if(typeof(arguments.callee.caller) != "undefined") {
					callee = arguments.callee.caller.name;
				}
				if(isset(callee) || callee == "" || callee == null) {
					if(current_object != null) {
						callee = current_object.current_method_name();
					} else {
						callee = current_function.name();
					}
				}
				if(bool === true) {
					assertion_passed(name,callee);
				} else if(bool === false) {
					;
				}
			};
			eval("this."+name+'='+assert_fun);
		}
		function gs_test(run_test) {
			if(!isset(run_test)) run_test = true;
			objects.map(function(obj) {
				current_object = obj;
				obj.test(run_test);
			});
			current_object = null;
			functions.map(function(fun) {
				current_function = fun;
				fun.test(run_test);
			});
			current_function = null;
		}
		return {
			functionSuffix: gs_function_suffix,
			function_suffix: gs_function_suffix,
			classSuffix: gs_class_suffix,
			class_suffix: gs_class_suffix,
			methodSuffix: gs_method_suffix,
			method_suffix: gs_method_suffix,
			designPrefix: gs_design_prefix,
			design_prefix: gs_design_prefix,
			setUpName: gs_set_up_name,
			set_up_name: gs_set_up_name,
			tearDownName: gs_tear_down_name,
			tear_down_name: gs_tear_down_name,
			addAssertion: add_assertion,
			addObject: gs_add_object,
			test: gs_test
		}
	})();
}
fun = function(b) {return b===true;};
JSUnit.addAssertion("assertTrue",fun);

function Test_Class() {
	this.set_up = function() {
		console.log("Set Up");
	}
	this.tear_down = function() {
		console.log("Tear Down");
	}
	this.method_test = function() {
		console.log("method_test");
		JSUnit.assertTrue(true);
	};
}

var obj = new Test_Class();
JSUnit.addObject(obj);
JSUnit.test();