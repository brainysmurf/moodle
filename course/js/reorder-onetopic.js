/*
	For reordering tabs in the oneropic format
*/

$(function(){

	function enableReordering()
	{
		$('#reorderSectionsButton').addClass('selected');
		$('.tabtree .tabrow0').before('<div id="reorderSectionsAlert" class="local-alert"><i class="icon-move pull-left"></i> Drag and drop tabs (sections) to rearrange them. Your changes will be saved automatically.<br/><span class="small"><strong>Note:</strong> the first section cannot be moved.</span></div>');
		$('.tabtree .tabrow0').sortable({
			items: 'li:not(:last-child):not(:first-child)',
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
				console.log('oldPos',oldPos,'newPos',newPos);
				moveSection( oldPos , newPos );
			}
			
		});
	}

	function moveSection( oldPos , newPos )
	{
		$('.tabtree .tabrow0').sortable('disable');
		$('#reorderSectionsAlert .small').html('<i class="icon-spinner"></i> Saving...');
		$.post('/course/ajax/move_section.php' , {courseid: courseID , oldPos:oldPos , newPos:newPos} , function(res)
		{
			if ( res.error )
			{
				alert(res.error);
			}
			else if ( res.success )
			{
				$('.tabtree .tabrow0').sortable('enable');
				$('#reorderSectionsAlert .small').html('<i class="icon-ok"></i> Changes saved');
			}
		});
	}

	function disableReordering()
	{
		$('#reorderSectionsButton').removeClass('selected');
		$('#reorderSectionsAlert').slideUp(function(){ $(this).remove(); });
		$('.tabtree .tabrow0').sortable('destroy');
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