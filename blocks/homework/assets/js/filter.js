// Filtering the my course list
//$(document).on('keyup', '.courseList .filter', function(){
$('.courseList .filter').bindWithDelay('keyup', function(){
	var filter = $(this).val();

	if (!$('.courseList').data('originalHTML')) {
		$('.courseList').data('originalHTML', $('.courseList .courses').html());
	} else {
		$('.courseList .courses').html( $('.courseList').data('originalHTML') );
	}

	if (filter) {

		var regex = new RegExp(filter, 'i');

		$('.courseList .courses div').each(function(){
			var name = $(this).text();
			if (name.match(regex)) {
				$(this).show();
			} else {
				$(this).remove();
			}
		});

	}

}, 100);
