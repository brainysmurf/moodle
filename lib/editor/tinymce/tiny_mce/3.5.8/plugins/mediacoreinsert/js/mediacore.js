tinyMCEPopup.requireLangPack();

var MediaCoreDialog = {
	init : function(ed) {
		tinyMCEPopup.resizeToInnerSize();
	},

	insert : function(file, title) {
		var ed = tinyMCEPopup.editor, dom = ed.dom;

		tinyMCEPopup.execCommand('mceInsertContent', false, dom.createHTML('a', {
			href : file
		}, title));

		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(MediaCoreDialog.init, MediaCoreDialog);