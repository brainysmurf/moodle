$(document).on('click', '.becomeActivityManagerList a', function(e)
{
	e.preventDefault();

	var courseID = $(this).attr('data-courseid');
	var courseName = $(this).attr('data-fullname');

	var desc = $(this).find('.desc').text();

	var dialogContent = '<div title="Become a supervisor of this activity">';

	dialogContent += '<div style="margin:10px; font-size:13px; text-align:center;"">' + desc + '</div>';

	dialogContent += '<div class="red" style="border-top:1px #eee solid; margin:10px; padding-top:10px; font-size:13px; text-align:center; font-weight:bold;">Do you want to be a supervisor of ' + courseName + '?</div>';

	dialogContent += '</div>';

	$(dialogContent).dialog({
		modal:true,
		autoOpen:true,
		width:600,
		height:'auto',
		buttons: {

			'Yes': function() {
				var ths = this;
				$(this).children('.ui-dialog-content').html('<i class="icon-spinner icon-spin"></i> Saving...');
				$(this).parent().find('.ui-dialog-buttonpane').hide();

				$.post('ajax/enrol.php', {action:'enrol', courseid:courseID}, function(res){
					if (res.success) {
						alert('You are now a manager of the activity.');
					} else {
						alert('Something went wrong. Please try again.');
					}
					$(ths).dialog('close');
					window.location.reload();
				});
			},

			'Remove me as supervisor': function() {
				var ths = this;
				$(this).children('.ui-dialog-content').html('<i class="icon-spinner icon-spin"></i> Saving...');
				$(this).parent().find('.ui-dialog-buttonpane').hide();

				$.post('../ajax/remove.php?action=remove&courseid=' + courseID, {action:'remove', courseid:courseID}, function(res){
					if (res.success) {
						alert('You have been removed as a supervisor for this activity.');
					} else {
						alert('Something went wrong. Please try again.');
					}
					$(ths).dialog('close');
					window.location.reload();
				});
			},

			// 'View Activity Page': function() {
			// 	window.open('/course/view.php?id=' + courseID);
			// 	return false;
			// },

			'Cancel': function() {
				$(this).dialog("close");
			}


		}
	});

});
