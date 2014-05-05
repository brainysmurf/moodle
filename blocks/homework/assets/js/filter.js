// Filtering the my course list
//$(document).on('keyup', '.courseList .filter', function(){
$('.courseList .filter').bindWithDelay('keyup', function(){
	var filter = $(this).val();
	if (filter) {

		var regex = new RegExp(filter, 'i');
		$('.courseList .courses div').each(function(){

			var name = $(this).text();
			if (name.match(regex)) {
				$(this).show();
			} else {
				$(this).hide();
			}

		});

	} else {
		$('.courseList .courses div').show();
	}

}, 100);


// Student search
function studentSearch() {
	var q = $('.userList input').val();
	if (!q) {
		return;
	}
	var div = $(this).closest('.userList');

	div.find('.courses').html('<div class="nothing"><i class="icon-spinner icon-spin"></i> Searching for <strong>' + q + '</strong>...</div>');

	$.get('ajax/studentsearch.php', {q:q}, function(res)
	{
		var html = '';

		if (res.users.length < 1) {

			html += '<div class="nothing"><i class="icon-frown"></i> Nothing to show here.</div>';

		} else {

			for (var i in res.users) {
				var user = res.users[i];
				html += '<div class="col-sm-3"><a href="changeuser.php?userid=' + user.id + '" class="btn">';
					html += user.firstname + ' ' + user.lastname;
					html += '<span>' + user.idnumber + '&nbsp;</span>';
				html += '</a></div>';
			}

		}
		div.find('.courses').html(html);
	});
}

$('.userList input[type=text]').bindWithDelay('keyup', studentSearch, 500);
