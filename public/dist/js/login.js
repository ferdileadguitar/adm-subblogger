webpackJsonp([1],{

/***/ 10:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 15:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(1);
module.exports = __webpack_require__(7);


/***/ }),

/***/ 7:
/***/ (function(module, exports, __webpack_require__) {

// SCSS
// ------------------------------------------------------------------------

__webpack_require__(10);

// APP
// ------------------------------------------------------------------------

new Promise(function(resolve) { resolve(); }).then(function() { var __WEBPACK_AMD_REQUIRE_ARRAY__ = [__webpack_require__(1), __webpack_require__(2)]; (function(MainApp, joii) {
	'use strict';

	var App = joii.Class({ extends: MainApp }, {
		init: function() {
			var self = this;

			this.application.controller('login-controller', function($scope, $attrs, $http) {
				$scope.error      = void 0;
				$scope.processing = false;
				$scope.username   = '';
				$scope.password   = '';
				$scope.$form      = $attrs.$$element.find('form');

				$attrs.$$element.on('submit', 'form', function() {
					$scope.error      = void 0;
					$scope.processing = true;

					$scope.tryLogin();
					return false;
				});

				// ------------------------------------------------------------------------
				
				// Try login
				$scope.tryLogin = function() {
					$http.post(self.baseURL + 'login', {'username': $scope.username, 'password': $scope.password})
						 .then(function(response) {
						 	var redirect = response.data.url || self.baseURL;
						 	window.location = redirect;
						 }, function() {
							$scope.error      = 'Invalid Username or Password';
							$scope.processing = false;
						 });
				};

				// Submit button click listener
				$scope.submit = function() {
					$scope.$form.submit();
				}
			});

			// Startup module
			// ------------------------------------------------------------------------
			
			this.application.run();
			angular.bootstrap(document.querySelector("html"), ["keepoApp"]);
		}
	});

	// ------------------------------------------------------------------------
	
	var loginApp = new App;
	loginApp.init();
}.apply(null, __WEBPACK_AMD_REQUIRE_ARRAY__));}).catch(__webpack_require__.oe);



/***/ })

},[15]);