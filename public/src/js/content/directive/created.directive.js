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
			data      = _content, picker,
			todayDate = new Date().getDate();
		
		scope.created = data.created;

		picker = element.find('[datetime-picker]').datetimepicker({
			format          : 'YYYY-MM-DD HH:mm',
			inline 		    : true,
			maxDate         : new Date(new Date().setDate(todayDate + 1)),
			defaultDate     : data.created,
			sideBySide      : true
		});

		picker.on('dp.change', (e) => { scope.created = picker.val();console.info(scope.created) });
	}
}

export default Created;