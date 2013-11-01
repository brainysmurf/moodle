/*
	For reordering tabs in the oneropic format
*/

$(function(){

	function enableReordering()
	{
		//Add the delete section button
		$('.tabs .selected').each(function()
		{
			var section = $(this).attr('data-section'); 
			if ( section == 0 )
			{
				$(this).append('<a class="btn delete_section" href="#" title="You can\'t delete the first section" onclick="return false;"><i class="icon-ban-circle"></i></a>');
			}
			else
			{
				var url = '/course/delete_section.php?courseid='+courseID+'&amp;section='+section;
				$(this).append('<a class="btn delete_section" href="'+url+'" title="Delete this section"><i class="icon-trash"></i></a>');	
			}
		});
		
		$('#reorderSectionsButton').addClass('selected');
		$('.tabs').prepend('<div id="reorderSectionsAlert" class="local-alert"><i class="icon-move pull-left"></i> Drag and drop tabs (sections) to rearrange them. Your changes will be saved automatically.<br/><span class="small"><strong>Note:</strong> the first section cannot be moved.</span></div>');
		$('.tabs > ul').sortable({
			items: 'li:not(:last-child):not(:first-child)', //skip section 0, and the add tab button
			scroll: false ,
			placeholder: 'sortable-placeholder' ,
			forcePlaceholderSize: true ,
			revert:100 ,
			
			//When user starts dragging
			start: function(event, ui) {
				$(this).attr('data-oldindex', ui.item.index());
			},
			
			//When user has finished dragging and the order has changed
			update: function(event, ui) {
				var oldPos = parseInt( $(this).attr('data-oldindex') );
				var newPos = ui.item.index();
				moveSection( oldPos , newPos );
			}
			
		});
	}

	function moveSection( oldPos , newPos )
	{
		$('.tabs > ul').sortable('disable');
		$('#reorderSectionsAlert .small').html('<i class="icon-spinner"></i> Saving...');
		$.post('/course/ajax/move_section.php' , {courseid: courseID , oldPos:oldPos , newPos:newPos} , function(res)
		{
			if ( res.error )
			{
				alert(res.error);
			}
			else if ( res.success )
			{
				$('.tabs > ul').sortable('enable');
				$('#reorderSectionsAlert .small').html('<i class="icon-ok"></i> Changes saved');
			}
		});
	}

	function disableReordering()
	{
		$('#reorderSectionsButton').removeClass('selected');
		$('#reorderSectionsAlert').slideUp(function(){ $(this).remove(); });
		$('.tabs > ul').sortable('destroy');
		//Hide delete buttons
		$('.tabs .delete_section').remove();
	}
	
	$(document).on('click','#reorderSectionsButton',function()
	{
		if ( $(this).hasClass('selected') )
		{
			disableReordering();
		}
		else
		{
			enableReordering();
		}
		return false;
	});
	
});