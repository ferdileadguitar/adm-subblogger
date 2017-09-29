import ArticleEditors from './article.directive';

class tagsEditors extends ArticleEditors {

	constructor() {
		super();

		this.restrict = 'A';
	}

	link(scope, element, attrs) {
		
	}
}

export default tagsEditors;