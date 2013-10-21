tinyMCEPopup.requireLangPack();

var ButtonsDialog = {

	existingContent: false,
	cancelling: true, //Upon closing the window, the default action is to undo changes

	init : function() {
		var t = this;
		
		//Editing an existing list
		if ( tinyMCEPopup.params.currentContent )
		{
			var ul = document.createElement('ul');
			ul.innerHTML = tinyMCEPopup.params.currentContent;
			
			this.existingContent = ul;
			
			lis = ul.getElementsByTagName('li');
			
			Array.prototype.forEach.call(lis, function(li, index, nodeList)
	        {
				var url = li.childNodes[0].getAttribute('href');
				var label = li.childNodes[0].textContent;
				var icons = li.getElementsByTagName('i');

				if ( icons.length > 0 )
				{
					var icon = icons[0].className;
				}
				else
				{
					var icon = false;
				}
				
				t.addRow(url,icon,label);
			});
		}
		else
		{
			this.existingContent = false;
			//New list
			//Add one row (button) by default
			this.addRow();
		}
	},
	
	//Return all the icons available
	getIcons: function() {
		var options = '<option value="">Select Icon</option>';
		
		for ( var i in fontawesomeIcons )
		{
			var niceName = i.replace('icon-','');
			options += '<option value="'+i+'">'+niceName+'</option>';
		}
		
		return options;
	},
	
	//Add a row (button)
	addRow: function(url, icon, text) {
		if ( !url ) { url = ''; }
		if ( !text ) { text = ''; }
		
		var li = document.createElement('li');
		li.className = 'buttonRow';
		li.innerHTML = '<input type="text" name="url" placeholder="Link (e.g. http://google.com) (Optional)" class="url" value="'+url+'" /><select name="icon" style="font-family:FontAwesome;" onchange="ButtonsDialog.changeIcon(this);">'+this.getIcons()+'</select><span class="iconPreview"></span><input type="text" name="text" class="text" placeholder="Button Text" value="'+text+'" onfocus="this.className=\'text\';"  /><button class="updateButton" onclick="ButtonsDialog.removeRow(this);">Remove</button>';
		
		if ( icon )
		{
			var iconSelect = li.getElementsByTagName('select')[0];
			var iconPreview = li.getElementsByClassName('iconPreview')[0];
			Array.prototype.forEach.call(iconSelect, function(option, index, nodeList)
	        {
	        	if ( option.value == icon )
	        	{
	        		iconSelect.selectedIndex = index;
	        		iconPreview.innerHTML = '<i class="'+icon+'"></i>';
	        		//Would break here but you can't do that in forEach
	        	}
	        });
		}
		
		document.getElementById('buttonList').appendChild(li);
	},
	
	changeIcon: function(select) {
		var value = select.value;
		if ( !value ) { value = ''; }
		select.parentNode.getElementsByClassName('iconPreview')[0].innerHTML = '<span class="'+value+'"></span>';
	},
	
	removeRow: function(button) {
		var li = button.parentNode;
		li.parentNode.removeChild(li);
	},
	
	getOutput: function() {
		var rows = document.getElementsByClassName('buttonRow');
		if ( rows.length < 1 ) { return ''; }
			
		var out = '<ul class="buttons" contenteditable="false" data-mce-contenteditable="false">';
			
			var BreakException= {};
			
			try {
			Array.prototype.forEach.call(rows, function(row, index, nodeList)
	        {
				var url = row.children[0].value;	
				var icon = row.children[1].value;
				var label = row.children[3].value;
				
				if ( !label )
				{
					row.children[3].className += ' error';
					alert('Please enter some text for every button, or remove buttons you don\'t want to show.');
					throw new BreakException();
				}
				
				out += '<li>';
					out += url ? '<a href="'+url+'" class="btn">' : '<span class="btn noclick">';
					
						if ( icon )
						{
							out += '<i class="'+icon+'"></i> ';
						}
						
						out += label;
					
					out += url ? '</a>' : '</span>';
					
				out += '</li>';
			});
			}
			catch(e)
			{
				if (e!==BreakException) throw e;
			}
			
		out += '</ul>';
		
		return out;
	},
	
	onClose: function() {	
		alert('dialog.js onClose');
	},

	undo: function() {
		if ( this.existingContent )
		{
			//When tinymce gave us the content, the class was removed from the <ul>, so put that back
			this.existingContent.className = 'buttons';
			this.existingContent.setAttribute('contenteditable',false);
			this.existingContent.setAttribute('data-mce-contenteditable',false);	
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, this.existingContent.outerHTML);
		}
	},

	cancel: function() {
		//The undo functionality was here, but it's moved to the window.onClose function instead
		tinyMCEPopup.close();
	},
	
	insert : function() {
		this.cancelling = false;
		var output = this.getOutput();

		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, output);
		tinyMCEPopup.close();
	},
	
};

tinyMCEPopup.onInit.add(ButtonsDialog.init, ButtonsDialog);

window.onClose = function(){
	if ( ButtonsDialog.cancelling )
	{
		ButtonsDialog.undo();
	}
}