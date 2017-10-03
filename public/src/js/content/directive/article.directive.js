import app_helper     from './../../common/title-helper.js';
import MediumEditor   from './../../vendor/medium/medium-editor.js';
import thisFileUpload from './../../common/bluimp-fileupload.service.js';
import mediumInsert   from 'imports-loader?$=jquery,define=>false,this=>window!./../../vendor/medium/medium-editor-insert-plugin.js';

let editors        = { title: void 0, lead: void 0, content: void 0 };
let titleEditorApp = app_helper.titleEditorApp;
let allData        = {};

class ArticleEditors {
	
	constructor($http, $rootScope, $timeout, $sce, $q, appFactory, appService) {
		'ngInject';

		this.restrict       = 'A';
		this.editors        = editors;
		this.titleEditorApp = titleEditorApp;
		this.MediumEditor   = MediumEditor;
		this.mediumInsert   = mediumInsert;
		this.fileUpload     = thisFileUpload;
		
		this._$http         = $http;
		this._$sce          = $sce;
		this._$q            = $q;
		this._$timeout      = $timeout;
		this._appFactory    = appFactory;
		this._appService    = appService;
		this._$scope        = $rootScope;
	}

	get $inject() {
		return ['$http', '$rootScope', '$timeout', '$sce', '$q', 'appFactory', 'appService'];
	}
 
	_post(data) {
	}

	static _listicleFormat(element, obj) {
		let data = obj.data, listicleItems = [], contentStringify;
		
		data.content = _.extend({}, obj.dataListicle);
		_.each(data.content.models, (value, index) => {
			let $element 		= $(element).find('.eb-listicle-list'),
				$listicleEl 	= $element.find('.eb-listicle-item:eq(' + index + ')'),
				contentEditor 	= $listicleEl.find('.listicle-item-content').data('editor');

			listicleItems.push({
				order 		: value.order,
				title 		: $listicleEl.find('.listicle-item-title').text(),
				image_str 	: $listicleEl.find('.listicle-item-image input[name=fn]').val() || '',
				content 	: contentEditor.serialize()['element-0'].value.replace(/contenteditable(=(\"|\')true(\"|\')|)/ig, '') //revert this commit 1f38d8598b7cdb99e3dba420b6fe06b59a3101ac
			});
		});

		// Tags
		let tags = [];
		_.each(obj.tags, (items, key) => {
			tags.push(items.text);
		});
		
		data.tags    = tags.join(';');
		data.content = JSON.stringify({ content : (data.content.content), sort : (data.content.sort), models : (listicleItems) });
		
		return {
			obj    : data,
			method : 'PUT' 
		};
	}

	static _articleFormat(element, obj, options) {
		let data = obj.data, content = {};
		let $fid = $(element).find('.fileupload-pool.cover-picture input[type=hidden][name=fid]');
			if ($fid.length) {
				obj.data.image = _.extend(data.image, { id : $fid.val() });
			} else {
				obj.data.image = _.extend(data.image, { id : void 0 });
			}
			// Tags
			let tags = [];
			_.each(obj.tags, (items, key) => {
				tags.push(items.text);
			});

			content = obj.editors.content.serialize()['editor-content'].value.replace(/contenteditable(=(\"|\')true(\"|\')|)/ig, ''); //revert this commit 1f38d8598b7cdb99e3dba420b6fe06b59a3101ac
			
			data.content = content;
			data.tags    = tags.join(';');

			return {
				obj    : data,
				method : 'PUT'
			};
	}

	static _titleFormat(element, obj, options) {

		return {
			obj    : obj.data,
			method : 'PUT',
			params : '/set-title'
		};
	}

	static _tagsFormat(element, obj, option) {
		let tags = [], data = obj.data;

		_.each(obj.tags, (items, key) => {
			tags.push(items.text);
		});

		data.tags = tags.join(';');

		return {
			obj    : data,
			method : 'PUT',
			params : '/set-tags' 
		}
	}

	static _channelFormat(element, obj, option) {
		let data = obj.data;

		return {
			obj     : data,
			method  : 'PUT',
			params  : '/set-channel' 
		}
	}

	static _createdFormat(element, obj, option) {
		let data = obj = _.extend(obj.data, {created : obj.created});

		return {
			obj 	: data,
			method  : 'PUT',
			params  : '/set-created'
		}
	} 

	static _prepSave(element, obj, options) {
		// Firts of all , let's define what is in obj data post_type, so i mean is in list below
		let type = (_.isUndefined(obj.type)) ? obj.data.post_type : obj.type;

		switch(type) {
			case 'article':
				return this._articleFormat(element, obj, options);
				break;
			case 'listicle':
				return this._listicleFormat(element, obj, options);
				break;
			case 'set-title':
				return this._titleFormat(element, obj);
				break;
			case 'set-tags':
				return this._tagsFormat(element, obj);
				break;
			case 'set-channel':
				return this._channelFormat(element, obj);
				break;
			case 'set-created':
				return this._createdFormat(element, obj);
				break;
			default:
				throw new Error('Sorry we can\'t process your request');
				break;
		}
	}

	static _tags(obj) {
		let data = [];
		_.map( obj ,(items, index) => 
			data.push({text : items.title })
		);
		return data;
	}

	link(scope, element, attrs) {
		let mainClass  = ArticleEditors,
			_base      = this._$scope,
			_content   = _base.allPosts.post,
			self       = this;

		scope.tempSave  = void 0;
		scope.message   = void 0;
		scope.tags      = [];
		scope.uploading = false;
		scope.onProgress= false;
		scope.data      = angular.copy(_base.allPosts.post);

		// really stuck for this one
		// if( scope.data.post_type == 'article' ) {
		scope.data.content = this._$sce.trustAsHtml($.parseJSON(scope.data.content));
		// }

		// Tags Autocomplete
		scope.tags     = mainClass._tags(_content.tags);

		scope.loadTags = (query) => {
			let config, temp;
			return self._$http.get(window.baseURL + 'api/tags?q=' + query);
		};

		// ------------------------------------------------------------------------

		// Temporary Save
		scope.saveTemp = () => {

		}

		// ------------------------------------------------------------------------

		// Save
		scope.saveClick = (type) => {
			if( scope.onProgress ) { return false }

			let options = { editors : editors, type : type };
			let data    = {};
			
			try{
				data = mainClass._prepSave(element, _.extend(scope, options));
			    scope.onProgress = true;
				
				self._appFactory.postFeed( data ).then(
					(res) => {
						scope.onProgress = false;
					
						self._$timeout(() => scope.feedsAssignObject(res) ,10);
					},
					(err) => {
						scope.onProgress = false;
						if( err.data.error == 'bad_request' || _.contains([400, 404, 500, 502], err.status) ) {
							self._$q.reject(data);

							self._appService.modal(scope, {
								type         : 'text',
								text         : '<h3>Whoops... something went wrong !</h3><h5>' + err.data.error_description + '</h5>',
								cancelText   : 'Ok',
								singleButton : true
							});
						}
						return err;
					}
				);
			} catch(err) {
				console.error( err );
				self._appService.modal(scope, {
					type         : 'text',
					text         : '<h3>Whoops... something went wrong !</h3><h5>' + err + '</h5>',
					cancelText   : 'Ok',
					singleButton : true
				});
			} finally { /* close connections */ }
		};

		// ------------------------------------------------------------------------

		// Browse Cover Picture File
		scope.browseFile =  (event) => {
			let $el = $(event.currentTarget || event.srcElement);

			$el.parent('.fileupload-pool').find('.file-upload').trigger('click');
		};

		// ------------------------------------------------------------------------

		// Remove Preview
		scope.removePreview = (event, cover) => {
			let $el = $(event.currentTarget || event.srcElement);
			let id = $($el).closest('.on-preview').find("input[name='fid']").val();

			if(!_.isUndefined(cover))
				scope.data.image = {
					id   : void 0,
					name : void 0,
					url  : void 0
				}

			if(!$($el).closest('.eb-listicle-item')[0]){
				scope.data.image.id = void 0;
			}
			if(!scope.data.images){
				scope.data.images = [];
			}
			scope.data.images.push({id: id, destroy: true});
		};

		// ------------------------------------------------------------------------

		// Get Image from URL
		scope.getImage = (event) => {
			let $el = $(event.currentTarget || event.srcElement);
		};

		// ------------------------------------------------------------------------

		// Set Channel
		scope.setChannel = ($event, slug) => {
			let $el = $($event.currentTarget || $event.srcElement);

			scope.data.channel = {
				slug : slug,
				name : $el.text()
			}

			scope.channel = scope.data.channel;

			if(typeof toggleSelectCategory == ''){
				toggleSelectCategory();
			}
		};

		// ------------------------------------------------------------------------

		scope.openCategory = ($event) => {
			let $el = $($event.currentTarget || $event.srcElement);

			$el.find('.eb-category-list').toggleClass('open');
		}

		scope.feedsAssignObject = (newData) => {
			let _data = [],
				ids   = scope.$parent.ids;

				scope.$apply(() => {

					if( _.has(newData, 'content') ) {
						newData.content = JSON.stringify(newData.content);
					}
						
					_.extend(_base.allPosts.data[ids], newData);

					scope.$destroy();

					$('body').find('.mdl.mdl-editor').remove();
				});
				
				// scope.$new(true);

				scope.$on('mdl_data', (event, args) => {
					// console.info( args );
				});
		}

		// ------------------------------------------------------------------------
		self._$timeout(() => {
			// Title
			if (element.find('.eb-title').length) {
				let titleEditor = new app_helper.titleEditorApp(element.find('.eb-title'), {
					placeholder: 'Title'
				});

				self.editors.title = titleEditor;
			}

			// Lead
			if (element.find('.eb-lead').length) {
				let titleEditor = new app_helper.titleEditorApp(element.find('.eb-lead'), {
					placeholder: 'Subtitle: it will be shown in feed'
				});

				self.editors.lead = titleEditor;
			}

			// Content
			if (element.find('.eb-article').length) {
				let contentEditor = new MediumEditor('#editor-content', {
					toolbar: {
						buttons: ['bold', 'italic', 'underline', 'anchor', 'h1', 'h2', 'quote', "orderedlist", "unorderedlist"],
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

				self.mediumInsert($);
				$('#editor-content').mediumInsert(
				{
			        editor: contentEditor,
			        addons: {
			        	images: {
			        		deleteScript: null,
			        		autoGrid: 0,
			        		fileUploadOptions: {
			        			url: self.thisFileUpload.uploadCoverUrl+"?type=body",
			        		},
			        		styles: {
			        		    wide: { label: '<span class="icon-align-justify"></span>' },
			        		    left: null,//{ label: '<span class="icon-align-left"></span>' },
			        		    right: { label: '<span class="icon-align-right"></span>' },
			        		    grid: null
			        		},
			        		uploadCompleted : ($el, data) => {

			        		},
			        		uploadFailed : (uploadErrors, data) => {
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

				self.editors.content = contentEditor;

				if (element.find('.fileupload-pool.cover-picture').length) {
					self.fileUpload._initFileUpload(
							element.find('.fileupload-pool.cover-picture input[type=file]'), 
							{
								dropZone: element.find('.fileupload-pool.cover-picture'), 
								uploadURL: self.fileUpload.uploadCoverUrl
							},
							scope);
				}
			}
		}, 500);
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
export default ArticleEditors;