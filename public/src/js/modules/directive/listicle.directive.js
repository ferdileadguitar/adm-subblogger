import ArticleEditors   from './article.directive';
// import BluimpFileUpload from './../service/bluimp-fileupload.service.js';

class ListicleEditors extends ArticleEditors {

	constructor($timeout, $http) {
		super();
		this.restrict = 'A';
		this.require  = '^modalEditor';
		// this.bindToController = 'editorArticleCtrl';
	}

	link($scope, $element, $attrs, modalEditor) {
		var windowListicle    = $scope.data.content || {};
		$scope.listicleOption = windowListicle.sort || 'ordinal';
		$scope.listicleItems  = windowListicle.models || [{"order": "1", "title": "", "image_str": "", "content": ""}];;
		// console.log( $scope.data );

		// ------------------------------------------------------------------------
		// const Editors = new ArticleEditors();

		// Set Listicle Order
		$scope.setOrder = function(event, order) {
			var $el = $(event.currentTarget || event.srcElement)

			if (!$el.length) { return false; }
			if ($scope.listicleOption == order) { return false; }

			$scope.listicleOption = order;

			// Rewrite numbering
			if (order != 'points')
			{ this.rewriteNumber(-1); }

			// Change class
			$el.siblings('.active').removeClass('active');
			$el.addClass('active');
		};

		// ------------------------------------------------------------------------

		// Add Listicle Item
		$scope.addItem = function(event) {
			var $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-separator'),
				indexPos 	= 0;

			if (!$el.length) { return false; }

			// ------------------------------------------------------------------------

			// Get the order position
			if (! $el.prev('.eb-listicle-item').length) { indexPos = 0; }
			else { indexPos = $el.prev('.eb-listicle-item').index('.eb-listicle-item') + 1; }

			$scope.listicleItems.splice(indexPos, 0, {"order": (indexPos + 1), "title": "", "image_str": "", "content": ""});

			// ------------------------------------------------------------------------

			// Re-write numbering
			this.rewriteNumber(indexPos);

			// Init Listicle Editor
			setTimeout(function() { $scope.initListicleEditor(indexPos); }, 50);
		};

		// ------------------------------------------------------------------------

		// Remove Listicle Item
		$scope.removeItem = function(event) {
			var $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-item'),
				indexPos 	= $el.index('.eb-listicle-item');

			if (!$el.length || (indexPos <= -1)) { return false; }

			// ------------------------------------------------------------------------

			$scope.listicleItems.splice(indexPos, 1);

			// empty? create new
			if (! $scope.listicleItems.length) {
				$scope.listicleItems.splice(0, 0, {"order": 1, "title": "", "image_str": "", "content": ""});

				// Init Listicle Editor
				setTimeout(function() { $scope.initListicleEditor(0); }, 50);
			}

			// ------------------------------------------------------------------------

			// Re-write numbering
			this.rewriteNumber(indexPos - 1);
		};

		// ------------------------------------------------------------------------

		// Remove Listicle Item Image Preview
		$scope.removeItemPreview = function(event) {
			var $el 		= $(event.currentTarget || event.srcElement).closest('.eb-listicle-item'),
				indexPos 	= $el.index('.eb-listicle-item');

			if (!$el.length || (indexPos <= -1)) { return false; }

			// ------------------------------------------------------------------------
			this.removePreview(event);

			$scope.listicleItems[indexPos].image_str = void 0;
			$scope.listicleItems[indexPos].image_id = void 0;
		};

		// ------------------------------------------------------------------------

		// Initial Listicle Editor
		// $scope.initListicleEditor = function(indexPos) {
		// 	var $el = $element.find('.eb-listicle-list .eb-listicle-item:eq(' + indexPos + ')'),
		// 		contentEditor;

		// 	// Editor
		// 	new self.MainEditor.titleEditorApp($el.find('.listicle-item-title'), {
		// 		placeholder: "Title"
		// 	});

		// 	contentEditor = new MediumEditor($el.find('.listicle-item-content'), {
		// 		toolbar: {
		// 			buttons: ['bold', 'italic', 'underline', 'anchor', 'h1', 'h2', 'quote', "orderedlist", "unorderedlist"],
		// 		},
		// 		paste: {
		// 			cleanPastedHTML: true,
		// 			cleanTags: ["meta", "script", "style", "label"]
		// 		},
		// 		placeholder: {
		// 			text: 'Write your content here ------- block the text to show text tool'
		// 		}
		// 	});
		// 	$el.find('.listicle-item-content').data('editor', contentEditor);

		// 	$el.find('.listicle-item-content').mediumInsert({
		//         editor: contentEditor,
		//         addons: {
		//         	images: {
		//         		deleteScript: null,
		//         		autoGrid: 0,
		//         		fileUploadOptions: {
		//         			url: self.MainEditor.uploadCoverUrl+"?type=body"
		//         		},
		//         		styles: {
		//         		    wide: { label: '<span class="icon-align-justify"></span>' },
		//         		    left: null,//{ label: '<span class="icon-align-left"></span>' },
		//         		    right: { label: '<span class="icon-align-right"></span>' },
		//         		    grid: null
		//         		}
		//         	},
		//         	embeds: {
		//         		placeholder: 'Paste a YouTube, Facebook, Twitter, Instagram link/video and press Enter',
  //   					oembedProxy: '',
		//         		styles: {
		//         			wide: null,
		//         			left: null,
		//         			right: null
		//         		}
		//         	}
		//         }
		//     });

		//     // FileUpload
		//     self.MainEditor._initFileUpload($el.find('.fileupload-pool input[type=file]'), {dropZone: $el.find('.fileupload-pool'), uploadURL: self.MainEditor.uploadCoverUrl+"?type=body"});
		// };

		// ------------------------------------------------------------------------

		// Rewrite numbering
		$scope.rewriteNumber = function(indexPos) {
			switch ($scope.listicleOption) {
				case 'reverse':
					for (var i = ($scope.listicleItems.length - 1), j = 0; i >= 0; i--, j++) {
						$scope.listicleItems[j].order = i + 1;
					}
					break;
				default:
					for (var i = 0; i < $scope.listicleItems.length; i++) {
						$scope.listicleItems[i].order = i + 1;
					}
					break;
			}
		};


		/*=================================================
					SEPERATE FUNCTION
		===================================================*/
		// setTimeout(function() {
		// 	$.each($('.eb-listicle-list .eb-listicle-item'), function() {
		// 		var $self = $(this),
		// 			contentEditor;

		// 		// Editor
		// 		new self.MainEditor.titleEditorApp($self.find('.listicle-item-title'), {
		// 			placeholder: "Title"
		// 		});

		// 		contentEditor = new MediumEditor($self.find('.listicle-item-content'), {
		// 			toolbar: {
		// 				buttons: ['bold', 'italic', 'underline', 'anchor', 'h1', 'h2', 'quote', "orderedlist", "unorderedlist"],
		// 			},
		// 			paste: {
		// 				cleanPastedHTML: true,
		// 				cleanTags: ["meta", "script", "style", "label"]
		// 			},
		// 			placeholder: {
		// 				text: 'Write your content here ------- block the text to show text tool'
		// 			}
		// 		});
		// 		$self.find('.listicle-item-content').data('editor', contentEditor);

		// 		$self.find('.listicle-item-content').mediumInsert({
		// 	        editor: contentEditor,
		// 	        addons: {
		// 	        	images: {
		// 	        		deleteScript: null,
		// 	        		autoGrid: 0,
		// 	        		fileUploadOptions: {
		// 	        			url: self.MainEditor.uploadCoverUrl+"?type=body"
		// 	        		},
		// 	        		styles: {
		// 	        		    wide: { label: '<span class="icon-align-justify"></span>' },
		// 	        		    left: null,//{ label: '<span class="icon-align-left"></span>' },
		// 	        		    right: { label: '<span class="icon-align-right"></span>' },
		// 	        		    grid: null
		// 	        		}
		// 	        	},
		// 	        	embeds: {
		// 	        		placeholder: 'Paste a YouTube, Facebook, Twitter, Instagram link/video and press Enter',
  //       					oembedProxy: '',
		// 	        		styles: {
		// 	        			wide: null,
		// 	        			left: null,
		// 	        			right: null
		// 	        		}
		// 	        	}
		// 	        }
		// 	    });

		// 		// FileUpload
		// 	    self.MainEditor._initFileUpload($self.find('.fileupload-pool input[type=file]'), {dropZone: $self.find('.fileupload-pool'), uploadURL: self.MainEditor.uploadCoverUrl+"?type=body"});
		// 	});
		// }, 50);
	}
}

export default ListicleEditors;