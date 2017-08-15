/*
 * jQuery File Upload User Interface Plugin 7.4
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, unparam: true, regexp: true */
/*global define, window, URL, webkitURL, FileReader */

(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        // Register as an anonymous AMD module:
        define([
            'jquery',
            'vendor/bluimp-upload/jquery.ui.widget',
            'vendor/bluimp-upload/jquery.iframe-transport',
            'vendor/bluimp-upload/jquery.fileupload'
        ], factory);
    } else {
        // Browser globals:
        factory(
            window.jQuery
        );
    }
}(function ($) {
    'use strict';

    // The UI version extends the basic fileupload widget and adds
    // a complete user interface based on the given upload/download
    // templates.
    $.widget('blueimpUI.fileupload', $.blueimp.fileupload, {

        options: {
            // By default, files added to the widget are uploaded as soon
            // as the user clicks on the start buttons. To enable automatic
            // uploads, set the following option to true:
            autoUpload: true,
            // The following option limits the number of files that are
            // allowed to be uploaded using this widget:
            //maxNumberOfFiles: 1,
            // The maximum allowed file size:
            maxFileSize: 1000000,
            // The minimum allowed file size:
            minFileSize: 1,
            // To limit the number of files uploaded with one XHR request,
            // set the following option to an integer greater than 0:
            //limitMultiFileUploads: 1,
            // To limit the number of concurrent uploads,
            // set the following option to an integer greater than 0:
            //limitConcurrentUploads: 1,
            // The regular expression for allowed file types, matches
            // against either file type or file name:
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png|bmp)$/i, // /(gif|jpeg|png|bmp)$/i,
            // The container for the list of files. If undefined, it is set to
            // an element with class "files" inside of the widget element:
            filesContainer: undefined,
            // By default, files are appended to the files container.
            // Set the following option to true, to prepend files instead:
            prependFiles: false,
            // The expected data type of the upload response, sets the dataType
            // option of the $.ajax upload requests:
            dataType: 'json',

            // The add callback is invoked as soon as files are added to the fileupload
            // widget (via file input selection, drag & drop or add API call).
            // See the basic file upload widget for more information:
            add: function (e, data) {
                var that = $(this).data('blueimpUIFileupload'),
                    options = that.options,
                    files = data.files;

                    that.options.fid = $(this).data('id');

                    that._adjustMaxNumberOfFiles(-files.length);
                    data.isAdjusted = true;
                    data.files.valid = data.isValidated = that._validate(files);

                    if (! data.files.valid){
                        that._showError(data.files);
                        return false;c
                    }

                    data.context = that._renderTemplate(data.files).data('data', data);

                    if ((that._trigger('added', e, data) !== false) &&
                            (options.autoUpload || data.autoUpload) &&
                            data.autoUpload !== false && data.isValidated) {

                        data.submit();
                    }
            },
            // Callback for the start of each file upload request:
            send: function (e, data) {

            },
            // Callback for successful uploads:
            done: function (e, data) {
                var that        = $(this).data('blueimpUIFileupload'),
                    $dropZone   = that.options.dropZone,
                    context     = $(data.context);

                if (data.result.error) {
                    data.errorThrown = data.result.error;
                    that._trigger('fail', e, data);
                } else {
                    // Set bar
                    context.find('.progressbar').
                            removeClass('progress-info').
                            removeClass('active').
                            addClass('progress-success').
                            find('.bar').
                            css({'width': '100%'});

                    context.remove();
                    $dropZone.find('.helper_text').show();
                    $dropZone.find('.drop_target').show();

                    var fid = 'fid_' + $(this).data('id');
                    if ($dropZone.find('input[type=hidden][namce=' + fid + ']').length > 0){
                        $dropZone.find('input[type=hidden][name=' + fid + ']').val(data.result.fid);
                    }else{
                        var $input = $('<input />', {'type': 'hidden', 'name': fid}).val(data.result.fid);
                        $dropZone.append($input);
                    }

                    // Render Preview
                    that._renderPreview(data.result, context);
                }
            },
            // Callback for failed (abort or error) uploads:
            fail: function (e, data) {
                var that        = $(this).data('blueimpUIFileupload'),
                    $dropZone   = that.options.dropZone,
                    $poolParent = $dropZone.parent('div');

                $poolParent.prepend($('<div />', {'class': 'upload-status alert-message error'}).text(data.errorThrown));

                data.context.remove();
                $dropZone.find('.helper_text').show();
                $dropZone.find('.drop_target').show();

                var fid = 'fid_' + $(this).data('id');
                data.form.find('input[type=hidden][name=' + fid + ']').remove();

            },
            // Callback for upload progress events:
            progress: function (e, data) {
                /*var $dropZone = data.dropZone,
                    percent = parseInt(data.loaded / data.total * 100, 10);

                    $dropZone.find('div.progressbar').find('div.bar').css({'width': percent + '%'});
                    $dropZone.find('span.status').html('uploading...' + percent + '%');*/

                var that = $(this).data('blueimpUIFileupload'),
                    percent = parseInt(data.loaded / data.total * 100, 10);

                data.context.find('div.progressbar').find('div.bar').css({'width': percent + '%'});
            }
        },

        _adjustMaxNumberOfFiles: function (operand) {
            if (typeof this.options.maxNumberOfFiles === 'number') {
                this.options.maxNumberOfFiles += operand;
                /*if (this.options.maxNumberOfFiles < 1) {
                    this._disableFileInputButton();
                } else {
                    this._enableFileInputButton();
                }*/
            }
        },

        _create: function () {
            if (! _.isNull(this.options.dropZone)) {
                $.blueimp.fileupload.prototype._create.call(this);
                return;
            }

            // Set dropZone
            this.options.dropZone = this.element.closest('.fileuploadpool');

            // Bind remove uploaded
            var that        = this,
                $poolParent = this.options.dropZone.parent('div'),
                $dropZone   = this.options.dropZone;

            // Append remove button
            $poolParent.on('click', '#post-media a.icon-remove', function(e) {
                e.preventDefault();

                $dropZone.find('input[type=hidden][name^=fid_]').remove();
                $poolParent.find('#post-media').remove();

                // Show drop zone
                $dropZone.show();
            });

            $.blueimp.fileupload.prototype._create.call(this);
        },

        _initEventHandlers: function () {
            $.blueimp.fileupload.prototype._initEventHandlers.call(this);
            var eventData = {fileupload: this};

            this.options.dropZone
                .delegate(
                    '.remove',
                    'click.' + this.options.namespace,
                    eventData,
                    this._cancelHandler
                );
        },

        _cancelHandler: function (e) {
            e.preventDefault();
            var tmpl = $(this).hasClass('fileupload_cancel') ? e.data.fileupload.options.dropZone.find('li') : $(this).closest('li'),
                data = tmpl.data('data') || {};

            if (!data.jqXHR) {
                data.errorThrown = 'abort';
                e.data.fileupload._trigger('fail', e, data);
            } else {
                data.jqXHR.abort();
            }

            tmpl.fadeOut(300, function(){ $(this).remove(); });
        },

        _formatFileSize: function (bytes) {
            if (typeof bytes !== 'number') {
                return '';
            }
            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }
            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }
            return (bytes / 1000).toFixed(2) + ' KB';
        },

        _hasError: function (file) {
            if (file.error) {
                return file.error;
            }

            // The number of added files is subtracted from
            // maxNumberOfFiles before validation, so we check if
            // maxNumberOfFiles is below 0 (instead of below 1):
            if (this.options.maxNumberOfFiles < 0) {
                return 'maxNumberOfFiles';
            }
            // Files are accepted if either the file type or the file name
            // matches against the acceptFileTypes regular expression, as
            // only browsers with support for the File API report the type:
            if (!(this.options.acceptFileTypes.test(file.type) ||
                    this.options.acceptFileTypes.test(file.name))) {
                return 'acceptFileTypes';
            }
            if (this.options.maxFileSize &&
                    file.size > this.options.maxFileSize) {
                return 'maxFileSize';
            }
            if (typeof file.size === 'number' &&
                    file.size < this.options.minFileSize) {
                return 'minFileSize';
            }
            return null;
        },

        _validate: function (files) {
            var that = this,
                valid = !!files.length;

            $.each(files, function (index, file) {
                file.error = that._hasError(file);
                if (file.error) {
                    valid = false;
                }
            });
            return valid;
        },

        _renderTemplate: function(files) {
            var $dropZone   = this.options.dropZone,
                $dropTarget = $dropZone.find('.drop_target'),
                $helper     = $dropZone.find('.helper'),
                $uploading  = $('<div />', {'class': 'uploading'}),
                $progress   = $('<div />', {'class': 'progressbar progress active progress-info progress-striped'}).append($('<div />', {'class': 'bar', 'style': 'width:0%'}));

            //$helper.hide();
            //$dropZone.parent('div').find('.upload-status').remove();
            //$helper.find('.uploaded, .uploading').remove();
            $dropZone.find('.uploaded, .uploading').remove();
            $dropZone.addClass('on-progress').removeClass('over');

            if (! $dropTarget.hasClass('option-fileupload')) {
                //$dropTarget.hide();
            }


            $.each(files, function(index, file) {
                $uploading.append($progress);

                $dropZone.append($uploading);
            })

            return $uploading;
        },

        _renderPreview: function(result, node) {
            var that        = this,
                $dropZone   = that.options.dropZone,
                $poolParent = $dropZone.parent('div'),
                $preview    = $('<div />', {'id': 'post-media'}).append('<span><a class="icon-remove"></a></span>');

            $preview.find('span').append(result.img);

            $poolParent.find('#post-media').remove();
            $poolParent.prepend($preview);

            // Hide drop zone
            $dropZone.hide();



            // $(result.img).one('load', function() {
            //    $preview.find('span').addClass('shadow-effect');
            // });
        },

        _showError: function(files) {
             $.each(files, function (index, file) {
                 switch(file.error) {
                    case 'acceptFileTypes':
                        alert('File type not accepted!');
                        break;
                    case 'maxFileSize':
                        alert('File size to big!');
                        break;
                    case 'minFileSize':
                        alert('File size to small!');
                        break;
                }
             });
        }

    });

}));
