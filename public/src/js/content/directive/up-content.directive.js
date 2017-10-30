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
			data      = _content,
			picker;
		
		scope.created = data.created;

		picker = element.find('[datetime-picker]').datetimepicker({
			format        : 'Y-m-d H:i:s',
			timepicker    : false,
			inline 		  : true,
			startDate     : data.created,//or 1986/12/08
			formatDate    : 'd M y',
			regional      : 'fr',
			onChangeDateTime : (current_time, $input) => {
				// We handle at PHP sorry, .. about this
				scope.created = current_time.toLocaleDateString();
				console.log( current_time.toLocaleDateString() )

			},
			onSelectDate: (a, b) => {
				console.log( a, b )
			},
			onClose : (current_time, $input) => {
				console.log( current_time );
			}
		});

		scope.dateNow = () => {
			console.log( picker.datetimepicker );
			picker.datetimepicker().datetimepicker('setDate', 'today');
		}
	}
}

export default Created;