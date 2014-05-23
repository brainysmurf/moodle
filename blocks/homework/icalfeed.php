<?php

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('icalfeed');

switch ($hwblock->mode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':

		echo $hwblock->display->icalFeedBox(false);

	break;
}

echo $OUTPUT->footer();
