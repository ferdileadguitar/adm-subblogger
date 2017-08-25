import { css }  from '../scss/main.scss';
import MainApp  from './app';
import joii     from 'joii';

/*TEMPLATES*/
import mdl_editor from './modules/template/mdl-editor.html';
import article    from './modules/template/editor-article.html';
import listicle   from './modules/template/editor-listicle.html';
import title      from './modules/template/editor-title.html';
import tags       from './modules/template/editor-tags.html';
import channel    from './modules/template/editor-channel.html';
import created    from './modules/template/mdl-editor-created.html';

/*DIRECTIVE*/
import articleDirective   from './modules/directive/article.directive.js';
import listicleDirective  from './modules/directive/listicle.directive.js';
import createdDirective   from './modules/directive/created.directive.js';
import titleDirective     from './modules/directive/title.directive.js';

// APP
// ------------------------------------------------------------------------
require(['./app.js', 'joii', 'angular-sanitize'], function(MainApp, joii) {
	'use strict';
	// console.log( angularSanitize );
	var App = joii.Class({ extends: MainApp }, {
		init: function() {
			var self = this;

			// Application controller
			// this.application = angular.module('keepoApp', ['ngSanitize', 'ngTagsInput']);
			// ------------------------------------------------------------------------
			
			this.application.controller('app-controller', ['$scope', '$attrs', 'appService', 'tabService', function($scope, $attrs, appService, tabService) {
				// Vars
				// ------------------------------------------------------------------------

				$scope.mainApp         = self;
				$scope.moderationCount = 0;
				$scope.onRequest       = void 0;

				// Local init
				// ------------------------------------------------------------------------
				appService.apply({
					'url': self.baseURL + 'api/contents/',
					'appContext': $scope, 
					'tabService': tabService,

					'controllerData': {
						'all': { data: [] },
						'moderated': { data: [] },
						'contributor': { data: [] },
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
							status: 'all-status',
							search: ''
						},

						sort: {
							key: 'created',
							reverse: true
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

				$scope.changeTitle = function(data) {
					console.log(  )
				};		

				$scope.bulkAction = bulkAction;
				$scope.setStatus  = setStatus;
				$scope.setSticky  = setSticky;
				$scope.setPremium = setPremium;
				$scope.delete     = remove;

				// Local methods
				// ------------------------------------------------------------------------
				
				function bulkAction(selected, data, $ctrlScope) {
					if (['approve', 'moderate', 'reject', 'sticky', 'premium'].includes(selected) &&
						data.find(function(item) { return item.status == 2; })
					) {
						appService.modal($scope, {
							type: 'text',
							text: 'You cannot apply this action if one of the selected posts is <strong>Private Post</strong>',
							cancelText: 'Ok',
							singleButton: true
						});
					}

					// ------------------------------------------------------------------------
					
					var id = _.map(data, function(item, index) { return item.id; }),
						status, method, url, params, set;

					data.forEach(function(post) {
						setOtherCtrlData(post, 'every', {loading: true});
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
						params = {'status': status};
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
						  	$scope.moderationCount = response.moderationCount || $scope.moderationCount;

						  	data.forEach(function(post) {
						  		setOtherCtrlData(post, 'every', {loading: false});

						  		if (selected == 'delete') { setOtherCtrlData(post, 'every', {}, true); }
						  		else { setOtherCtrlData(post, 'every', set); }
						  		
						  	});
						  }, function(error) {
						  	console.log(error);

						  	data.forEach(function(post) {
						  		setOtherCtrlData(post, 'every', {loading: false});
						  	});
						  });
				}

				function setStatus(post, status, $ctrlScope) {
					setOtherCtrlData(post, $ctrlScope.controller, {loading: true});

					appService.put({'url': 'set-status'}, {'id': post.id, 'status': status})
					  .then(function(data) {
					  	$scope.moderationCount = data.moderationCount || $scope.moderationCount;

					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  	setOtherCtrlData(post, $ctrlScope.controller, {status: status});
					  }, function(error) {
					  	console.log(error);

					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  });
				};

				function setSticky(post, setSticky, $ctrlScope) {
					setOtherCtrlData(post, $ctrlScope.controller, {loading: true});

					appService.put({'url': 'set-sticky'}, {'id': post.id, 'set': setSticky})
					  .then(function(data) {
					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  	setOtherCtrlData(post, $ctrlScope.controller, {is_sticky: setSticky});
					  }, function(error) {
					  	console.log(error);

					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  });
				};

				function setPremium(post, setPremium, $ctrlScope) {
					setOtherCtrlData(post, $ctrlScope.controller, {loading: true});

					appService.put({'url': 'set-premium'}, {'id': post.id, 'set': setPremium})
					  .then(function(data) {
					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  	setOtherCtrlData(post, $ctrlScope.controller, {is_premium: setPremium});
					  }, function(error) {
					  	console.log(error);

					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  });
				};

				function remove(post, $ctrlScope) {
					setOtherCtrlData(post, $ctrlScope.controller, {loading: true});

					appService.delete({'id': post.id})
					  .then(function(data) {
					  	$scope.moderationCount = data.moderationCount || $scope.moderationCount;

					  	setOtherCtrlData(post, $ctrlScope.controller, {}, true);
					  }, function(error) {
					  	console.log(error);
					  	setOtherCtrlData(post, $ctrlScope.controller, {loading: false});
					  });
				};

				function setOtherCtrlData(post, controller, data, remove) {
					var otherCtrl = void 0;

					if (remove) {
						['all', 'moderated', 'contributor'].forEach(function(ctrl) {
							appService.controllerData[ctrl].data = _.without(
								appService.controllerData[ctrl].data,
								_.findWhere(appService.controllerData[ctrl].data, {'id': post.id})
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
						var otherCtrlData = _.findWhere(appService.controllerData[ctrl].data, {'id': post.id});
						if (otherCtrlData) { Object.assign(otherCtrlData, data); }
					});

					Object.assign(post, data);
				};

			}]).
			controller('allController', ['$scope', '$attrs', 'appService', self.allController]).
			controller('moderationController', ['$scope', '$attrs', 'appService', self.moderationController]);

			// Directive
			// ------------------------------------------------------------------------
			
			this.directive();

			// Startup module
			// ------------------------------------------------------------------------
			
			this.application.run(function($rootScope, $templateCache) {
				// Implement underscore to rootScope
		        $rootScope._ = _;

		        $templateCache.put('article.html', article);
		        $templateCache.put('listicle.html', listicle);
		        $templateCache.put('title.html', title);
		        $templateCache.put('tags.html', tags);
		        $templateCache.put('channel.html', channel);
		        $templateCache.put('created.html', created);
			});
			angular.bootstrap(document.querySelector("html"), ["keepoApp"]);
		},

		directive: function() {
			this.application.directive('feeds', ['$compile', '$rootScope', '$window', 'appService', function($compile, $rootScope, $window, appService) {
				return {
					restrict: 'E',
					replace: true,
                    templateUrl: 'feedListTemplate',
                    controller: function ($scope, appService) {
                    },
                    link: function($scope, $elements, $attrs) {
                    	$scope.post = {};

                    	// $scope.$watch()
                    	$scope.convertTags = function(tags) {
                    		return (tags && tags.length) ? _.map(tags, function(tag) { return tag.title; }).join(', ') : '<em>No tag available</em>';
                    	};

                    	$scope.onCheck = function(post) {
							post.checked = !post.checked;
						};

						$scope.setStatus = function(post, status) {
							appService.appContext.setStatus(post, status, $scope);
						};

						$scope.delete = function(post) {
							appService.modal($scope, {
								data: post, 
								type: 'delete',
								text: 'Are you sure to delete <strong>' + post.title + '</strong>?',
								okCallback: function(scope) {
									appService.appContext.delete(scope.data, scope.$parent);

									scope.close();
								}
							});
						};

						$scope.setSticky = function(post) {
							appService.appContext.setSticky(post, (!post.is_sticky ? 1 : 0), $scope);
						};

						$scope.setPremium = function(post) {
							appService.appContext.setPremium(post, (!post.is_premium ? 1 : 0), $scope);
						};

						$scope.setEditor = function(post, type, index) {
							appService.modalEditor($scope, {
								ids  : index,
								data : post,
								type : type
							});
							$scope.$broadcast('mdl_data', { allPosts : $scope.data, posts : post, type });
							
							// This use for all directives
							$rootScope.allPosts = Object.assign($scope, { post : post });
						};

						$scope.parseFeedsLink = function(post) {
							$window.open('http://localhost:8000/' + post.user + '/' + post.slug, '_blank');
						}
                    }
				};
			}]);

			this.application.directive('tab', ['$compile', function($compile) {
				return {
					restrict: 'E',
					replace: true,
					templateUrl: 'tabTemplate'
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

						$scope.$on('mdl_data', function(item, args){ 
							$scope.allPost = args.allPosts; 
							// $scope.data    = args.posts; 
							$scope.layout  = {
								type : args.type,
								url  : args.type + '.html'
							};
							
							// $scope.$broadcast('data_context', 'send fronm editors');
							$scope.close = function() {
								if( $rootScope.onProgress ) { confirm('Are you sure to cancel !!!') }
								$scope.$destroy();
							};

							$scope.$on('$destroy', function() {  
								$element.remove();
							});
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

			this.application.directive('editorArticle',  articleDirective);
			this.application.directive('editorListicle', listicleDirective);
			this.application.directive('editorCreated', createdDirective);
			this.application.directive('editorTitle', titleDirective);
		},

		allController: function($scope, $attrs, appService) {
			return (function() {
				Object.assign($scope, angular.copy(appService.initData));

				// Init
				// ------------------------------------------------------------------------
				
				appService.controllerData['all'] = $scope;
				$scope.controller = 'all';
				$scope.onRequest  = void 0;
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
					$scope.data.forEach(function(post) {
						post.checked = $scope.checkAll;
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
					$scope.data                    = data.data;
					
					$scope.onLoad                  = false;
					$scope.checkAll                = false;
				};

				function handleError(error) {
					console.log(error);
				};

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

		moderationController: function($scope, $attrs, appService) {
			return (function() {
				_.map(appService.initData, function(val, key) {
					$scope[key] = val;
				});

				// Init
				// ------------------------------------------------------------------------
			
				appService.controllerData['moderated'] = $scope;
				$scope.controller = 'moderated';
				$scope.filters.status = 'moderated';	// Overide status from filters
				$scope.onRequest = void 0;

				request();


				// Methods
				// ------------------------------------------------------------------------
				
				$scope.search = function(searchInput) {
					$scope.filters.search = searchInput || ''; 
					$scope.pageCurrent    = 1;

					request();
				};

				$scope.bulkAction = function(selected) {
					console.log(_.where($scope.data, {'checked': true}).map(function(item) { return item.id }));
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
					$scope.data.forEach(function(post) {
						post.checked = $scope.checkAll;
					});
				}

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
					$scope.data                    = data.data;
					
					$scope.onLoad                  = false;
					$scope.checkAll                = false;
				};

				function handleError(error) {
					console.log(error);
				};
				
				// On
				// ------------------------------------------------------------------------
				
				$scope.$on('moderationOnTabChange', function() 
				{ appService.apply({'localContext': $scope}); });
			})();
		}
	});

	// ------------------------------------------------------------------------
	
	var contentApp = new App;
	contentApp.init();
});

