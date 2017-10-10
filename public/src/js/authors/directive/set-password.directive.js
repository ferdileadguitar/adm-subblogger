class PasswordEditors {

	constructor($timeout, $rootScope, appService){
		'ngInject';

		this._$timeout    = $timeout;
		this._$scope      = $rootScope;
		this.appService   = appService;
	}

	get $inject() {
		return ['$timeout', '$rootScope', 'appService'];
	}

	link(scope, element, attrs) {
		let _base      = this._$scope,
			_content   = _base.allAuthors.author,
			self       = this;

		scope.data = {
			password :  {
				new    : "",
				reType : ""
			},
		};

		scope.notify =  {
			success : {
				active :false,
				msg    :void 0
			},

			error : {
				active :false,
				msg    :void 0
			},

			close : () => {
				scope.notify.error.active = false
			}
		};

		scope.btnRemove = {
			float : 'right',
			color : '#f4adb2'
		};

		scope.save = (data) => {

			this.appService.post({'url': 'set-password'}, _.extend(_base.allAuthors.filters, {password: data.password.new, id: _content.id}))
			  .then(function(res) {
			  		if( _.contains(['ok'], res.status) )
			  			$('body').find('.mdl.mdl-editor').remove();
			  }, function(err) {
			  		console.error(err.data.error_description);
			  		scope.notify.error = {
						active : true,
						msg    : err.data.error_description
					}
			  });
		};

		scope.saveClick = (data) => {
			
			// Validate here
			if (_.isEmpty(data.password.new) || _.isEmpty(data.password.reType)) 
			{
				scope.notify.error = {
					active : true,
					msg    : 'Please complete the form '
				}
				return false;	
			}

			// Not same ...
			if(!_.isEqual(data.password.new, data.password.reType ))
			{
				scope.notify.error = {
					active : true,
					msg    : 'The password you entered is not the same '
				}
				return false;
			}
			// Go ..save here
			scope.save(data);
		};


	}
}

export default PasswordEditors;