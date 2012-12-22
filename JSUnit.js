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
		function assertion_passed(assertion,callee) {
			if(typeof(callee) == 'undefined') {
				print("The assertion '"+assertion+"' passed");
			} else {
				print("'"+callee+"' passed the assertion '"+assertion+"'.");
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
				if(bool === true) {
					assertion_passed(name,callee);
				} else if(bool === false) {
					;
				}
			};
			eval("this."+name+'='+assert_fun);
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
			addAssertion: add_assertion
		}
	})();
}
fun = function() {return true;};
JSUnit.addAssertion("hej",fun);
function test() {
	JSUnit.hej();
}
test();
JSUnit.hej();