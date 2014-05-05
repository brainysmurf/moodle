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
