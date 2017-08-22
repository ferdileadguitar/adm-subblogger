import helper          from './../library/helper.js';
import MediumEditor    from './../../vendor/medium/medium-editor.js';
import thisFileUpload  from './../service/bluimp-fileupload.service.js';
import mediumInsert    from 'imports-loader?$=jquery,define=>false,this=>window!./../../vendor/medium/medium-editor-insert-plugin.js';

const editors        = { title: void 0, lead: void 0, content: void 0 };
const titleEditorApp = helper.titleEditorApp;

class ArticleEditors {
	
	constructor($http, $rootScope) {
		'ngInject';

		this.restrict       = 'A';
		this.editors        = editors;
		this.titleEditorApp = titleEditorApp;
		
		// let _$http  = $http;
		this.$http   = $http;
		// this.appFactory     = appFactory;
		// this.$q 			= $q;
	}

	get $inject() {
		return ['$http', '$rootScope'];
	}
 
	// $http($http) {
	// 	return $http;
	// }

	_post(data) {
		console.log( this._$http, this.http );
		// return this.$http();
		// return ArticleEditors.prototype.constructor;
		// $http({
		// 	method       : params.type,
		// 	url        	 : window.baseURL + 'api/feeds',
		// 	data         : data,
		// 	responseType : 'json',
		// }).then(
		// 	function(res) {
		// 		$q.resolve(res.data);
		// 	},
		// 	function(err) {
		// 		$q.reject(err);
		// 	}
		// );
	}

	static _listicleFormat(element, data) {
		var listicleItems = [], contentStringify;
		$.each(data.content.models, function(index, value) {
			var $element 		= $(element).find('.eb-listicle-list'),
				$listicleEl 	= $element.find('.eb-listicle-item:eq(' + index + ')'),
				contentEditor 	= $listicleEl.find('.listicle-item-content').data('editor');

			// console.log( contentEditor.serialize() )
			listicleItems.push({
				order 		: value.order,
				title 		: $listicleEl.find('.listicle-item-title').text(),
				image_str 	: $listicleEl.find('.listicle-item-image input[name=fn]').val() || '',
				content 	: contentEditor.serialize()['element-0'].value.replace(/contenteditable(=(\"|\')true(\"|\')|)/ig, '') //revert this commit 1f38d8598b7cdb99e3dba420b6fe06b59a3101ac
			});
		});
		data.content = JSON.stringify({ content : data.content.content, sort : data.content.sort, models : listicleItems });
		
		return data;
	}

	static _articleFormat($element, data, options) {
		var $fid = $($element).find('.fileupload-pool.cover-picture input[type=hidden][name=fid]');
			if ($fid.length) {
				data.image = $.extend(data.image, { id : $fid.val() });
			} else {
				data.image = $.extend(data.image, { id : void 0 });
			}

			// Tags
			var tags = [];
			$.each(data.tags, function() {
				tags.push(this.text);
			});
			data.tags    = tags;
			data.content = options.editors.content.serialize()['editor-content'].value.replace(/contenteditable(=(\"|\')true(\"|\')|)/ig, ''); //revert this commit 1f38d8598b7cdb99e3dba420b6fe06b59a3101ac

			return data;
	}

	static _prepSave($element, data, options) {
		switch(data.post_type) {
			case 'article':
				return this._articleFormat($element, data, options);
				break;
			case 'listicle':
				return this._listicleFormat($element, data, options);
				break;
			default:
				throw new Error('Sorry we can\'t process your request');
				break;
		}
	}

	controller($scope, $element, $attrs, $timeout, $rootScope, $http, $sce, appFactory) {
		var $this     = $.extend({}, helper),
		    thisClass = ArticleEditors;

		$scope.tempSave  = void 0;
		$scope.message   = void 0;
		$scope.tags      = [];
		$scope.uploading = false;
		$scope.onProgress= false;

		$scope.data = {
			title 		: void 0,
			lead 		: void 0,
			content 	: void 0,
			image 		: {
				id 	: void 0,
				url : void 0
			},
			tags 		: [],
			source 		: void 0,
			channel 	: {
				slug : void 0,
				name : void 0
			},
			slug		: void 0
		};

		$scope.data = angular.copy($scope.$parent.data);
		$scope.data.content = ($scope.data.post_type == 'article') ? $sce.trustAsHtml($.parseJSON($scope.data.content)) : angular.fromJson(JSON.parse($scope.data.content));
		
		// Tags Autocomplete
		$.map( ($scope.data.tags) , function(items, index) {
			$scope.tags.push({text : items.title });
			
		});

		$scope.loadTags = function(query) {
			var config, temp;

			return $http.get(window.baseURL + 'api/tags?q=' + query);
		};

		// ------------------------------------------------------------------------

		// Temporary Save
		$scope.saveTemp = function() {

		}

		// ------------------------------------------------------------------------

		// Save
		$scope.saveClick = function() {
			if( $scope.onProgress ) { return false }
		
			const options = { editors : editors };
			let data      = {};
			
			try{
				$scope.onProgress = true;
				data = thisClass._prepSave($element, angular.copy($scope.data), options);
			} catch(err) {
				console.error( err );
			} finally {
				console.log( data );
				appFactory.postFeed( data, 'PUT' ).then(
					function(res) {
						$scope.onProgress = false;
					
						$timeout(function() { $scope.feedsAssignObject(res); },10);
					},
					function(err) {
						$scope.onProgress = false;
						return err;
					}
				);

				console.log( $scope )
			}
			
		};

		// ------------------------------------------------------------------------

		// Browse Cover Picture File
		$scope.browseFile = function (event) {
			var $el = $(event.currentTarget || event.srcElement);

			$el.parent('.fileupload-pool').find('.file-upload').trigger('click');
		};

		// ------------------------------------------------------------------------

		// Remove Preview
		$scope.removePreview = function(event) {
			var $el = $(event.currentTarget || event.srcElement);
			var id = $($el).closest('.on-preview').find("input[name='fid']").val();
			if(!$($el).closest('.eb-listicle-item')[0]){
				$scope.data.image.id = void 0;
			}
			if(!$scope.data.images){
				$scope.data.images = [];
			}
			$scope.data.images.push({id: id, destroy: true});
		};

		// ------------------------------------------------------------------------

		// Get Image from URL
		$scope.getImage = function(event) {
			var $el = $(event.currentTarget || event.srcElement);
		};

		// ------------------------------------------------------------------------

		// Set Channel
		$scope.setChannel = function($event, slug) {
			var $el = $($event.currentTarget || $event.srcElement);

			$scope.data.channel = {
				slug : slug,
				name : $el.text()
			}
			if(typeof toggleSelectCategory == 'function'){
				toggleSelectCategory();
			}
		};

		// ------------------------------------------------------------------------

		// Close Message popup
		$scope.closeMessage = function($event) {
			$scope.message = void 0;
		};

		$scope.openCategory = function($event) {
			var $el = $($event.currentTarget || $event.srcElement);

			$el.find('.eb-category-list').toggleClass('open');
		}

		// ------------------------------------------------------------------------
		$timeout(function() {
			// Title
			if ($element.find('.eb-title').length) {
				var titleEditor = new $this.titleEditorApp($element.find('.eb-title'), {
					placeholder: 'Title'
				});

				editors.title = titleEditor;
			}

			// Lead
			if ($element.find('.eb-lead').length) {
				var titleEditor = new $this.titleEditorApp($element.find('.eb-lead'), {
					placeholder: 'Subtitle: it will be shown in feed'
				});

				editors.lead = titleEditor;
			}

			// Content
			if ($element.find('.eb-article').length) {
				var contentEditor = new MediumEditor('#editor-content', {
					toolbar: {
						buttons: ['bold', 'italic', 'underline', 'anchor', 'h1', 'h2', 'quote', "orderedlist", "unorderedlist"],
						// static : true,
						// sticky : false
					},
					paste: {
						cleanPastedHTML: true,
						cleanTags: ["meta", "script", "style", "label"]
					},
					placeholder: {
						text: 'Write your content here ------- block the text to show text tool'
					},
					// elementsContainer : document.querySelector('.editor-body'),
				});

				mediumInsert($);
				$('#editor-content').mediumInsert(
				{
			        editor: contentEditor,
			        addons: {
			        	images: {
			        		deleteScript: null,
			        		autoGrid: 0,
			        		fileUploadOptions: {
			        			url: thisFileUpload.uploadCoverUrl+"?type=body",
			        		},
			        		styles: {
			        		    wide: { label: '<span class="icon-align-justify"></span>' },
			        		    left: null,//{ label: '<span class="icon-align-left"></span>' },
			        		    right: { label: '<span class="icon-align-right"></span>' },
			        		    grid: null
			        		},
			        		uploadCompleted : function($el, data) {

			        		},
			        		uploadFailed : function(uploadErrors, data) {
			        			console.error( uploadErrors );
			        		}

			        	},
			        	embeds: {
			        		placeholder: 'Paste a YouTube, Facebook, Twitter, Instagram link/video and press Enter',
        					oembedProxy: '',
			        		styles: {
			        		    wide: null,
			        		    left: null,
			        		    right: null
			        		}
			        	},
			        	embeds: {
			        		placeholder: 'Paste a YouTube, Facebook, Twitter, Instagram link/video and press Enter',
        					oembedProxy: '',
			        		styles: {
			        		    wide: null,
			        		    left: null,
			        		    right: null
			        		}
			        	},
			        }
			    });
				editors.content = contentEditor;
				if ($element.find('.fileupload-pool.cover-picture').length) {
					thisFileUpload._initFileUpload(
							$element.find('.fileupload-pool.cover-picture input[type=file]'), 
							{
								dropZone: $element.find('.fileupload-pool.cover-picture'), 
								uploadURL: thisFileUpload.uploadCoverUrl
							},
							$scope);
				}
			}
			// ------------------------------------------------------------------------

			// Set window.onbeforeunload (Simple, too lazy to check whether the content has been changed :P)
			window.onbeforeunload = function() {
				return 'Apa kamu yakin mau menutup post editor? Semua perubahan akan hilang! :(';
			};
		}, 50);

		$scope.feedsAssignObject = function(newData) {
			var _data = [],
				ids   = $scope.$parent.ids;

				$scope.$apply(function() {
					newData.content = JSON.stringify(newData.content);
					_.extend($scope.$parent.allPost[ids], newData)
				});
				
				$('body').find('.mdl.mdl-editor').remove();

				$scope.$on('mdl_data', function(event, args) {
					console.log( args );
				});
		}
	}

	thisFileUpload() {
		return thisFileUpload;
	}

	helper() {
		return helper;
	}

	feedsAssignObject(newData) {
		return newData;
	}
}
// ArticleEditors.$inject = ['$http'];
export default ArticleEditors;