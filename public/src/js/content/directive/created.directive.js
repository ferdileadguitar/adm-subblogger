import 'style-loader!css-loader!jquery-datetimepicker/jquery.datetimepicker.css';
import 'jquery-datetimepicker';
import ArticleEditors from './article.directive';

class Created extends ArticleEditors{

	constructor($http, $rootScope, $timeout, $sce, $q, appFactory, appService) {
		super($http, $rootScope, $timeout, $sce, $q, appFactory, appService);
		this.restrict = 'A';
	}

	link(scope, element, attrs) {

		$.datetimepicker.setLocale('en');
		
		let _base     = this._$scope.allPosts,
		    _content  = _base.post,
			data      = _content;
		
		scope.created = data.created;

		element.find('[datetime-picker]').datetimepicker({
			format        : 'Y-m-d H:i:s',
			timepicker    : false,
			inline 		  : true,
			startDate     : data.created,//or 1986/12/08
			formatDate    : 'd M y',
			regional      : 'fr',
			todayButton   : false,
			maxDate       : true,
			onChangeDateTime : function(current_time, $input) {
				// We handle at PHP sorry, .. about this
				console.log( $input[0].value )
				scope.created = $input[0].value;

			},
			onClose : function(current_time, $input) {
				console.log( current_time );
			}
		});
	}
}

export default Created;