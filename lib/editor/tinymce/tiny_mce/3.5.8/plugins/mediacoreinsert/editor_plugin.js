/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/   
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___   
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/   
 *                      TinyMCE Plugin
 *
 */

(function(tinymce) {
	tinymce.create('tinymce.plugins.mediacoreinsert', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceMoodleMediaCore', function() {
				ed.windowManager.open({
					file : url + '/mediacore.php',
					height: 470,
					width: 460,
					inline : 1,
					popup_css : false
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('mediacoreinsert', {
									title : 'MediaCore', 
									  cmd : 'mceMoodleMediaCore',
									image : url + "/mcore-icon.png",
								});
		},

		getInfo : function() {
			return {
				longname : 'MediaCore',
				version : '1'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('mediacoreinsert', tinymce.plugins.mediacoreinsert);
})(tinymce);