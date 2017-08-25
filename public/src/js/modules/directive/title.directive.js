import ArticleEditors from './article.directive';

class titleEditors extends ArticleEditors {

	constructor() {
		super();
		this.restrict = 'A';
	}


	link(scope, element, attrs) {

		this._prepSave = pushSave;
		
		// let pushSave;
		function pushSave() {
			return 'this from title';
		}
	}
}

// ArticleEditors.prototype.constructor.call = {

// 	titleSave() {
// 		console.log( 'This is title save' );
// 	}
// }

export default titleEditors;