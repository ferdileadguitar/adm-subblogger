function extend(b, a) {
    var prop;
    if (b === undefined) {
        return a;
    }
    for (prop in a) {
        if (a.hasOwnProperty(prop) && b.hasOwnProperty(prop) === false) {
            b[prop] = a[prop];
        }
    }
    return b;
}

function titleEditorApp(element, options) {
	this.init(element, options);
}

titleEditorApp.prototype = {
	$el : $('.title-holder'),
	defaults: {
		placeholder : 'Title'
	},

	_bind: function() {
		var self = this;

		if (! this.$el.length) { return; }

		// ------------------------------------------------------------------------

		this.$el.bind('clickoutside', function() {
			if (self.$el.text().replace(/\s+/, '') == '') {
				$(this).addClass('empty-field');
			}
		});

		this.$el.bind('click', function() {
			$(this).removeClass('empty-field');
		});

		this.$el.bind('paste', function(e) {
			//e.preventDefault();
			//document.execCommand("insertHTML", false, e.clipboardData.getData("text/plain"));

			if (e.originalEvent && e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
                e.preventDefault();
                window.document.execCommand('insertText', false, e.originalEvent.clipboardData.getData('text/plain'));
            }
            else if (e.clipboardData && e.clipboardData.getData) {
                e.preventDefault();
                window.document.execCommand('insertText', false, e.clipboardData.getData('text/plain'));
            }
            else if (window.clipboardData && window.clipboardData.getData) {
                // Stop stack overflow
                if (!_onPaste_StripFormatting_IEPaste) {
                    _onPaste_StripFormatting_IEPaste = true;
                    e.preventDefault();
                    window.document.execCommand('ms-pasteTextOnly', false);
                }
                _onPaste_StripFormatting_IEPaste = false;
            }
		});
	},

	init: function(element, options) {
		this.$el     = element;
		this.options = extend(options, this.defaults);

		// ------------------------------------------------------------------------

		this.$el.attr('contenteditable', true).
				 attr('data-placeholder', this.options.placeholder).
				 addClass('title-editor-app');

		if (this.$el.text().replace(/\s+/, '') == '' || ((/^<@/).test(this.$el.text()) && (/@>$/).test(this.$el.text()))) {
			this.$el.addClass('empty-field');
		}

		// ------------------------------------------------------------------------

		this._bind();
	}
};

module.exports = { titleEditorApp, extend };