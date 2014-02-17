<?php

if ($PAGE->course->id != 1 && isloggedin()) {   # if not frontpage

	//Search URL
	$searchURL = new moodle_url('/blocks/search/');

	//Begin form
	echo html_writer::start_tag('form', array(
		'id' => 'headerSearchForm',
		'action' => $searchURL,
		'method' => 'get'
	));

		if (empty($PAGE->course->id) || $PAGE->course->id == 1395) {

			//Frontpage or "Frontpage" course
			$placeholder = 'Search All of DragonNet';

		} else {

			//Search within a course
			$placeholder = 'Search Within This Course';

			echo html_writer::empty_tag('input', array(
				'type' => 'hidden',
				'name' => 'courseID',
				'value' => $PAGE->course->id
			));

		}

		//Input box
		echo html_writer::empty_tag('input', array(
			'type' => 'text',
			'name' => 'q',
			'placeholder' => s($placeholder),
			'onclick' => 'this.select()'
		));

		//Search button
		echo html_writer::tag('button', 'Search');

	//End form
	echo html_writer::end_tag('form');
}
