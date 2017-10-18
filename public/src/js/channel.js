import { css }  from '../scss/main.scss';
import MainApp  from './app';
import joii     from 'joii';

// APP
// ------------------------------------------------------------------------
require(['./app.js', 'joii', 'angular-sanitize'], function(MainApp, joii) {
	'use strict';
	
	var App = joii.Class({ extends: MainApp }, {
		init: function() {
			var self = this;

			// Application controller
			// this.application = angular.module('keepoApp', ['ngSanitize', 'ngTagsInput']);
			// ------------------------------------------------------------------------
			this.application.controller('app-controller', ['$scope', '$attrs', '$filter', '$rootScope', 'appService', 'tabService', function($scope, $attrs, $filter, $rootScope, appService, tabService) {
				// Vars
				// ------------------------------------------------------------------------

				$scope.mainApp             = self;
				$scope.onRequest           = void 0;
				
				// Local init
				// ------------------------------------------------------------------------
				appService.apply({
					'url': self.baseURL + 'api/channel/',
					'appContext': $scope, 
					'tabService': tabService,

					'controllerData': {
						'all': { data: [] },
						'format': { data: [] }
					},

					// Data
					// ------------------------------------------------------------------------
					
					'initData': {
						onLoad: true,

						data: [],

						pageCount: 0,
						pageCurrent: 1,

						filters: {
							dateRange: 'all-time',
							search: ''
						},

						sort: {
							key: '',
							reverse: false
						},
					}
				});
				tabService.setTabActive({$$el: $('all'), name: 'all'});

				// ------------------------------------------------------------------------
				$scope.init = function(data) {
					Object.assign(appService.appContext, data);
				};

				$scope.onTabChange = function() {
					$scope.$broadcast(tabService.getTabActive().name + 'OnTabChange');
				};

				// Local methods
				// ------------------------------------------------------------------------
				
				function setOtherCtrlData(channel, controller, data, remove) {
					var otherCtrl = void 0;

					if (remove) {
						['all', 'moderated', 'contributor'].forEach(function(ctrl) {
							appService.controllerData[ctrl].data = _.without(
								appService.controllerData[ctrl].data,
								_.findWhere(appService.controllerData[ctrl].data, {'id': channel.id})
							);
						});
						return;
					}

					// ------------------------------------------------------------------------

					switch (controller) {
						case 'all':
							otherCtrl = ['format'];
							break;
						case 'format':
							otherCtrl = ['all'];
							break;
					}

					otherCtrl.forEach(function(ctrl) {
						var otherCtrlData = _.findWhere(appService.controllerData[ctrl].data, {'id': channel.id});
						if (otherCtrlData) { Object.assign(otherCtrlData, data); }
					});
					Object.assign(channel, data);
				};

			}]).
			controller('allController', ['$scope', '$attrs', '$rootScope', '$filter', 'appService', self.allController]).
			controller('formatController', ['$scope', '$attrs', '$rootScope', '$filter', 'appService', self.formatController]);

			// Directive
			// ------------------------------------------------------------------------
			
			this.directive();

			// Startup module
			// ------------------------------------------------------------------------
			
			this.application.run(function($rootScope, $templateCache) {
				// Implement underscore to rootScope
		        $rootScope._ = _;
			});
			angular.bootstrap(document.querySelector("html"), ["keepoApp"]);
		},

		// Test directives
		directive: function() {
			this.application.directive('feeds', ['$compile', '$rootScope', '$window', '$filter', '$timeout', 'appService', function($compile, $rootScope, $window, $filter, $timeout, appService) {
				return {
					restrict: 'E',
					replace: true,
                    templateUrl: 'feedListTemplate',
                    link: function($scope, $elements, $attrs) {

                    	$scope.convertTags = function(tags) {
                    		return (tags && tags.length) ? _.map(tags, function(tag) { return tag.title; }).join(', ') : '<em>No tag available</em>';
                    	};

                    	$scope.onCheck = function(channel) {
							channel.checked = !channel.checked;
						};

						$scope.setPassword = function(channel, index) {
							$scope.setEditor(channel, index, 'set-password');
						};

						$scope.delete = function(channel) {
							appService.modal($scope, {
								data: channel, 
								type: 'delete',
								text: 'Are you sure to delete <strong>' + channel.username + '</strong>?',
								okCallback: function(scope) {
									appService.delete({'url': 'delete-users'}, {id: channel.id})
									.then(
										function SuccessCallback(res) {	
											var newData = appService.localContext.data;
											
											appService.localContext.data = _.without(newData, _.find(newData, channel));
											
											$scope.createLabelCount(res);
											// console.log( $scope )
										},

										function ErrorCallback(err){
											appService.modal($scope, {
												type         : 'text',
												text         : '<h3>Whoops... something went wrong !</h3><h5>' + err.data.error_description + '</h5>',
												cancelText   : 'Ok',
												singleButton : true
											});
										}
									);

									scope.close();
								}
							});
						};

						$scope.setEditor   = function(channel, index, type) {
							appService.modalEditor($scope, {
								ids  : index,
								data : channel,
								type : type
							});

							$scope.$broadcast('mdl_data', { allchannels : $scope.data, channels : channel, type});
							
							// This use for all directives
							$rootScope.allchannels = _.extend($scope, { channel : channel, all : $scope.data });
						};

						$scope.parseFeedsLink = function(channel) {
							$window.open(channel.url, '_blank');
						}
                    }
				};
			}]);

			this.application.directive('tab', ['$compile', function($compile) {
				return {
					restrict: 'E',
					replace: true,
					templateUrl: 'tabTemplate',
				};
			}]);

			this.application.directive('modal', ['appService', function(appService) {
				return {
					restrict: 'E',
					replace: true,
					templateUrl: 'modalTemplate',
					link: function($scope, $element) {
						$scope.callback = function(callback) {
							if (typeof callback == 'function') 
							{ return callback($scope); }

							if (typeof callback == 'string') 
							{ return $scope[callback](); }

							console.log('Undefined Callback');
						};

						// ------------------------------------------------------------------------

						$scope.ok = function() {
							$scope.close();
						};

						$scope.close = function() {
							$scope.$destroy();
						};

						// On
						// ------------------------------------------------------------------------
						
						$scope.$on('$destroy', function() {
							$element.remove();
						});
					}
				};
			}]);

			this.application.directive('textContent', ['$sce', function($sce) {
			    return {
				    restrict: 'AC', // only activate on element attribute
				    require: '?ngModel', // get a hold of NgModelController
				    link: function(scope, element, attrs, ngModel) {
				      	if (!ngModel) return; // do nothing if no ng-model

				      	// Specify how UI should be updated
				      	ngModel.$render = function() {
				        	element.html($sce.getTrustedHtml(ngModel.$viewValue || ''));
					      	read(); // initialize
				      	};

				      	// Listen for change events to enable binding
				      	element.on('keyup change', function() {
				        	scope.$evalAsync(read);
				      	});

				      	// element.on('click', function() {
				      	// 	var $focus = $(this).parents('.eb-quiz-list').find('[data-content="editable-text"]');

				      	// 	// clear hasClass focus
				      	// 	__clearOnFocus($focus);

				      	// 	// addClass focus to existance element
				      	// 	$(this).parent().toggleClass('focus');
				      	// }).on('blur', function() {
				      	// 	var $focus = $(this).parents('.eb-quiz-list').find('[data-content="editable-text"]');

				      	// 	// clear hasClass focus
				      	// 	__clearOnFocus($focus);
				      	// }).on('focusin', function(){
				      	// 	$(this).parents('.content-header, .content-desc, .qs-content').toggleClass('focus');
				      	// }).on('paste', function(e){
				      	// 	e.preventDefault();

					      //   var text;
					      //   var clp = (e.originalEvent || e).clipboardData;
					      //   if (clp === undefined || clp === null) {
					      //       text = window.clipboardData.getData("text") || "";
					      //       if (text !== "") {
					      //           if (window.getSelection) {
					      //               var newNode = document.createElement("span");
					      //               newNode.innerHTML = text;
					      //               window.getSelection().getRangeAt(0).insertNode(newNode);
					      //           } else {
					      //               document.selection.createRange().pasteHTML(text);
					      //           }
					      //       }
					      //   } else {
					      //       text = clp.getData('text/plain') || "";
					      //       if (text !== "") {
					      //           document.execCommand('insertText', false, text);
					      //       }
					      //   }
				      	// });

				      	function __clearOnFocus(element) {
				      		angular.forEach(element, function(value, key) {
				      			var $el = angular.element(value);

				      			if( $el.hasClass('focus') ) { $el.removeClass('focus') }
				      		});

				      		// // set active layout
				      	}
				      	// Write data to the model
				      	function read() {
				        	var html = element.html();
				        	// When we clear the content editable the browser leaves a <br> behind
				        	// If strip-br attribute is provided then we strip this out
				        	if (attrs.stripBr && html === '<br>' && html === '<div>') {
				          		html = '';
				        	}
				        	ngModel.$setViewValue(html);
				      	}
				    }
				};
			}]);

			// Editors Directive
			// this.application.directive('editorPassword', passwordDirective);
		},

		allController: function($scope, $attrs, $rootScope, $filter, appService) {

			return (function() {
				Object.assign($scope, angular.copy(appService.initData));
				// Init
				// ------------------------------------------------------------------------
				
				appService.controllerData['all'] = $scope;
				$scope.controller       = 'all';
				$scope.onRequest        = void 0;
				$scope.createLabelCount = createLabelCount;
				console.log( appService );
				request();
				
				// Methods
				// ------------------------------------------------------------------------
				
				$scope.search = function(searchInput) {
					$scope.filters.search = searchInput || ''; 
					$scope.pageCurrent    = 1;

					console.log($scope.filters)
					
					request();
				};

				// $scope.bulkAction = function(selected) {
				// 	appService.appContext.bulkAction(
				// 		selected, 
				// 		_.where($scope.data, 
				// 			{'checked': true}).map(function(item) { 
				// 				return {id: item.id, status: item.status}; 
				// 			}
				// 		),
				// 		$scope
				// 	);
				// };

				$scope.dropdownAction = function(name, selected) {
					$scope.filters[name.replace(/^filter-/, '')] = selected;

					appService.url          = self.baseURL + 'api/channel/';
					request();
				};

				$scope.onSort = function(sortBy) { 
					$scope.sort.reverse = $scope.sort.key != sortBy ? false : !$scope.sort.reverse;
					$scope.sort.key     = sortBy;
				};

				$scope.onCheckAll = function()
				{
					$scope.checkAll = !$scope.checkAll;
					$scope.data.forEach(function(channel) {
						channel.checked = $scope.checkAll;
					});
				};

				$scope.changePage = function() { 
					request();
				};

				// Local Methods
				// ------------------------------------------------------------------------
				
				function request() {
					appService.cancel($scope.onRequest);

					$scope.onLoad 	 = true;
					$scope.onRequest = appService.get({'page': $scope.pageCurrent}, $scope.filters, $scope.sort);
					$scope.onRequest.then(handleResponse, handleError);
				};

				function handleResponse(data) {
					$scope.pageCurrent             = data.current_page;
					$scope.pageCount               = data.last_page;
					$scope.pageTotal               = data.all_channel;
					$scope.data                    = data.data;
					
					$scope.onLoad                  = false;
					$scope.checkAll                = false;
					
					createLabelCount(data);
				};

				function handleError(error) {
					console.log(error);
				};

				function createLabelCount(data) {
					var status = $scope.filters.status,
						label  = '';

					// Total all channel
					label  = '<span>'+$filter('number')(data.total)+' Channel</span>';  
					
					$scope.countChannel = label;  
				}

				// On
				// ------------------------------------------------------------------------
				
				$scope.$on('allOnTabChange', function() {
					appService.apply({'localContext': $scope});

					appService.url          = self.baseURL + 'api/channel/';
					console.log( appService );
				});

				// Init context as this first opened tab
				// ------------------------------------------------------------------------
				
				appService.apply({'localContext': $scope});
			})();
		},

		formatController: function($scope, $attrs, $rootScope, $filter, appService) {

			return (function() {
				Object.assign($scope, angular.copy(appService.initData));
				// Init
				// ------------------------------------------------------------------------
				appService.controllerData['format'] = $scope;
				$scope.controller       = 'format';
				$scope.onRequest        = void 0;
				appService.url          = self.baseURL + 'api/format/'; 
				console.log( appService )
				request();

				$scope.dropdownAction = function(name, selected) {
					$scope.filters[name.replace(/^filter-/, '')] = selected;
					console.log( selected );
					appService.url          = self.baseURL + 'api/format/';
					request();
				};

				function request() {
					appService.cancel($scope.onRequest);

					$scope.onLoad 	 = true;
					$scope.onRequest = appService.get({'page': $scope.pageCurrent}, $scope.filters, $scope.sort);
					$scope.onRequest.then(handleResponse, handleError);
				};

				function handleResponse(data) {
					$scope.pageCurrent             = data.current_page;
					$scope.pageCount               = data.last_page;
					$scope.pageTotal               = data.all_channel;
					$scope.data                    = data.data;
					
					$scope.onLoad                  = false;
					$scope.checkAll                = false;
					
					createLabelCount(data);
					appService.url          = self.baseURL + 'api/channel/';
				};

				function handleError(error) {
					console.log(error);
				};

				function createLabelCount(data) {
					var status = $scope.filters.status,
						label  = '';

					// Total all channel
					label  = '<span>'+$filter('number')(data.total)+' Format</span>';  
					
					$scope.countChannel = label;  
					
				}

				$scope.$on('formatOnTabChange', function() {
					appService.apply({'localContext': $scope});

					appService.url          = self.baseURL + 'api/format/';
					console.log( appService );
				});
			})();
		}
	});

	// ------------------------------------------------------------------------
	
	var contentApp = new App;
	contentApp.init();
});

