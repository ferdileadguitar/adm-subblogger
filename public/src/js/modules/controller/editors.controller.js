import fileupload from './../service/bluimp-fileupload.service.js';

var editors = {
	MainApp 		: void 0,
	$mainBody 		: void 0,
	editorApp 		: void 0,
	ngApp 			: void 0,
	titleEditorApp 	: titleEditorApp,
	embedURL 		: '//emb.keepo.pyo/',
	uploadURL 		: 'api/v1/asset/img',
	uploadCoverUrl: 'api/v1/asset/img-cover',
	editors  		: {title: void 0, lead: void 0, content: void 0};

class EditorsController{

	extend(b, a) {
	    var prop;
	    if (b === undefined) {
	        return a;
	    }
	    for (prop in a) {
	        if (a.hasOwnProperty(prop) && b.hasOwnProperty(prop) === false) {
	            b[prop] = a[prop];
	        }
	    }
	    return b;
	}

	titleEditorApp(element, options) {
		this.init(element, options);

		this.titleEditorApp.prototype = {
			$el : $('.title-holder'),
			defaults: {
				placeholder : 'Title'
			},

			_bind: function() {
				var self = this;

				if (! this.$el.length) { return; }

				// ------------------------------------------------------------------------

				this.$el.bind('clickoutside', function() {
					if (self.$el.text().replace(/\s+/, '') == '') {
						$(this).addClass('empty-field');
					}
				});

				this.$el.bind('click', function() {
					$(this).removeClass('empty-field');
				});

				this.$el.bind('paste', function(e) {
					//e.preventDefault();
					//document.execCommand("insertHTML", false, e.clipboardData.getData("text/plain"));

					if (e.originalEvent && e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
	                    e.preventDefault();
	                    window.document.execCommand('insertText', false, e.originalEvent.clipboardData.getData('text/plain'));
	                }
	                else if (e.clipboardData && e.clipboardData.getData) {
	                    e.preventDefault();
	                    window.document.execCommand('insertText', false, e.clipboardData.getData('text/plain'));
	                }
	                else if (window.clipboardData && window.clipboardData.getData) {
	                    // Stop stack overflow
	                    if (!_onPaste_StripFormatting_IEPaste) {
	                        _onPaste_StripFormatting_IEPaste = true;
	                        e.preventDefault();
	                        window.document.execCommand('ms-pasteTextOnly', false);
	                    }
	                    _onPaste_StripFormatting_IEPaste = false;
	                }
				});
			},

			init: function(element, options) {
				this.$el     = element;
				this.options = extend(options, this.defaults);

				// ------------------------------------------------------------------------

				this.$el.attr('contenteditable', true).
						 attr('data-placeholder', this.options.placeholder).
						 addClass('title-editor-app');

				if (this.$el.text().replace(/\s+/, '') == '' || ((/^<@/).test(this.$el.text()) && (/@>$/).test(this.$el.text()))) {
					this.$el.addClass('empty-field');
				}

				// ------------------------------------------------------------------------

				this._bind();
			}
		};

	}

	_initEditor: function() {
		var self = this;

		// ------------------------------------------------------------------------
		// Module
		// ------------------------------------------------------------------------

		this.MainApp.keepoApp = angular.module('keepoApp', ['ngSanitize', 'ngTagsInput']);

		// Init controller
		this.MainApp.keepoApp.controller('editorController', ['$scope', '$element', '$http', function($scope, $element, $http) {
			$scope.tempSave  = void 0;
			$scope.message   = void 0;
			$scope.tags      = [];
			$scope.uploading = false;

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

			$scope.data = window.editorData ? $.extend($scope.data, window.editorData) : $scope.data;
			$scope.tags = $scope.data.tags;


			// ------------------------------------------------------------------------

			// Tags Autocomplete
			$scope.loadTags = function(query) {
				var config, temp;

				temp = JSON.parse(window.localStorage.token);
				return $http.get(self.MainApp.baseURL + 'api/v1/tags?q=' + query);
			};

			// ------------------------------------------------------------------------

			// Temporary Save
			$scope.saveTemp = function() {

			}

			// ------------------------------------------------------------------------

			// Save Draft
			$scope.saveDraft = function() {
				var data = $scope._save(angular.copy($scope.data));

				self.prepSave(data, true);
			};

			// ------------------------------------------------------------------------

			$scope.cancelClick = function(){
				window.history.back();
			};
			// ------------------------------------------------------------------------

			// Save
			$scope.saveClick = function() {
				var data = $scope._save(angular.copy($scope.data));

				self.prepSave(data);
			};

			// ------------------------------------------------------------------------

			$scope._save = function (data) {
				// Cover Image
				var $fid = $($element).find('.fileupload-pool.cover-picture input[type=hidden][name=fid]');
				if ($fid.length) {
					data.image = $.extend(data.image, { id : $fid.val() });
				} else {
					data.image = $.extend(data.image, { id : void 0 });
				}

				// Title and Lead (fuck! I don't know why but contentedit directive sometimes makes noises Ã Â² _Ã Â² )
				if ($($element).find('.eb-title').length) {
					data.title = $($element).find('.eb-title').text();
				}
				if ($($element).find('.eb-lead').length) {
					data.lead = $($element).find('.eb-lead').text();
				}

				// Tags
				var tags = [];
				$.each($scope.tags, function() {
					tags.push(this.text);
				});
				data.tags    = tags;

				// Content
				data.content = self.editors.content.serialize()['editor-content'].value.replace(/contenteditable(=(\"|\')true(\"|\')|)/ig, ''); //revert this commit 1f38d8598b7cdb99e3dba420b6fe06b59a3101ac

				return data;
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
		}]);

		this.MainApp.keepoApp.directive('droppable', function() {
			return {
				scope: {},
				link: function(scope, element) {
					var el = element[0];

					el.addEventListener(
					    'dragover',
					    function(e) {
					        e.dataTransfer.dropEffect = 'move';
					        // allows us to drop
					        if (e.preventDefault) e.preventDefault();
					        this.classList.add('over');
					        return false;
					    },
					    false
					);

					el.addEventListener(
					    'dragenter',
					    function(e) {
					        this.classList.add('over');
					        return false;
					    },
					    false
					);

					el.addEventListener(
					    'dragleave',
					    function(e) {
					        this.classList.remove('over');
					        return false;
					    },
					    false
					);
				}
			}
		});

		// ------------------------------------------------------------------------
		// Upload Cover Picture
		// ------------------------------------------------------------------------

		if (this.$mainBody.find('.fileupload-pool.cover-picture').length) {
			this._initFileUpload(this.$mainBody.find('.fileupload-pool.cover-picture input[type=file]'), {dropZone: this.$mainBody.find('.fileupload-pool.cover-picture'), uploadURL: this.uploadCoverUrl});
		}

		// ------------------------------------------------------------------------
		// Medium Editor Setup
		// ------------------------------------------------------------------------

		// Wait until angular finished rendering Ã‚Â¯\_(Ã£Æ’â€ž)_/Ã‚Â¯
		setTimeout(function() {
			// Title
			if (self.$mainBody.find('.eb-title').length) {
				var titleEditor = new titleEditorApp(self.$mainBody.find('.eb-title'), {
					placeholder: 'Title'
				});

				self.editors.title = titleEditor;
			}

			// Lead
			if (self.$mainBody.find('.eb-lead').length) {
				var titleEditor = new titleEditorApp(self.$mainBody.find('.eb-lead'), {
					placeholder: 'Subtitle: it will be shown in feed'
				});

				self.editors.lead = titleEditor;
			}

			// Content
			if (self.$mainBody.find('.eb-article').length) {
				var contentEditor = new MediumEditor('#editor-content', {
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

				$('#editor-content').mediumInsert(
				{
			        editor: contentEditor,
			        addons: {
			        	images: {
			        		deleteScript: null,
			        		autoGrid: 0,
			        		fileUploadOptions: {
			        			url: self.uploadCoverUrl+"?type=body",
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
			}

			// ------------------------------------------------------------------------

			// Set window.onbeforeunload (Simple, too lazy to check whether the content has been changed :P)
			window.onbeforeunload = function() {
				return 'Apa kamu yakin mau menutup post editor? Semua perubahan akan hilang! :(';
			};
		}, 50);



		// ------------------------------------------------------------------------
		// start up the module
		if (! this.editorApp || (this.editorApp == 'article')) {
			this.MainApp.keepoApp.run();
			angular.bootstrap(document.querySelector("html"), ["keepoApp"]);
		}
	}

	// ------------------------------------------------------------------------

	init(MainApp) {
		var self = this;

		// Initialize variables
		this.MainApp        = MainApp;
		this.$mainContainer = $('.main-container');
		this.$mainBody      = $('.editor-container');
		this.editorApp      = this.$mainBody.data('editor');
		this.uploadURL 		= MainApp.baseURL + this.uploadURL;
		this.uploadCoverUrl = MainApp.baseURL + this.uploadCoverUrl;

		// Initialize editor
		// if (! self.editorApp || (self.editorApp == 'article'))
		// { self._initEditor(); }
		// else {
		// 	if (self.editorApp && typeof window[self.editorApp].init == 'function') {
		// 			window[self.editorApp].init(self);
		// 	}
		// }
	}

	prepSave(data, draft) {
		var self 		= this,
			draft 		= draft || false,
			prepData 	= {};

		if (this.$mainContainer.hasClass('on-progress')) { return false; }

		// ------------------------------------------------------------------------

		// Set up the data
		prepData = {
			cover 		: data.image.id || 0,
			title 		: data.title ? data.title : 'Untitled',
			lead 		: data.lead,
			content 	: data.content,
			tags 		: data.tags.join(';'),
			source 		: data.source ? ((/^http\:\/\//).test(data.source) ? data.source : 'http://' + data.source) : '',
			channel 	: data.channel.slug,
			type 		: this.editorApp,
			status  	: draft ? 'draft' : (data.status == -1 ? data.status : 'submit'),
			slug 		: data.slug
		};

		// ------------------------------------------------------------------------

		this.save(prepData);
	}

	save(data) {
		var self 	= this,
			method  = !data.slug ? 'POST' : 'PUT',
			$scope 	= angular.element('[ng-controller=editorController]').scope();

		// ------------------------------------------------------------------------
		// Still uploading?

		if ($scope.uploading) {
			$scope.message = {error: "We haven't finished uploading your image yet.<br />Please try again after all image(s) have been uploaded."};
			return false;
		}

		// ------------------------------------------------------------------------

		this.$mainContainer.addClass('on-progress');


		// Now let's try to post it
		$.ajax({
			url  	: this.MainApp.baseURL + 'api/v1/feed',
			data 	: data,
			method 	: method
		}).
		done(function(response) {

			// Remove onbeforeunload
			window.onbeforeunload = void 0;

			// Got redirect in the response? then it must be edit
			if (response.redirect) {
				// Redirect
				window.location.replace(response.redirect);

				return;
			}

			// Set message popup
			if (data.status == 'submit' || response.success === true) {
				// Remove loading status
				self.$mainContainer.removeClass('on-progress');

				$scope.message = {success: true};
				$scope.$apply();

				return;
			}
		}).
		fail(function(response) {
			// Remove loading status
			self.$mainContainer.removeClass('on-progress');

			// Get the reason
			if (response.responseJSON) { var reason = response.responseJSON.error_description; }
			else { var reason = JSON.parse(response.responseText).error_description; }

			// Set message popup
			$scope.message = {error: reason};
			$scope.$apply();
		});
	}
}
editorsController.$inject = []; 
window.editors = editors;