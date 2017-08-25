import ArticleEditors from './article.directive';

class ListicleEditors extends ArticleEditors {

	constructor($http, $rootScope, $timeout, $sce, $q, appFactory) {
		// 'ngInject';
		super($http, $rootScope, $timeout, $sce, $q, appFactory);

		this.restrict   = 'A'; 
	}
	
	controller($scope, $element, $timeout) {
	}

	link(scope, $element, $attrs) {
		var	self    = this,
		    dataListicle  = scope.data.content = angular.fromJson(JSON.parse(scope.data.content)) || {};

		$.map(dataListicle.models, (item, key) => {
			dataListicle.models[key].content = self._$sce.trustAsHtml(item.content);
		});

		// Set Listicle Order
		scope.setOrder = function(event, order) {
			var $el = $(event.currentTarget || event.srcElement)

			if (!$el.length) { return false; }
			if (scope.listicleOption == order) { return false; }

			// scope.listicleOption    = order;
			scope.data.content.sort = order;
			console.log( scope );
			// Rewrite numbering
			if (order != 'points')
			{ this.rewriteNumber(-1); }

			// Change class
			$el.siblings('.active').removeClass('active');
			$el.addClass('active');
		};

		// ------------------------------------------------------------------------

		// Add Listicle Item
		scope.addItem = function(event) {
			var $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-separator'),
				indexPos 	= 0;

			if (!$el.length) { return false; }

			// ------------------------------------------------------------------------

			// Get the order position
			if (! $el.prev('.eb-listicle-item').length) { indexPos = 0; }
			else { indexPos = $el.prev('.eb-listicle-item').index('.eb-listicle-item') + 1; }

			scope.listicleItems.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});
			scope.data.content.models.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});

			// ------------------------------------------------------------------------

			// Re-write numbering
			this.rewriteNumber(indexPos);

			// Init Listicle Editor
			setTimeout(function() { scope.initListicleEditor(indexPos); }, 50);
			console.log( scope );

		};

		// ------------------------------------------------------------------------

		// Remove Listicle Item
		scope.removeItem = function(event) {
			var $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-item'),
				indexPos 	= $el.index('.eb-listicle-item');

			if (!$el.length || (indexPos <= -1)) { return false; }

			// ------------------------------------------------------------------------

			scope.listicleItems.splice(indexPos, 1);

			// empty? create new
			if (! scope.listicleItems.length) {
				scope.listicleItems.splice(0, 0, {"order": 1, "title": "", "image_str": "", "content": ""});
				scope.data.content.models.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});

				// Init Listicle Editor
				setTimeout(function() { scope.initListicleEditor(0); }, 50);
			}

			// ------------------------------------------------------------------------

			// Re-write numbering
			this.rewriteNumber(indexPos - 1);
		};

		// ------------------------------------------------------------------------

		// Remove Listicle Item Image Preview
		scope.removeItemPreview = function(event) {
			var $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-item'),
				indexPos 	= $el.index('.eb-listicle-item');

			if (!$el.length || (indexPos <= -1)) { return false; }

			// ------------------------------------------------------------------------
			this.removePreview(event);

			scope.listicleItems[indexPos].image_str = void 0;
			scope.listicleItems[indexPos].image_id = void 0;
		};

		// ------------------------------------------------------------------------

		// Initial Listicle Editor
		scope.initListicleEditor = function(indexPos) {
			var $el = $element.find('.eb-listicle-list .eb-listicle-item:eq(' + indexPos + ')'),
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
		        			url: self.thisFileUpload().uploadCoverUrl+"?type=body"
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
		    self.thisFileUpload()._initFileUpload(
    			$el.find('.fileupload-pool input[type=file]'), 
    				{
    					dropZone: $el.find('.fileupload-pool'), 
    					uploadURL: self.thisFileUpload().uploadCoverUrl+"?type=body"
    				},
    				scope);
		};

		// ------------------------------------------------------------------------

		// Rewrite numbering
		scope.rewriteNumber = function(indexPos) {
			switch (scope.data.content.sort) {
				case 'reverse':
					for (var i = (scope.data.content.models.length - 1), j = 0; i >= 0; i--, j++) {
						scope.data.content.models[j].order = i + 1;
					}
					break;
				default:
					for (var i = 0; i < scope.data.content.models.length; i++) {
						scope.data.content.models[i].order = i + 1;
					}
					break;
			}
		};

		// scope.save = prepSave;

		// /*=================================================
		// 			SEPERATE FUNCTION
		// ===================================================*/
		setTimeout(function() {
			$.each($('.eb-listicle-list .eb-listicle-item'), function() {
				var $self = $(this),
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
			        			url: self.thisFileUpload().uploadCoverUrl+"?type=body"
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
			    self.thisFileUpload()._initFileUpload(
			    	$self.find('.fileupload-pool input[type=file]'), 
			    	{
			    		dropZone: $self.find('.fileupload-pool'), 
			    		uploadURL: self.thisFileUpload().uploadCoverUrl+"?type=body"
			    	},
			    	scope);
			});
		}, 50);
	}
}
export default ListicleEditors;