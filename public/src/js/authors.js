import { css }  from '../scss/main.scss';
import MainApp  from './app';
import joii     from 'joii';

/*TEMPLATES*/
import mdl_editor from './authors/template/mdl-editor.html';
import editorPass from './authors/template/editor-password.html'

/*DIRECTIVE*/
import passwordDirective from './authors/directive/set-password.directive.js';
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
				// $scope.searchInput         = void 0;
				
				// Local init
				// ------------------------------------------------------------------------
				appService.apply({
					'url': self.baseURL + 'api/authors/',
					'appContext': $scope, 
					'tabService': tabService,

					'controllerData': {
						'all': { data: [] },
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

				$scope.bulkAction = bulkAction;
				$scope.setStatus  = setStatus;
				$scope.delete     = remove;

				// Local methods
				// ------------------------------------------------------------------------
				
				function bulkAction(selected, data, $ctrlScope) {
					// if (['approve', 'moderate', 'reject', 'sticky', 'premium'].includes(selected) &&
					// 	data.find(function(item) { return item.status == 2; })
					// ) {
					// 	appService.modal($scope, {
					// 		type: 'text',
					// 		text: 'You cannot apply this action if one of the selected authors is <strong>Private author</strong>',
					// 		cancelText: 'Ok',
					// 		singleButton: true
					// 	});

					// 	return false;
					// }

					// ------------------------------------------------------------------------
					
					var id = _.map(data, function(item, index) { return item.id; }),
						status, method, url, params, set;

					data.forEach(function(author) {
						setOtherCtrlData(author, 'every', {loading: true});
					});

					if (['approve', 'moderate', 'reject'].includes(selected)) {
						switch (selected) {
							case 'moderate':
								status = -2;
								break;
							case 'reject':
								status = 0;
								break;
							case 'approve':
							default:
								status = 1;
								break;
						};

						method = 'put';
						url    = {'url': 'set-status'};
						params = {'author-status': status};
						set    = {status: status};
					}
					else if (['sticky', 'premium'].includes(selected)) {
						method = 'put';
						url    = {'url': 'set-' + selected};
						params = {'set': 1};
						set    = selected == 'sticky' ? {'is_sticky' : 1} : {'is_premium': 1};
					}
					else {
						method = 'delete';
						url    = '';
						params = {};
					}

					appService[method](url, Object.assign(params, {'id': id.join(',')}))
						.then(function(response) {

						  	$ctrlScope.createLabelCount(response);
						  	
						  	data.forEach(function(author) {
						  		setOtherCtrlData(author, 'every', {loading: false});

						  		if (selected == 'delete') { setOtherCtrlData(author, 'every', {}, true); }
						  		else { setOtherCtrlData(author, 'every', set); }
						  		
						  	});
						  }, function(error) {
						  	console.log(error);

						  	data.forEach(function(author) {
						  		setOtherCtrlData(author, 'every', {loading: false});
						  	});
						  });
				}

				function setStatus(author, status, $ctrlScope) {
					setOtherCtrlData(author, $ctrlScope.controller, {loading: true});

					appService.put({'url': 'set-status'}, _.extend($ctrlScope.filters, {'id': author.id, 'author-status': status}) )
					  .then(function(data) {
					  	// $root
					  	$ctrlScope.createLabelCount(data);

					  	setOtherCtrlData(author, $ctrlScope.controller, {loading: false});
					  	setOtherCtrlData(author, $ctrlScope.controller, {status: status});
					  }, function(error) {
					  	console.log(error);

					  	setOtherCtrlData(author, $ctrlScope.controller, {loading: false});
					  });
				};

				function remove(author, $ctrlScope) {
					setOtherCtrlData(author, $ctrlScope.controller, {loading: true});

					appService.delete({}, _.extend($ctrlScope.filters, {'id': author.id, 'author-status': -99}) )
					  .then(function(data) {
					  	$ctrlScope.createLabelCount(data);

					  	setOtherCtrlData(author, $ctrlScope.controller, {}, true);
					  }, function(error) {
					  	console.log(error);
					  	setOtherCtrlData(author, $ctrlScope.controller, {loading: false});
					  });
				};

				function setOtherCtrlData(author, controller, data, remove) {
					var otherCtrl = void 0;

					if (remove) {
						['all', 'moderated', 'contributor'].forEach(function(ctrl) {
							appService.controllerData[ctrl].data = _.without(
								appService.controllerData[ctrl].data,
								_.findWhere(appService.controllerData[ctrl].data, {'id': author.id})
							);
						});
						return;
					}

					// ------------------------------------------------------------------------

					switch (controller) {
						case 'all':
							otherCtrl = ['moderated', 'contributor'];
							break;
						case 'moderated':
							otherCtrl = ['all', 'contributor'];
							break;
						case 'contributor':
							otherCtrl = ['moderated', 'all'];
							break;
						case 'every':
							otherCtrl = ['all', 'moderated', 'contributor'];
							break;
					}

					otherCtrl.forEach(function(ctrl) {
						var otherCtrlData = _.findWhere(appService.controllerData[ctrl].data, {'id': author.id});
						if (otherCtrlData) { Object.assign(otherCtrlData, data); }
					});
					Object.assign(author, data);
				};

			}]).
			controller('allController', ['$scope', '$attrs', '$rootScope', '$filter', 'appService', self.allController]);

			// Directive
			// ------------------------------------------------------------------------
			
			this.directive();

			// Startup module
			// ------------------------------------------------------------------------
			
			this.application.run(function($rootScope, $templateCache) {
				// Implement underscore to rootScope
		        $rootScope._ = _;

		        // Caching all templete -> Ferdi Ardiansa
		        $templateCache.put('set-password.html', editorPass);
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

                    	$scope.onCheck = function(author) {
							author.checked = !author.checked;
						};

						$scope.setPassword = function(author, index) {
							$scope.setEditor(author, index, 'set-password');
						};

						$scope.delete = function(author) {
							appService.modal($scope, {
								data: author, 
								type: 'delete',
								text: 'Are you sure to delete <strong>' + author.username + '</strong>?',
								okCallback: function(scope) {
									appService.delete({'url': 'delete-users'}, {id: author.id})
									.then(
										function SuccessCallback(res) {	
											var newData = appService.localContext.data;
											
											appService.localContext.data = _.without(newData, _.find(newData, author));
											
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

						$scope.setEditor   = function(author, index, type) {
							appService.modalEditor($scope, {
								ids  : index,
								data : author,
								type : type
							});

							$scope.$broadcast('mdl_data', { allAuthors : $scope.data, authors : author, type});
							
							// This use for all directives
							$rootScope.allAuthors = _.extend($scope, { author : author, all : $scope.data });
						};

						$scope.parseFeedsLink = function(author) {
							$window.open(author.url, '_blank');
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

			this.application.directive('modalEditor', ['appService', function(appService) {
				return {
					restrict    : 'E',
					replace     :  true,
					template    : mdl_editor,
					controller  : function($scope, $element, $rootScope) {
						var datas = {};

							$scope.allAuthor = $scope.allAuthors; 
							$scope.layout  = {
								type : $scope.type,
								url  : $scope.type + '.html'
							};
							
							// $scope.$broadcast('data_context', 'send fronm editors');
							$scope.close = function() {
								if( $rootScope.onProgress ) { confirm('Are you sure to cancel !!!') }
								$scope.$destroy();
							};

							$scope.$on('$destroy', function() {  
								$element.remove();
							});
					}	

				}
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
			this.application.directive('editorPassword', passwordDirective);
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
				request();
				
				// Methods
				// ------------------------------------------------------------------------
				
				$scope.search = function(searchInput) {
					$scope.filters.search = searchInput || ''; 
					$scope.pageCurrent    = 1;

					request();
				};

				$scope.bulkAction = function(selected) {
					appService.appContext.bulkAction(
						selected, 
						_.where($scope.data, 
							{'checked': true}).map(function(item) { 
								return {id: item.id, status: item.status}; 
							}
						),
						$scope
					);
				};

				$scope.dropdownAction = function(name, selected) {
					$scope.filters[name.replace(/^filter-/, '')] = selected;
				};

				$scope.onSort = function(sortBy) { 
					$scope.sort.reverse = $scope.sort.key != sortBy ? false : !$scope.sort.reverse;
					$scope.sort.key     = sortBy;
				};

				$scope.onCheckAll = function()
				{
					$scope.checkAll = !$scope.checkAll;
					$scope.data.forEach(function(author) {
						author.checked = $scope.checkAll;
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
					$scope.pageTotal               = data.all_author;
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

					// Total all author
					label  = '<span>'+$filter('number')(data.total)+' Authors</span>';  
					
					$scope.countAuthor = label;  
				}

				// On
				// ------------------------------------------------------------------------
				
				$scope.$on('allOnTabChange', function() {
					appService.apply({'localContext': $scope});
				});

				// Init context as this first opened tab
				// ------------------------------------------------------------------------
				
				appService.apply({'localContext': $scope});
			})();
		},
	});

	// ------------------------------------------------------------------------
	
	var contentApp = new App;
	contentApp.init();
});

