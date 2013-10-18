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
	
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
		
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceButtons');
			ed.addCommand('mceButtons', function() {
				ed.windowManager.open({
					file : url + '/dialog.htm',
					width : 640,
					height : 320,
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					//some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register example button
			ed.addButton('addbuttons', {
				title : 'buttons.desc',
				cmd : 'mceButtons',
				image : url + '/img/buttons.png'
			});
						
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