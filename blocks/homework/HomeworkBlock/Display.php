<?php

namespace SSIS\HomeworkBlock;

class Display
{
	private $hwblock;
	private $possibleTabs = array( // Array of which tabs are shown in differnet modes
		'student' => array(
			'index' => array('index.php', '<i class="icon-calendar"></i> To Do'),
			'history' => array('history.php', '<i class="icon-th-list"></i> Full List / History'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
			'add' => array('add.php', '<i class="icon-plus-sign"></i> Add Homework'),
			'icalfeed' => array('icalfeed.php', '<i class="icon-rss"></i> iCal'),
		),
		'pastoral-student' => array( // When a pastoral user clicks on a student (same as parent mode)
			'index' => array('index.php', '<i class="icon-calendar"></i> To Do'),
			'history' => array('history.php', '<i class="icon-th-list"></i> Full List / History'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
			'icalfeed' => array('icalfeed.php', '<i class="icon-rss"></i> iCal'),
		),
		'teacher' => array(
			'index' => array('index.php', '<i class="icon-check"></i> Manage Submissions'),
			'history' => array('history.php', '<i class="icon-th-list"></i> Full List / History'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
			'add' => array('add.php', '<i class="icon-plus-sign"></i> Add Homework'),
		),
		'parent' => array(
			'index' => array('index.php', '<i class="icon-calendar"></i> To Do'),
			'history' => array('history.php', '<i class="icon-th-list"></i> Full List / History'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
			'icalfeed' => array('icalfeed.php', '<i class="icon-rss"></i> iCal'),
		),
		'pastoral' => array(
			'index' => array('index.php', '<i class="icon-home"></i> Overview'),
			'classes' => array('classes.php', '<i class="icon-group"></i> Classes'),
			#'courses' => array('courses.php', '<i class="icon-magic"></i> Courses'),
			'grades' => array('grades.php', '<i class="icon-sitemap"></i> Grades'),
			'students' => array('students.php', '<i class="icon-user"></i> Student Lookup'),
		),
	);

	public function __construct(Block $hwblock)
	{
		$this->hwblock = $hwblock;
	}


	public function modeTabs()
	{
		$currentMode = $this->hwblock->mode();
		$possibleModes = $this->hwblock->possibleModes();

		$modeLabels = array(
			'student' => '<i class="icon-user"></i> Student Mode',
			'parent' => '<i class="icon-male"></i> Parent Mode',
			'teacher' => '<i class="icon-magic"></i> Teacher Mode',
			'pastoral' => '<i class="icon-heart"></i> Pastoral Mode',
		);

		if ($currentMode == 'pastoral-student') {
			global $DB, $SESSION;
			$possibleModes[] = 'pastoral-student';
			$student = $DB->get_record('user', array('id' => $SESSION->homeworkBlockUser));
			$modeLabels['pastoral-student'] = 'Student Mode: ' . $student->firstname . ' ' . $student->lastname;
		}

		if (count($possibleModes) < 2) {
			return false;
		}

		$t = '<div class="tabs noborder">';
		$t .= '<ul class="additional-tabs">';
		foreach ($possibleModes as $mode) {
			$t .= '<li>';
			$t .= '<a ' . ($mode == $currentMode ? 'class="selected"': '') . 'href="changemode.php?mode=' . $mode . '">' . $modeLabels[$mode] . '</a>';
			$t .= '</li>';
		}
		$t .= '</ul>';
		$t .= '</div>';

		return $t;
	}

	/**
	 * Shows a tab for each of a user's children and allows them to switch between them
	 */
	public function parentTabs()
	{
		global $SESSION;

		$currentUser = $this->hwblock->userID();

		if (!isset($SESSION->usersChildren) || !is_array($SESSION->usersChildren)) {
			return false;
		}

		$t = '<div class="tabs noborder">';
		$t .= '<ul class="additional-tabs">';
		foreach ($SESSION->usersChildren as $child) {
			$t .= '<li>';
			$t .= '<a ' . ($child->userid == $currentUser ? 'class="selected"': '') . 'href="changeuser.php?userid=' . $child->userid . '">' . $child->firstname . ' ' . $child->lastname . '</a>';
			$t .= '</li>';
		}
		$t .= '</ul>';
		$t .= '</div>';
		return $t;
	}

	/**
	 * Returns HTML for the tabs at the top of all homework pages.
	 * Including the subtabs and mode / children tabs.
	 */
	public function tabs($current = false, $subtabs = false, $currentsubtab = false, $groupid = false)
	{
		global $USER;
		if (!$USER->id) {
			return '';
		}

		$tabs = $this->possibleTabs[$this->hwblock->mode()];

		$t = '';

		// If in parent mode, show the list of children at the top.
		if ($this->hwblock->mode() == 'parent') {
			$t .= $this->parentTabs();
		}

		// Show tabs for switching modes (if possible)
		$t .= $this->modeTabs();

		$t  .= '<div class="tabs">';
		$t .= '<ul>';
		foreach ($tabs as $name => $tab) {
			if ($groupid && $name == 'add') {
				$tab[0] .= '?groupid=' . $groupid;
			}
			$t .= '<li>';
				$t .= '<a ' . ($name == $current ? 'class="selected"': '') . 'href="' . $tab[0] . '">' . $tab[1] . '</a>';
			$t .= '</li>';
		}
		$t .= '</ul>';


		if ($subtabs) {
			$t .= '<ul class="additional-tabs">';
			foreach ($subtabs as $name => $tab) {
				$t .= '<li>';
					$t .= '<a ' . ($name == $currentsubtab ? 'class="selected"': '') . 'href="' . $tab[0] . '">' . $tab[1] . '</a>';
				$t .= '</li>';
			}
			$t .= '</ul>';
		}
		$t .= '</div>';

		return $t;
	}


	/**
	 * Index page for students
	 */
	public function overview($homework, $hashLinks = false)
	{
		$today = $this->hwblock->today;

		//Build an array of dates for the next fortnight
		$dates = array();

		$date = new \DateTime('monday this week');

		for ($i = 0; $i < 14; $i++) {

			if ($date->format('l') != 'Saturday' && $date->format('l') != 'Sunday') {
				$dates[$date->format('Y-m-d')] = array();
			}

			$date->modify('+1 day');
		}

		// Sort the homework into the days it's assigned for
		foreach ($homework as $hw) {
			if (isset($dates[$hw->assigneddate])) {
				$dates[$hw->assigneddate][] = $hw;
			}
		}

		$r = '<ul class="weekOverview row">';

		$i = 0;
		foreach ($dates as $date => $hw) {
			++$i;
			$past = $date < $today;
			$r .= '<li class="col-md-2 ' . ($past ? 'past' : '') . '">
			<a class="day" href="'. ($hashLinks ? '#' . $date : 'day.php?date=' . $date) . '">';
			$r .= '<h4>' . date('l M jS', strtotime($date)) . '</h4>';
			foreach ($hw as $item) {
				if ($item->courseid) {
					$icon = course_get_icon($item->courseid);
					$text = $item->coursename;
				} else {
					$icon = 'pushpin';
					$text = $this->truncate($item->description, 30);
				}

				$r .= '<p>' . ($icon ? '<i class="icon-' . $icon . '"></i> ' : '') . $text . '</p>';
			}
			$r .= '</a>
			</li>';
			if ($i == 5) {
				$r .= '</ul><div class="clear"></div><ul class="weekOverview row">';
			}
		}

		$r .= '</ul>';
		$r .= '<div class="clear"></div>';
		return $r;
	}

	/**
	 * Show an array of classes as buttons, with a filter box
	 */
	public function classList($courses, $url = 'class.php?groupid=')
	{
		global $PAGE;
		$PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
		$PAGE->requires->js('/blocks/homework/assets/js/filter.js');

		$r  = '<div class="courseList">';
		$r .= '<input type="text" class="filter" placeholder="Type here to filter by name or teacher..." />';

		$r .= '<div class="row courses">';

		foreach ($courses as $courseID => $enrollment) {
			$icon = course_get_icon($courseID);

			foreach ($enrollment['groups'] as $group) {
				$r .= '<div class="col-sm-3"><a href="' . $url . $group['id'] . '" class="btn">';
					if ($icon) {
						$r .= '<i class="icon-' . $icon . '"></i> ';
					}
					$r .= $enrollment['course']->fullname;

					$r .= '<span>' . $group['classname'] . '</span>';


					/*if (!empty($group['teacher'])) {



						if ($this->hwblock->mode() != 'student') {
							$r .= ' <span style="font-size:9px;">' . $group['name'] . '</span>';
						}
						$r .= '</span>';

					} else {
						$r .= '<span>' . $group['name'] . '</span>';
					}*/

				$r .= '</a></div>';
			}
		}

		$r .= '</div>';

		$r .= '<div class="clear"></div>';

		$r .= '</div>';
		return $r;
	}

	/**
	 * Show an array of courses as buttons, with a filter box
	 */
	public function courseList($courses, $url = 'course.php?courseid=')
	{
		global $PAGE;
		$PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
		$PAGE->requires->js('/blocks/homework/assets/js/filter.js');

		$r  = '<div class="courseList">';
		$r .= '<input type="text" class="filter" placeholder="Type here to filter by name..." />';

		$r .= '<div class="row courses">';

		foreach ($courses as $courseID => $course) {
			$icon = course_get_icon($courseID);
			$r .= '<div class="col-sm-3"><a href="' . $url . $courseID . '" class="btn">';
				if ($icon) {
					$r .= '<i class="icon-' . $icon . '"></i> ';
				}
				$r .= $course->fullname;
			$r .= '</a></div>';
		}

		$r .= '</div>';
		$r .= '<div class="clear"></div>';
		$r .= '</div>';

		return $r;
	}

	public function studentList()
	{
		global $PAGE;
		$PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
		$PAGE->requires->js('/blocks/homework/assets/js/filter.js');

		$r  = '<div class="courseList userList">';
		$r .= '<input type="text" class="filter" placeholder="Type part of a student\'s name or their whole PowerSchool ID to search..." />';
		$r .= '<div class="row courses"></div>';
		$r .= '<div class="clear"></div>';
		$r .= '</div>';
		return $r;
	}

	/**
	* Returns HTML for a dragonnet-like explanation
	*/
	public function sign($icon, $bigText, $littleText)
	{
	    return '<div class="blueAlert alert alert-info">
	    		<i class="icon-4x icon-' . $icon . ' pull-left"></i>
	    		<h4>' . $bigText . '</h4>
	    		<p>' . $littleText . '</p>
	    	</div>';
	}


	/**
	 * Returns HTML to display a list of homework to do,
	 * optionally organised with headings for a certain field
	 *
	 * @param headingsForField 	Watch this field in each homework item, if the contents of this field is not the same as the last one, a new header will be shown. This should be a field containing a date that can be parsed by strtotime()
	 * @param headingPrefix	Show this text before each heading
	 * @param headingDateFormat	The format to show the date from the headingsForField field
	 * @param showClassName	true or false to show the class (group) name each item is assigned in
	 */
	public function homeworkList(
		$homework,
		$headingsForField = false,
		$headingPrefix = false,
		$headingDateFormat = 'l M jS Y',
		$showClassName = false,
		$showAssignedDates = true
	) {
		if (count($homework) < 1) {
			return '<div class="nothing">
				<i class="icon-smile"></i> Nothing to show here.
			</div>';
		}

		$r = '<div class="homeworkListContainer">'; //container

		if ($headingsForField) {
			$inList = false;
			$lastHeadingFieldValue = 0;
		} else {
			// If the headingsForField headings are NOT used, start the list here
			$r .= '<ul class="homeworkList">';
			$inList = true;
		}

		foreach ($homework as $hw) {

			// If the headingsForField headers are used, check if it's time for a new heading and start a new list
			if ($headingsForField && $hw->{$headingsForField} != $lastHeadingFieldValue) {

				if ($inList) {
					$r .= '</ul>';
				}

				$r .= '<h3 id="' . $hw->{$headingsForField} . '">';
					$r .= $headingPrefix . date($headingDateFormat, strtotime($hw->{$headingsForField}));
				$r .= '</h3>';

				$r .= '<ul class="homeworkList">';
				$inList = true;
				$lastHeadingFieldValue = $hw->{$headingsForField};
			}

			$r .= $this->homeworkItem($hw, $showClassName, $showAssignedDates);
		}

		$r .= '</ul>'; // end list
		$r .= '</div>'; // end container

		return $r;
	}

	/**
	 * Returns the HTML to display a single homework item
	 */
	private function homeworkItem($hw, $showClassName = false, $showAssignedDates = false)
	{
		// Is this item only visible to students in the future?
		$future = $hw->startdate > $this->hwblock->today;

		// Should we show the edit / delete buttons?
		// true if the item is a user's private item,
		// or it's not private and the user is a techer for the course
		$canEdit = $this->hwblock->canEditHomeworkItem($hw);

		// Is this due in the past?
		$past = $hw->duedate < $this->hwblock->today;

		$r  = '<li class="homework ' . ($hw->approved ? 'approved' : 'unapproved') . ($canEdit ? ' canedit' : '') . ($past ? ' past' : '') . ($future ? ' future' : '') . ($hw->private ? ' private' : '') . '" data-id="' . $hw->id . '" data-duedate="'. $hw->duedate . '">';

		if (!$hw->private && !$hw->approved) {
			$r .= '<h5><i class="icon-pause"></i> This must be approved by a teacher before it is visible to the whole class.</h5>';
		}

		if (!$hw->private && $hw->approved && $this->hwblock->mode() == 'teacher') {
			$r .= '<h5><i class="icon-ok"></i> Approved and visible to the whole class.</h5>';
		}

		if ($past) {
			$r .= '<h5><i class="icon-time"></i> The due date for this has passed.</h5>';
		}

		if ($hw->private) {
			$r .= '<h5><i class="icon-eye-close"></i> Only ' . $this->getUsername($hw->userid) . ' can see this.</h5>';
		}

		// Button for teachers to approve pending homework
		if (!$hw->approved && !$hw->private) {
			// Only teachers should be seeing this
			$r .= '<span class="buttons approvalButtons">';
				$r .= '<span><i class="icon-user"></i> Submitted by ' . $hw->userfirstname . ' ' . $hw->userlastname . ' &nbsp;&nbsp; <i class="icon-warning-sign"></i> Not visible to students until approved</span> &nbsp;';
				if ($canEdit) {
					$r .= '<a class="approveHomeworkButton btn-mini btn btn-success" href="#"><i class="icon-ok"></i> Approve</a>';
				}
			$r .= '</span>';
		}

		if ($future) {
			$r .= '<span class="buttons approvalButtons">';
				$r .= '<span><i class="icon-pause"></i> Will not appear to students until ' . date('l M jS Y', strtotime($hw->startdate)) . '</span>';
			$r .= '</span>';
		}

		// Edit buttons
		if ($canEdit) {
			$r .= '<span class="buttons editButtons">';
				$r .= '<a class="btn-mini btn btn-info" href="add.php?action=edit&editid=' . $hw->id . '" title="Edit"><i class="icon-pencil"></i> Edit</a>';
				$r .= '<a class="deleteHomeworkButton btn-mini btn btn-danger" href="#" title="Delete"><i class="icon-trash"></i> Delete</a>';
			$r .= '</span>';
		}

		$icon = course_get_icon($hw->courseid);
		$r .= '<h5 class="dates">';

			// List of assigned dates
			if ($showAssignedDates) {
				$r .= $this->showAssignedDates($hw->getAssignedDates());
				$r .= ' &nbsp; <i class="icon-arrow-right"></i> &nbsp; ';
			}

			// Due date
			$r .= '<i class="icon-bell"></i> <strong>Due on</strong> ' . date('D M jS Y', strtotime($hw->duedate));

		$r .= '</h5>';

		// Course name with link
		$r .= '<h4><a href="class.php?groupid=' . $hw->groupid . '">' . ($icon ? '<i class="icon-' . $icon . '"></i> ' : '') . $hw->coursename . '</a></h4>';

		// Class (group) name
		global $SESSION;
		if ($SESSION->userIsTeacher || $showClassName) {
			$r .= '<h4>' . $hw->getGroupName() . '</h4>';
		}

		// Description
		$r .= '<p>';

			if ($hw->title) {
				$r .= '<strong>' . $hw->title . '</strong><br/>';
			}

			$r .= $this->filterText($hw->description);

			// Duration
			$r .= '<span class="duration"><i class="icon-time"></i> This should take ' . $this->showDuration($hw->duration) . ' in total.</span>';

		$r .= '</p>';

		// Notes
		if ($notes = $hw->getNotes($this->hwblock->userID())) {
			$notes = $this->filterText($notes);
		} else  {
			$notes = '';
		}

		$r .= '<p class="notes" ' . ($notes ? '' : 'style="display:none;"') . '>' . $notes . '</p>';


		if ($this->hwblock->mode() == 'teacher' || $this->hwblock->mode() == 'student') {
			// Edit notes button
			$r .= '<span class="buttons noteButtons">';
				$r .= '<a class="btn-mini btn btn-primary editNotes" href="#"><i class="icon-comment"></i> Add Notes</a>';
				$r .= '<a class="btn-mini btn btn-danger cancelNotes" href="#" style="display:none;"><i class="icon-remove"></i> Cancel</a>';
				$r .= '<a class="btn-mini btn btn-success saveNotes" href="#" style="display:none;"><i class="icon-save"></i> Save Notes</a>';
			$r .= '</span>';
		}

		$r .= '<div class="clear"></div>';

		$r .= '</li>';
		return $r;
	}

	private function showAssignedDates($dates)
	{
		$output = '<strong>To Do On</strong> &nbsp;';
		$count = count($dates);

		$joiner = ' &nbsp;<strong class="ampersand-icon">&amp;</strong>&nbsp; ';

		// Show the first two dates
		// or three if there are only three
		// but only two if there are more than three
		foreach ($dates as $i => $date) {

			if ($i > 0) {
				$output .= $joiner;
			}

			$output .= date('D M jS', strtotime($date));

			// If there are more than 3 dates and this is the 2nd one, stop
			if ($count > 3 && $i >= 1) {
				break;
			}
		}

		// Show a 'more' link if there are more than 3 dates
		if ($count > 3) {
			$overflow = $count - $i - 1;
			$output .= $joiner . '<a href="#" class="showAllHomeworkDates">' . $overflow . ' more days...</a>';
			// This is lame but how else to get the json to the page?
			$output .= '<span class="assignedDatesJSON">' . json_encode($dates) . '</span>';
		}

		return $output;
	}

	private function showDuration($duration, $short = false)
	{
		$r = '';

		if (stripos('-', $duration)) {
			list($min, $max) = explode('-', $duration);
		} else {
			$min = $duration;
			$max = null;
		}

		if ($min == 0 && $max) {

			$r .= 'up to ' . $this->displayMinutes($max, $short);

		} elseif ($max) {

			$r .= $short ? '' : 'between ';

			$r .= $this->displayMinutes($min, $short);

			$r .= $short ? ' to ' : ' and ';

			$r .= $this->displayMinutes($max, $short);

		} else {

			$r .= 'up to ' . $this->displayMinutes($min, $short);

		}

		return $r;
	}

	private function displayMinutes($mins, $short = false)
	{
		$hours = floor($mins / 60);
		$mins = $mins % 60;

		$str = '';

		if ($hours) {
			$str .= $hours;
			$str .= $short ? 'hr' : ' hour';
			$str .= $this->s($hours);
		}

		if ($mins) {
			if ($hours) {
				$str .= ' ';
			}
			$str .= $mins;
			$str .= $short ? 'min' : ' minute';
			$str .= $this->s($mins);
		}

		return $str;
	}


	/**
	 * Weekly stats view for pastoral staff
	 */
	public function weekStats(HomeworkStats $stats)
	{
		//Build an array of dates for the next fortnight
		$dates = array();

		$date = clone $stats->getStartDate();

		$dateInterval = $date->diff($stats->getEndDate());

		for ($i = 0; $i < $dateInterval->d; $i++) {

			if ($date->format('l') != 'Saturday' && $date->format('l') != 'Sunday') {
				$dates[$date->format('Y-m-d')] = array();
			}

			$date->modify('+1 day');
		}

		$homework = $stats->getHomework();

		// Sort the homework into the days it's assigned for
		foreach ($homework as $hw) {
			if (isset($dates[$hw->assigneddate])) {
				$dates[$hw->assigneddate][] = $hw;
			}
		}

		$r = '<ul class="weekOverview pastoralWeekOverview row">';

		foreach ($dates as $date => $hw) {
			++$i;
			$past = $date < $this->hwblock->today;
			$r .= '<li class="col-md-2 ' . ($past ? 'past' : '') . '">
				<span class="day row">

				<h4>' . date('l M jS', strtotime($date)) . '</h4>';

				foreach ($hw as $item) {
					if ($item->courseid) {
						$icon = course_get_icon($item->courseid);
						$text = $item->coursename;
					} else {
						$icon = 'pushpin';
						$text = $this->truncate($item->description, 30);
					}

					$assignedDates = $item->getAssignedDates();
					if ($assignedDates) {
						$averageDuration = $item->duration / count($assignedDates);
					} else {
						$averageDuration = $item->duration;
					}

					$r .= '<p class="col-md-4"><a href="class.php?groupid=' . $item->groupid . '">';
						$r .= ($icon ? '<i class="icon-' . $icon . '"></i> ' : '') . $text;
						$r .= '<br/><strong>' . $this->displayMinutes($item->duration, true) . ' total (' . $this->displayMinutes($averageDuration, true) . ' today)</strong>';
					$r .= '</a></p>';
				}

			$r .= '</span>
			</li>';
		}

		$r .= '</ul>';
		$r .= '<div class="clear"></div>';
		return $r;
	}


	function icalFeedBox($button = true)
	{
		// Feed link
		$r = '<div class="feedLink">';
		$text = 'You can see due dates for your homework in iCal by adding this URL.';
		if ($button) {
			$text .= ' <a class="btn btn-mini btn-primary" href="icalfeed.php">Click here to learn how</a>';
		}
		$text .= '<input type="text" readonly="readonly" value="' . $this->hwblock->generateFeedURL() . '"/>';
		$r .= $this->sign('calendar', 'iCal Feed', $text);
		$r .= '</dv>';
		return $r;
	}

	/**
	 * Utility functions...
	 */

	/**
	* Returns an s to pluralize a word depending on if the given number is greater than 1
	*/
	private function s($num)
	{
		return $num == 1 ? '' : 's';
	}

	public function filterText($text)
	{
		$text = htmlentities($text, ENT_COMPAT, 'UTF-8', false);
		$text = nl2br($text);
		$text = $this->parseURLs($text);
		return $text;
	}

	public function truncate($text, $limit, $append = '...')
	{
		if ($limit < 1) {
			return '';
		}
		if (strlen($text) > $limit) {
			return substr($text, 0, $limit - 3) . $append;
		} else {
			return $text;
		}
	}

	//Shows beginning and end of URL. Cuts out the middle
	function truncateURL($text, $limit, $append = '...')
	{
		if ($limit < 1) {
			return '';
		}

		$offset1 = ceil(0.65 * $limit) - 2;
		$offset2 = ceil(0.30 * $limit) - 1;

		if (strlen($text) > $limit) {
			return substr($text, 0, $offset1) . '...' . substr($text, -$offset2);
		} else {
			return $text;
		}
	}

	/**
	 * Turn URLs into anchor tags, and images into images
	 */
	public function parseURLs($text, $maxurl_len = 50, $target = "_blank", $embedImages = true)
	{
		if (preg_match_all('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n"\'<>]+)/si', $text, $urls)) {

			foreach (array_unique($urls[1]) as $url) {

				$urltext = $this->truncateURL($url, $maxurl_len);

				// Images
				if ($embedImages && $this->isURLImage($url)) {
					$text = str_replace($url, '<a href="' . $url . '" target="' . $target . '" class="embeddedPhoto"><img src="' . $url . '"  /></a>', $text);
					continue;
				}

				$text = str_replace($url, '<a href="' . $url . '" target="' . $target . '" title="' . $url . '" rel="nofollow">' . $urltext . '</a>', $text);

			}
		}

		return $text;
	}

	private function isURLImage($url)
	{
		$ext = strtolower(substr($url, strrpos($url, '.')));
		return $ext == '.jpg' || $ext == '.png' || $ext == '.gif' || $ext == '.bmp';
	}

	private function getUsername($userid)
	{
		global $DB;
		return $DB->get_field('user', 'username', array('id' => $userid));
	}

}
