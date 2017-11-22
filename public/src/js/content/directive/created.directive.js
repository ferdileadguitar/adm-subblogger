import 'style-loader!css-loader!eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css';
import datetimepicker from 'eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker';
import ArticleEditors from './article.directive';

class Created extends ArticleEditors{

	constructor($http, $rootScope, $timeout, $sce, $q, appFactory, appService) {
		super($http, $rootScope, $timeout, $sce, $q, appFactory, appService);
		this.restrict = 'A';
	}

	link(scope, element, attrs) {
		
		let _base     = this._$scope.allPosts,
		    _content  = _base.post,
			data      = _content, picker;
		
		scope.created = data.created;

		picker = element.find('[datetime-picker]').datetimepicker({
			format          : 'YYYY-MM-DD HH:mm',
			inline 		    : true,
			maxDate         : 'now',
			defaultDate     : data.created,
			// todayHightlight : true
		});

		picker.on('dp.change', (e) => { scope.created = picker.val(); });
	}
}

export default Created;