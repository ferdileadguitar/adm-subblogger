import 'style-loader!css-loader!jquery-datetimepicker/jquery.datetimepicker.css';
import 'jquery-datetimepicker';
import ArticleEditors from './article.directive';

class Created extends ArticleEditors{

	constructor() {
		super();
		this.restrict = 'A';
	}

	controller($scope, $element, $timeout) {
		$.datetimepicker.setLocale('en');
		var data = $scope.data;
		console.log( data );
		$element.find('[datetime-picker]').datetimepicker({
			// format:'d.m.Y H:i',
			timepicker    : false,
			inline 		  :true,
			startDate     :data.created,//or 1986/12/08
			formatDate    : 'd M y',
			onChangeDateTime : function(current_time, $input) {
				var $Date = new Date;
				console.log( current_time, $Date.getTime() );
			}
		});
	}
}

export default Created;