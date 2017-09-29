import thisFileUpload from './../service/bluimp-fileupload.service.js';

class ImgCover {
	
	constructor($http, $rootScope, $timeout, $sce, $q, appFactory, appService) {
		'ngInject';

		this.restrict       = 'A';
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
 	
	static _imgCover(element, obj, options) {

		return {
			obj    : obj.data,
			method : 'PUT',
			params : '/set-img-cover'
		};
	}

	link(scope, element, attrs) {
		var mainClass  = ImgCover,
			_base      = this._$scope,
			self       = this;

		scope.uploading = false;
		scope.onProgress= false;
		scope.data      = angular.copy(_base.allPosts.post);

		// Save
		scope.saveClick = (type) => {
			if( scope.onProgress || scope.uploading ) { return false }

			let options = { type : type };
			let data    = {};
			
			try{
				data = mainClass._imgCover(element, _.extend(scope, options));

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
			} finally {
			}
			
		};

		// ------------------------------------------------------------------------

		// Browse Cover Picture File
		scope.browseFile =  (event) => {
			var $el = $(event.currentTarget || event.srcElement);

			$el.parent('.fileupload-pool').find('.file-upload').trigger('click');
		};

		// ------------------------------------------------------------------------

		// Remove Preview
		scope.removePreview = (event) => {
			var $el = $(event.currentTarget || event.srcElement);
			var id = $($el).closest('.on-preview').find("input[name='fid']").val();
			if(!$($el).closest('.eb-listicle-item')[0]){
				scope.data.image.id = void 0;
			}
			if(!scope.data.images){
				scope.data.images = [];
			}
			scope.data.images.push({id: id, destroy: true});
		};

		// ------------------------------------------------------------------------

		scope.feedsAssignObject = (newData) => {
			var _data = [],
				ids   = scope.$parent.ids;
				console.log( ids, newData, _base.allPosts.data );
				scope.$apply(() => {

					if( _.has(newData, 'content') ) {
						newData.content = JSON.stringify(newData.content);
					}
						
					_.extend(_base.allPosts.data[ids], newData);
				});
				
				$('body').find('.mdl.mdl-editor').remove();

				scope.$on('mdl_data', (event, args) => {
					console.info( args );
				});
		}

		// ------------------------------------------------------------------------
		this._$timeout(() => {
			if (element.find('.fileupload-pool.cover-picture').length) {
				thisFileUpload._initFileUpload(
						element.find('.fileupload-pool.cover-picture input[type=file]'), 
						{
							dropZone: element.find('.fileupload-pool.cover-picture'), 
							uploadURL: thisFileUpload.uploadCoverUrl
						},
						scope);
			}
		}, 50);
	}
}
export default ImgCover;