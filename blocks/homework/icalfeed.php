<?php

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('icalfeed');

switch ($hwblock->mode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':

		echo $hwblock->display->icalFeedBox(false);

		echo '<h2><i class="icon-group"></i> How To Add Your Homework Feed To iCal</h2>';

		?>
		<ol id="feedhowto">
			<li>Open iCal and choose <strong>File</strong> > <strong>New Calendar Subscription...</strong>
			<img src="assets/img/feed-1.png" />
			</li>

			<li>Paste the link from above in the <strong>Calendar URL</strong> box.
			<img src="assets/img/feed-2.png" />
			</li>

			<li>Change the <strong>Name</strong> to &quot;Homework&quot; and the <strong>Auto-Refresh</strong> option to Every 15 minutes. Then click <strong>OK</strong> and your homework from DragonNet will automatically appear in iCal.
			<img src="assets/img/feed-3.png" />
			</li>

		</ol>
		<?

	break;
}

echo $OUTPUT->footer();
