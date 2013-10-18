tinyMCEPopup.requireLangPack();

var ButtonsDialog = {

	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		//f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		//f.somearg.value = tinyMCEPopup.getWindowArg('some_custom_arg');
		
		this.addRow();
	},
	
	getIcons: function() {
		var options = '<option value="">Select Icon</option><option>icon-eur</option><option>icon-dropbox</option><option>icon-cny</option>';
		return options;
	},
	
	addRow: function() {
		var row = '<li class="buttonRow"><input type="text" name="url" placeholder="Link" class="url" /><select name="icon">'+this.getIcons()+'</select><input type="text" name="text" class="text" placeholder="Button Text"</li>';
		document.getElementById('buttonList').innerHTML += row;
	},
	
	getOutput: function() {
		var out = '<ul class="buttons">';
			
			var rows = document.getElementsByClassName('buttonRow');
			
			console.log(rows);

			Array.prototype.forEach.call(rows, function(row, index, nodeList)
	        {
				console.log(row);
				console.log(row.children);
				console.log(row.children[0]);
				
				var url = row.children[0].value;
				
				var icon = row.children[1].value;
				var label = row.children[2].value;
				
				out += '<li>';
					out += url ? '<a href="'+url+'">' : '<span>';
					
						if ( icon )
						{
							out += '<i class="'+icon+'"></i> ';
						}
						
						out += label;
					
					out += url ? '</a>' : '</span>';
					
				out += '</li>';
			});
			
		out += '</ul>';
		
		return out;
	},

	insert : function() {
		var output = this.getOutput();
		console.log(output);

		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, output);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(ButtonsDialog.init, ButtonsDialog);
