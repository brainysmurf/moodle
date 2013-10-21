/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {

	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('buttons');

	tinymce.create('tinymce.plugins.ButtonsPlugin', {
	
		openDialog: function(ed, url, currentContent) {
		
			ed.windowManager.open({
				file : url + '/dialog.php',
				width : 640,
				height : 320,
				inline : 1
			}, {
				plugin_url : url,
				currentContent: currentContent
			});
			
		},
		
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
		
			//Reference to this class (as we can't use 'this' keyword in callbacks)
			var t = this;
			
			//Set the url as we'll need it later in the openDialog function
			this.url = url;
		
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceButtons');
			ed.addCommand('mceButtons', function() {
				t.openDialog(ed,url);
			});

			// Create button for toolbar
			ed.addButton('addbuttons', {
				title : 'buttons.desc',
				cmd : 'mceButtons',
				image : url + '/img/buttons.png'
			});
					
			//Prevent editing the button text directly inside the tinymce editor - we want to open the dialog instead
			ed.onInit.add(function(ed)
			{
				ed.dom.setAttrib(tinyMCE.activeEditor.dom.select('ul.buttons'),'contenteditable','false');
				ed.dom.setAttrib(tinyMCE.activeEditor.dom.select('ul.buttons'),'data-mce-contenteditable','false');
			});
			
			//Remove the contenteditable attribute when submitting so it doesn't get saved in the db
			ed.onSubmit.add(function(ed) {
           		ed.dom.setAttrib(tinyMCE.activeEditor.dom.select('ul.buttons'),'contenteditable',null);
           		ed.dom.setAttrib(tinyMCE.activeEditor.dom.select('ul.buttons'),'data-mce-contenteditable',null);           		
			});
			
			//Open button creation dialog if user clicks on a ul.buttons in the editor
			ed.onClick.add(function(ed, e) {
				if ( buttons = t.isButtons(e.target) )
				{
					//Remove them as they are - they'll get readded afterwards
					buttons.parentNode.removeChild(buttons);
					t.openDialog(ed,t.url ,e.target.innerHTML);
				}
			});
											
		},
		
		//Checks if the given element or one of its parents is ul.buttons
		isButtons: function(element) {
		
			if ( element.id == 'tinymce' )
			{
				//Checking through parents reached the #tinymce root. Don't go any further
				return false;
			}
		
			if ( element.tagName == 'UL' && element.className == 'buttons' )
			{
				//Element is what we were looking for!
				return element;
			}
			
			//Check the element's parent
			return this.isButtons(element.parentNode);
		},
		
		

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Buttons plugin',
				author : 'Anthony Kuske',
				authorurl : 'http://www.anthonykuske.com',
				infourl : '#',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('buttons', tinymce.plugins.ButtonsPlugin);	
	
})();