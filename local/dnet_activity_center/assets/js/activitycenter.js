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
				});
			},

			'No': function() {
				$(this).dialog("close");
			},

			'View Activity Page': function() {
				window.open('/course/view.php?id=' + courseID);
				return false;
			}


		}
	});

});
