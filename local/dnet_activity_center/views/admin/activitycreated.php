<?php

/**
 * Success page after creating an activity
 */

$courseID = optional_param('id', '', PARAM_INT);


$signText = 'Your activity has been successfuly created.';
if ($courseID) {
	$signText .= '<p><a class="btn" href="/course/view.php?id=' . $courseID . '">Go to activity</a></p>';
}

echo $OUTPUT->sign('ok-sign', 'Activity Created', $signText);
