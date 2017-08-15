function _initFileUpload($element, options) {
		var self 	= this,
			fileUploadOptions;

		if (! $element.length) { return false; }

		// ------------------------------------------------------------------------

		fileUploadOptions = {
			url: options.uploadURL ? options.uploadURL : self.uploadURL,
			dropZone: void 0,
			/*beforeSend: function(xhr, data) {
		        var file = data.files[0],
		        	temp = JSON.parse(window.localStorage.token);

		        xhr.setRequestHeader('Authorization', temp.token_type + ' ' + temp.access_token);
		    },*/

			add: function(e, data) {
				var that 	= $(this).data('blueimpUIFileupload'),
	                options = that.options,
	                files 	= data.files;

	            // Validation
	            // ------------------------------------------------------------------------

	            that._adjustMaxNumberOfFiles(-files.length);
	            data.isAdjusted = true;
	            data.files.valid = data.isValidated = that._validate(files);

	            if (! data.files.valid){
	                that._showError(data.files);
	                return false;
	            }

	            // DOM manipulation
	            // ------------------------------------------------------------------------

	            that.options.dropZone.find('.option-url').hide();
	            that.options.dropZone.siblings('.remove').hide();

	            data.context = that._renderTemplate(data.files).data('data', data);

		        // ------------------------------------------------------------------------

		        var $scope = angular.element('[ng-controller=editorController]').scope();
		        $scope.uploading = true;
		        $scope.$apply();

		        // ------------------------------------------------------------------------

	            // Trigger
	            // ------------------------------------------------------------------------

	            if ((that._trigger('added', e, data) !== false) &&
	                    (options.autoUpload || data.autoUpload) &&
	                    data.autoUpload !== false && data.isValidated) {

	                data.submit();
	            }
			},

			done: function(e, data) {
				var that        = $(this).data('blueimpUIFileupload'),
	                $dropZone   = that.options.dropZone,
	                context     = $(data.context),
	                fid, fn, $input;

		        // ------------------------------------------------------------------------

						if($(this).closest('.eb-listicle')[0]){
							var $scope = angular.element('[ng-controller=listicleController]').scope();
							var indexPos = $(this).closest('.eb-listicle-item').index('.eb-listicle-item');
							$scope.listicleItems[indexPos].image_str = data.result.url;
							$scope.listicleItems[indexPos].image_id = data.result.id;
							$scope.$apply();
						}

	        	var $scope = angular.element('[ng-controller=editorController]').scope();
						if(!$(this).closest('.eb-listicle')[0]){
							$scope.data.image = {id: data.result.id, url: data.result.url, name: data.result.name};
						}
		        $scope.uploading = false;
		        $scope.$apply();

		        // ------------------------------------------------------------------------

	            // DOM manipulation
	            // ------------------------------------------------------------------------

	            $dropZone.find('.option-url').show();
	            $dropZone.siblings('.remove').show();

	            // On result
	            // ------------------------------------------------------------------------

	            if (data.result.error) {
	                data.errorThrown = data.result.error_description || data.result.error;
	                that._trigger('fail', e, data);
	            } else {
	            	// DOM manipulation
	            	// ------------------------------------------------------------------------

					context.remove();

					$dropZone.removeClass('on-progress').addClass('on-preview');
	            }
			},

			fail: function(e, data) {
				var that 		= $(this).data('blueimpUIFileupload'),
					$progress 	= data.context.find('.progressbar'),
					$parent 	= data.context,
					$ancestor   = data.context.parent('.fileupload-pool'),
					fid;

		        // ------------------------------------------------------------------------

		        var $scope = angular.element('[ng-controller=editorController]').scope();
		        $scope.uploading = false;
		        $scope.$apply();

		        // ------------------------------------------------------------------------

				// DOM manipulation
				// ------------------------------------------------------------------------

	    		$progress.removeClass('progress-info').addClass('progress-danger');
	    		$parent.addClass('has-status')
	    			   .append(
	    			   		$('<div />', {'class': 'status'}).text(data.errorThrown)
	    			   		.append($('<a />', {'class': 'error-close'}).append($('<i />', {'class': 'keepo-icon icon-cancel'})))
	    			   	);

	    		$parent.on('click', 'a.error-close', function(e) {
	    			e.preventDefault();

	    			$parent.remove();
	    			$ancestor.removeClass('on-progress');
	    			$ancestor.find('.helper').show();
	    			$ancestor.find('.drop_target').show();
	    		});

	    		// Remove input hidden
	    		// ------------------------------------------------------------------------

	    		//fid = 'fid_' + $(this).data('id');
	    		data.form.find('input[type=hidden][name=fid]').remove();
	    		data.form.find('input[type=hidden][name=fn]').remove();
			}
		};

		fileUploadOptions = $.extend(fileUploadOptions, options);
		$element.fileupload(fileUploadOptions);
	}

module.exports = _initFileUpload;