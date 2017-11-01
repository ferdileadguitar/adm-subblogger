import ArticleEditors from './article.directive';

class ListicleEditors extends ArticleEditors {

	constructor($http, $rootScope, $timeout, $sce, $q, appFactory, appService) {
		// Super call
		super($http, $rootScope, $timeout, $sce, $q, appFactory, appService);

		this.restrict     = 'A'; 
		this._buildModels = this.buildModels;
		// this.scope        = {
			// data : '@'
		// }
	}

	buildModels(obj) {
		_.map(obj.models, (item, key) => {
			obj.models[key].content = this._$sce.trustAsHtml(item.content);
		});
	}
	
	link(scope, element, attrs) {
		let	self          = this,
			data          = angular.copy(self._$scope.allPosts.post),
		    dataListicle  = angular.fromJson(JSON.parse(data.content)) || {};

		// Decode html file with Angular $sce
		_.map(dataListicle.models, (item, key) => {
			dataListicle.models[key].content = self._$sce.trustAsHtml(item.content);
		});

		scope.dataListicle = dataListicle;

		// Set Listicle Order
		scope.setOrder = (event, order) => {
			let $el = $(event.currentTarget || event.srcElement)

			if (!$el.length) { return false; }
			if (scope.listicleOption == order) { return false; }

			// scope.listicleOption    = order;
			scope.dataListicle.sort = order;

			// Rewrite numbering
			if (order != 'points')
			{ scope.rewriteNumber(-1); }

			// Change class
			$el.siblings('.active').removeClass('active');
			$el.addClass('active');
		};

		// ------------------------------------------------------------------------

		// Add Listicle Item
		scope.addItem = (event) => {
			let $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-separator'),
				indexPos 	= 0;

			if (!$el.length) { return false; }

			// ------------------------------------------------------------------------

			// Get the order position
			if (! $el.prev('.eb-listicle-item').length) { indexPos = 0; }
			else { indexPos = $el.prev('.eb-listicle-item').index('.eb-listicle-item') + 1; }

			// scope.listicleItems.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});
			scope.dataListicle.models.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});

			// ------------------------------------------------------------------------

			// Re-write numbering
			scope.rewriteNumber(indexPos);

			// Init Listicle Editor
			this._$timeout(() => { scope.initListicleEditor(indexPos); }, 50);

		};

		// ------------------------------------------------------------------------

		// Remove Listicle Item
		scope.removeItem = (event) => {
			let $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-item'),
				indexPos 	= $el.index('.eb-listicle-item');

			if (!$el.length || (indexPos <= -1)) { return false; }

			// ------------------------------------------------------------------------

			scope.dataListicle.models.splice(indexPos, 1);

			// empty? create new
			if (! scope.dataListicle.models.length) {
				scope.dataListicle.models.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});

				// Init Listicle Editor
				this._$timeout(() => { scope.initListicleEditor(0); }, 50);
			}

			// ------------------------------------------------------------------------

			// Re-write numbering
			scope.rewriteNumber(indexPos - 1);
		};

		// ------------------------------------------------------------------------

		// Remove Listicle Item Image Preview
		scope.removeItemPreview = (event) => {
			let $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-item'),
				indexPos 	= $el.index('.eb-listicle-item');

			if (!$el.length || (indexPos <= -1)) { return false; }

			// ------------------------------------------------------------------------
			scope.removePreview(event);

			scope.dataListicle.models[indexPos].image_str = void 0;
			scope.dataListicle.models[indexPos].image_id = void 0;
		};

		// ------------------------------------------------------------------------

		// Initial Listicle Editor
		scope.initListicleEditor = (indexPos) => {
			let $el = element.find('.eb-listicle-list .eb-listicle-item:eq(' + indexPos + ')'),
				contentEditor;

			// Editor
			new self.titleEditorApp($el.find('.listicle-item-title'), {
				placeholder: "Title"
			});

			contentEditor = new self.MediumEditor($el.find('.listicle-item-content'), {
				toolbar: {
					buttons: ['bold', 'italic', 'underline', 'anchor', 'h1', 'h2', 'quote', "orderedlist", "unorderedlist"],
				},
				paste: {
					cleanPastedHTML: true,
					cleanTags: ["meta", "script", "style", "label"]
				},
				placeholder: {
					text: 'Write your content here ------- block the text to show text tool'
				}
			});
			$el.find('.listicle-item-content').data('editor', contentEditor);

			$el.find('.listicle-item-content').mediumInsert({
		        editor: contentEditor,
		        addons: {
		        	images: {
		        		deleteScript: null,
		        		autoGrid: 0,
		        		fileUploadOptions: {
		        			url: self.fileUpload.uploadCoverUrl+"?type=body"
		        		},
		        		styles: {
		        		    wide: { label: '<span class="icon-align-justify"></span>' },
		        		    left: null,//{ label: '<span class="icon-align-left"></span>' },
		        		    right: { label: '<span class="icon-align-right"></span>' },
		        		    grid: null
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
		        	}
		        }
		    });

		    // FileUpload
		    self.fileUpload._initFileUpload(
    			$el.find('.fileupload-pool input[type=file]'), 
    				{
    					dropZone: $el.find('.fileupload-pool'), 
    					uploadURL: self.fileUpload.uploadCoverUrl+"?type=body"
    				},
    				scope);
		};

		// ------------------------------------------------------------------------

		// Rewrite numbering
		scope.rewriteNumber = (indexPos) => {
			switch (scope.dataListicle.sort) {
				case 'reverse':
					for (let i = (scope.dataListicle.models.length - 1), j = 0; i >= 0; i--, j++) {
						scope.dataListicle.models[j].order = i + 1;
					}
					break;
				default:
					for (let i = 0; i < scope.dataListicle.models.length; i++) {
						scope.dataListicle.models[i].order = i + 1;
					}
					break;
			}
		};

		// /*=================================================
		// 			SEPERATE FUNCTION
		// ===================================================*/
		self._$timeout(() => {
			$.each($('.eb-listicle .eb-listicle-list .eb-listicle-item'), (index, elm) => {
				let $self = $('.eb-listicle-item:eq('+index+')'),
					contentEditor;

				// Editor
				new self.titleEditorApp($self.find('.listicle-item-title'), {
					placeholder: "Title"
				});

				contentEditor = new self.MediumEditor($self.find('.listicle-item-content'), {
					toolbar: {
						buttons: ['bold', 'italic', 'underline', 'anchor', 'h1', 'h2', 'quote', "orderedlist", "unorderedlist"],
					},
					paste: {
						cleanPastedHTML: true,
						cleanTags: ["meta", "script", "style", "label"]
					},
					placeholder: {
						text: 'Write your content here ------- block the text to show text tool'
					}
				});
				$self.find('.listicle-item-content').data('editor', contentEditor);

				self.mediumInsert($);
				$self.find('.listicle-item-content').mediumInsert({
			        editor: contentEditor,
			        addons: {
			        	images: {
			        		deleteScript: null,
			        		autoGrid: 0,
			        		fileUploadOptions: {
			        			url: self.fileUpload.uploadCoverUrl+"?type=body"
			        		},
			        		styles: {
			        		    wide: { label: '<span class="icon-align-justify"></span>' },
			        		    left: null,//{ label: '<span class="icon-align-left"></span>' },
			        		    right: { label: '<span class="icon-align-right"></span>' },
			        		    grid: null
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
			        	}
			        }
			    });

			    self.fileUpload._initFileUpload(
			    	$self.find('.fileupload-pool input[type=file]'), 
			    	{
			    		dropZone: $self.find('.fileupload-pool'), 
			    		uploadURL: self.fileUpload.uploadCoverUrl+"?type=body"
			    	},
			    	scope);
			});
		}, 50);
	}
}
export default ListicleEditors;