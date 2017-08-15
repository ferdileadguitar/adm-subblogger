// SCSS
// ------------------------------------------------------------------------

// import { joii } from 'joii';
// import $ from 'jQuery';
// import { underscore } from 'underscore';
// import { angular } from 'angular';
// import {angular-sanitize 
// APP
// ------------------------------------------------------------------------

require(['joii', 'jquery', 'underscore', 'angular', 'angular-sanitize'], function(joii) {
	'use strict';

	var MainApp = joii.Class({
		baseURL: void 0,
		application: void 0,

		__construct: function() {
			this.baseURL = window.baseURL;
			this.application = angular.module('keepoApp', ['ngSanitize'], function($interpolateProvider) {
		        $interpolateProvider.startSymbol('<@');
		        $interpolateProvider.endSymbol('@>');
		    });

		    this.application.config([
		    	'$compileProvider', '$httpProvider',
		    	function($compileProvider, $httpProvider) {
			    	// whitelist href content
                    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|javascript):/);
                    // disable debug info
                    $compileProvider.debugInfoEnabled(false);

                    $httpProvider.defaults.headers.common = {
		        		'X-Requested-With': 'XMLHttpRequest'
		        	};
			    }
			]);

			// Listeners / Services
			// ------------------------------------------------------------------------
			
			this.appService();

			// Search bar
			if ($('.search').length) { this.evSearch(); }

			// Dropdown
			if ($('.drop').length) { this.evDropdowns(); }

			// Tabs
			if ($('.tabs').length) { this.evTabs(); }
		},

		appService: function() {
			this.application.service('appService', ['$http', '$q', '$compile', function ($http, $q, $compile) {
				return {
					url: void 0,
					tabService: void 0,
					appContaxt: void 0,
					localContext: void 0,

					sharedData: {},

					apply: apply,
					join: join,	

					request: request,				
					get: get,
					post: post,
					put: put,
					delete: remove,
					cancel: cancel,

					modal: modal,
					modalEditor: modalEditor
				};

				// ------------------------------------------------------------------------
				// Local Methods
				// ------------------------------------------------------------------------
				
				function apply() {
					for(var i = 0; i < arguments.length; i++) {
						if (typeof arguments[i] == 'object') {
							Object.assign(this, arguments[i])
						}
					}
				};

				function join(args, joinWith) {
					var self = this,
						arr = [],
						joinWith = joinWith || '&';

					_.map(args, function(val, key) {
						arr.push((typeof val == 'object' ? self.join(val, joinWith) : (key + '=' + val)));
					});

					return arr.join(joinWith);
				};

				// ------------------------------------------------------------------------
				
				function request(method, args) {
					var method = method || 'get',
						params = [],
						url    = this.url,
						httpTimeout, request, promise;

					// ------------------------------------------------------------------------
					
					for(var i = 0; i < args.length; i++) {
						if (! _.isUndefined(args[i].url)) {
							url = this.url + args[i].url;
							continue;
						}

						params.push(this.join(args[i]));
					}

					httpTimeout = $q.defer();
					request 	= $http({
						method: method,
						url: url + (!_.isEmpty(params) ? '?' : '') + params.join('&'),
						timeout: httpTimeout.promise
					});

					promise = request.then(unwrapResolve);
					promise._httpTimeout = httpTimeout;

					return promise;
				};

				function get() {
					return this.request('get', arguments);
				};

				function post() {
					return this.request('post', arguments);
				};

				function put() {
					return this.request('put', arguments);
				};

				function remove() {
					return this.request('delete', arguments);
				};

				function cancel(promise) {
					if (promise &&
						promise._httpTimeout &&
						promise._httpTimeout.resolve
					) {
						promise.cancelled = true;
						promise._httpTimeout.resolve();
					}
				};

				function unwrapResolve(response) {
					return response.data;
				};

				// ------------------------------------------------------------------------
				
				function modal(scope, params) {
					scope               = scope.$new(true);
					Object.assign(scope, params);
					
					angular.element('body').append($compile('<modal></modal>')(scope));
				}

				function modalEditor(scope, params) {
					scope               = scope.$new(true);
					Object.assign(scope, params);
					
					angular.element('body').append($compile('<modal-editor></modal-editor>')(scope));	
				}
			}]);
		},

		evSearch: function() {
			// Service
			// ------------------------------------------------------------------------
			
			this.application.service('searchService', ['appService', function(appService) {
				return {
					submit: function($scope, $attrs, $event) {
						appService.localContext['search']($scope.searchInput);
					}
				};
			}]);

			// Controller
			// ------------------------------------------------------------------------
			
			this.application.controller('search', ['$scope', '$attrs', 'searchService', function($scope, $attrs, searchService) {
				$scope.submit = function($event) {
					$event.preventDefault();

					// Just call search method
					searchService.submit($scope, $attrs, $event);

					return false;
				};
			}]);
		},

		evDropdowns: function() {
			// Service
			// ------------------------------------------------------------------------
			
			this.application.service('dropdownService', ['appService', function(appService) {
				return {
					select: function($scope, $attrs, $event, selected) {
						var $el = $($event.currentTarget) || $($event.srcElement);

						// Close list
						$scope.openList = false;

						// Is single action?
						if ($attrs.singleaction) {
							appService.localContext[$attrs.singleaction](selected);
							return;
						}

						// Change text and push to dropdown data
						$attrs.$$element.find('.drop-component-text').text($el.text());
						appService.localContext.dropdownAction($attrs.name, selected);

						//_.mapObject($rootScope.dropdowns, function(value, key) {
						//	console.log(key, value);
						//});
					},

					selectDate: function($scope, $attrs, $event) {
						if (! $scope.startDate || ! $scope.endDate)
						{ return false; }

						if ($scope.startDate.getTime() > $scope.endDate.getTime())
						{ return false; }

						// Close list
						$scope.openList = false;

						// Change text and push to dropdown data
						$attrs.$$element
							  .find('.drop-component-text')
							  .text(this.formatDate($scope.startDate, ' ', true) + ' - ' + this.formatDate($scope.endDate, ' ', true));

						appService.localContext.dropdownAction($attrs.name, {
							'startDate': this.formatDate($scope.startDate, '-', false, true),
							'endDate': this.formatDate($scope.endDate, '-', false, true)
						});
					},

					formatDate: function(date, separator, useMonthName, mySQLFormat) {
						var separator  	 = separator || '-',
							monthNames 	 = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
						
						return ! mySQLFormat ? 
							[date.getDate(), (useMonthName ? monthNames[date.getMonth()] : (date.getMonth() + 1)), date.getFullYear()].join(separator) :
							[date.getFullYear(), date.getMonth() + 1, date.getDate()].join(separator);
					}
				};
			}]);

			// Controller
			// ------------------------------------------------------------------------
			
			this.application.controller('dropdown', ['$scope', '$attrs', 'dropdownService', function($scope, $attrs, dropdownService) {
				$scope.openList = false;

				$scope.select = function($event, selected) {
					dropdownService.select($scope, $attrs, $event, selected);
				};

				$scope.selectDate = function($event) {
					dropdownService.selectDate($scope, $attrs, $event);
				};
			}]);
		},

		evTabs: function() {
			// Service
			// ------------------------------------------------------------------------
			
			this.application.service('tabService', ['appService', function(appService) {
				var tabActive   = {$$el: void 0, name: void 0},
					rootContext = void 0;

				return {
					setTabActive: function(tabObj) 
					{ tabActive   = tabObj || {$$el: void 0, name: void 0}; },

					getTabActive: function(getElement) 
					{ return tabActive; },

					openTab: function($attrs, $event, tabName) {
						var $el = $($event.currentTarget) || $($event.srcElement);

						// Already active? Meh..¯\_(ツ)_/¯
						if ($el.hasClass('active')) { return false; }

						// Close all tabs, then open the tab and change active tab nav
						this.closeAll($attrs);
						$attrs.$$element.find('#' + tabName).addClass('tab-open');
						$el.addClass('active');

						this.setTabActive({$$el: $attrs.$$element.find('#' + tabName), name: tabName});

						if (appService.appContext.onTabChange) {
							appService.appContext.onTabChange();
						}
					},

					closeAll: function($attrs) {
						$attrs.$$element.find('.tab-component').removeClass('tab-open');
						$attrs.$$element.find('.tabs-nav .box').removeClass('active');
					}
				};
			}]);

			// Controller
			// ------------------------------------------------------------------------
			
			this.application.controller('tabs', ['$scope', '$attrs', 'tabService', function($scope, $attrs, tabService) {
				$scope.openTab = function($event, tabName) {
					tabService.openTab($attrs, $event, tabName);
				};
			}]);
		}
	});

	// EXPORTS
	// ------------------------------------------------------------------------
	
	module.exports = MainApp;
});

