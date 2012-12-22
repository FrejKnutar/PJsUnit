# PJSUnit Readme: PHP and JavaScript unit testing
Two libraries for easy unit testing in PHP and JavaScript.
*	**The philosihy of the libraries are:**
	- Easy to use
	- Dynamic, changeable and customizable
	- Easy to implement
## Ease of use
Functions, objects, classes and theire methdods can be set up to be tested automatically by the libraries, given that they are named a specific way.
-	**Examples of ease of use**
	- Functions, PHP classes and JavaScript objects that has a specific suffix (ends with a specific String) will be called automatically by the libraries.
### Dynamic and changeable
There are several ways of cusomizing and changing the functionality, presentation and atomization of the libraries.
*	**Examples of functionality that can be customized**
	- How functions, classes and methods should be named to be tested automatically.
	- Presentation of unit tests, such as presentation of classes, objects, functions, methods and general presentation.
	- Seperate adding of assertion methods to the libraries.
### Easy to implement
There is no need for class extending or setting up suits for testing code. The libraries need minimal code for setting up and running unit tests. No deeper knowledge of the libraries are needed to use them. Implementation is being as simple as possible. Adding a function to the library can be done by simply calling the function, given that the function call assertion methods from the library classes.
*	**Implementation**
	- There is no class that a test class needs to inherit from
		- A test class is a class with a specific suffix (see Ease of Use) and those will be tested automatically
		- set\_up/before and tear\_down/after methods will be called automatically as the first and last methods respectively.
		- Methods named with a specific suffix (see Ease of Use) will be called in between the set\_up and tear\_down methods.