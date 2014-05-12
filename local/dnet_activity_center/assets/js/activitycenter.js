$(document).on('click', '.becomeActivityManagerList a', function(e)
{
	e.preventDefault();

	var courseID = $(this).attr('data-courseid');
	var courseName = $(this).attr('data-fullname');

	var dialogContent = '<div title="Become a supervisor of this activity"><div style="padding:10px; font-size:13px; text-align:center;">Do you want to be a supervisor of ' + courseName + '?</div></div>';

	$(dialogContent).dialog({
		modal:true,
		autoOpen:true,
		width:600,
		height:'auto',
		buttons: {

			'Yes': function() {
				var ths = this;
				$(this).children('div').html('<i class="icon-spinner icon-spin"></i> Saving...');
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
			}
		}
	});

});
