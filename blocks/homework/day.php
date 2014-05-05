<?php

die("Not used.");

/**
 * Show all homework due on a single day
 */

/*
include 'include/header.php';

$day = required_param('date', PARAM_RAW);

$date = new \DateTime();
$date->setTimestamp(strtotime($day));

echo $OUTPUT->header();
echo $hwblock->display->tabs('overview', $subtabs);

switch ($hwblock->mode()) {

	case 'student':

		echo '<h2>Homework Due on ' . $date->format('l F jS') . '</h2>';
		$homework = $hwblock->getHomeworkForUser($USER->id, 1, $date->format('U'));
		echo $hwblock->display->homeworkList($homework);
		break;

}

?>
<div class="course-content">
<div class="single-section">
<div class="section-navigation mdl-bottom">
	<?php

	$date->modify('-1 day');

	$previousLink = $date->format('Y-m-d');
	$previousText = $date->format('l F jS');

	$date->modify('+2 days');

	$nextLink = $date->format('Y-m-d');
	$nextText = $date->format('l F jS');

	?>
	<span class="mdl-left"><a href="day.php?date=<?=$previousLink?>"><span class="larrow"></span> <?=$previousText?></a></span>
	<span class="mdl-right"><a href="day.php?date=<?=$nextLink?>"><?=$nextText?> <span class="rarrow"></span></a></span>

</div>
</div>
</div>
<?php

echo $OUTPUT->footer(); */
