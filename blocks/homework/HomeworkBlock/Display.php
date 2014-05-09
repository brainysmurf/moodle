<?php

namespace SSIS\HomeworkBlock;

class Display
{
	private $hwblock;
	private $possibleTabs = array( // Array of which tabs are shown in differnet modes
		'student' => array(
			'index' => array('index.php', '<i class="icon-tasks"></i> To Do'),
			'classes' => array('classes.php', '<i class="icon-list-ul"></i> By Class'),
			'history' => array('history.php', '<i class="icon-list-alt"></i> History'),
			'add' => array('add.php', '<i class="icon-plus-sign"></i> Add Homework'),
		),
		'pastoral-student' => array( // When a pastoral user clicks on a student (same as parent mode)
			'index' => array('index.php', '<i class="icon-tasks"></i> To Do'),
			'classes' => array('classes.php', '<i class="icon-list-ul"></i> By Class'),
			'history' => array('history.php', '<i class="icon-list-alt"></i> History'),
		),
		'teacher' => array(
			'index' => array('index.php', '<i class="icon-check"></i> Pending Submissions'),
			'classes' => array('classes.php', '<i class="icon-list-ul"></i> By Class'),
			'history' => array('history.php', '<i class="icon-list-alt"></i> History'),
			'add' => array('add.php', '<i class="icon-plus-sign"></i> Add Homework'),
		),
		'parent' => array(
			'index' => array('index.php', '<i class="icon-tasks"></i> To Do'),
			'classes' => array('classes.php', '<i class="icon-list-ul"></i> By Class'),
			'history' => array('history.php', '<i class="icon-list-alt"></i> History'),
		),
		'pastoral' => array(
			'index' => array('index.php', '<i class="icon-home"></i> Home'),
			'classes' => array('classes.php', 'Classes'),
			'courses' => array('courses.php', 'Courses'),
			'grades' => array('grades.php', 'Grades'),
			'students' => array('students.php', 'Students'),
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
			'student' => 'Student Mode',
			'parent' => 'Parent Mode',
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
				$icon = course_get_icon($item->courseid);
				$r .= '<p>' . ($icon ? '<i class="icon-' . $icon . '"></i> ' : '') . $item->coursename . '</p>';
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

					echo $this->hwblock->mode;

					if ($this->hwblock->mode == 'teacher') {
						if (trim($group['teacher'])) {
							$r .= '<span>' . $group['teacher'] . '\'s Class</span>';
						} else {
							$r .= '<span>' . $group['name'] . '</span>';
						}
					}
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
		$r .= '<input type="text" class="filter" placeholder="Enter a student\'s name to search..." />';
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
	    return '<div class="alert alert-info">
	    		<i class="icon-4x icon-' . $icon . ' pull-left"></i>
	    		<h4>' . $bigText . '</h4>
	    		<p>' . $littleText . '</p>
	    	</div>';
	}


	/**
	 * Returns HTML to display a list of homework to do,
	 * optionally organised with headings for a certain field
	 */
	public function homeworkList($homework, $headingsForField = false, $headingPrefix = false, $headingDateFormat = 'l M jS Y', $showClassName = false)
	{
		if (count($homework) < 1) {
			return '<div class="nothing">
				<i class="icon-smile"></i> Nothing to show here.
			</div>';
		}

		$lastHeadingFieldValue = 0;
		$inList = false;
		$r = '<div class="homeworkListContainer">';

		if (!$headingsForField) {
			#$r .= '<div>';
			$r .= '<ul class="homeworkList">';
			$inList = true;
		}

		foreach ($homework as $hw) {

			if ($headingsForField && $hw->{$headingsForField} != $lastHeadingFieldValue) {

				if ($inList) {
					$r .= '</ul>';
					#$r .= '</div>';
					#$r .= '<hr />';
				}

				#$r .= '<div>';

				$r .= '<h3 id="' . $hw->{$headingsForField} . '">';
					#$r .= '<a href="day.php?date=' . date('Y-m-d', strtotime($hw->{$headingsForField})) . '">';
						$r .= $headingPrefix . date($headingDateFormat, strtotime($hw->{$headingsForField}));
					#$r .= '</a>';
				$r .= '</h3>';

				$r .= '<ul class="homeworkList">';
				$inList = true;
				$lastHeadingFieldValue = $hw->{$headingsForField};
			}

			$r .= $this->homeworkItem($hw, $showClassName);
		}

		$r .= '</ul>';
		#$r .= '</div>';

		$r .= '</div>';

		return $r;
	}

	/**
	 * Returns the HTML to display a single homework item
	 */
	private function homeworkItem($hw, $showClassName = false)
	{
		$canEdit = $this->hwblock->canApproveHomework($hw->courseid);
		$past = $hw->duedate < $this->hwblock->today;

		$r  = '<li class="homework ' . ($hw->approved ? 'approved' : 'unapproved') . ($canEdit ? ' canedit' : '') . ($past ? ' past' : '') . '" data-id="' . $hw->id . '">';

		if (!$hw->approved) {
			$r .= '<span class="approvalButtons">';
				$r .= '<span><i class="icon-user"></i> Submitted by ' . $hw->userfirstname . ' ' . $hw->userlastname . ' &nbsp;&nbsp; <i class="icon-warning-sign"></i> Not visible to students until approved</span> &nbsp;';
				if ($canEdit) {
					$r .= '<a class="approveHomeworkButton btn-mini btn btn-success" href="#"><i class="icon-ok"></i> Approve</a>';
				}
			$r .= '</span>';
		}

		// Edit buttons
		if ($canEdit) {
			$r .= '<span class="editButtons">';
				#$r .= '<a class="editHomeworkButton btn-mini btn btn-info" href="#" title="Edit"><i class="icon-pencil"></i> Edit</a>';
				$r .= '<a class="btn-mini btn btn-info" href="add.php?action=edit&editid=' . $hw->id . '" title="Edit"><i class="icon-pencil"></i> Edit</a>';
				$r .= '<a class="deleteHomeworkButton btn-mini btn btn-danger" href="#" title="Delete"><i class="icon-trash"></i> Delete</a>';
			$r .= '</span>';
		}

		$icon = course_get_icon($hw->courseid);
		$r .= '<h5 class="dates">';

			$assignedDates = $hw->getAssignedDates();

			#$r .= '<i class="icon-calendar"></i> Set ' . date('D M jS', strtotime($hw->startdate));
			#$r .= ' &nbsp; <i class="icon-arrow-right"></i> &nbsp; ';
			#$r .= 'Do this on ';

			$assigned = '';
			foreach ($assignedDates as $date) {
				 $assigned .= '' . date('D M jS', strtotime($date)) . ' &nbsp; <i class="icon-plus"></i>  &nbsp; ';
			}
			$r .= rtrim($assigned, '  &nbsp; <i class="icon-plus"></i>  &nbsp; ');

			$r .= ' &nbsp; <i class="icon-arrow-right"></i> &nbsp; ';
			$r .= '<i class="icon-bell"></i> Due on ' . date('D M jS', strtotime($hw->duedate));
		$r .= '</h5>';

		#$r .= '<h4>' . ($icon ? '<i class="icon-' . $icon . '"></i> ' : '') . $hw->coursename . '</h4>';
		$r .= '<h4><a href="class.php?groupid=' . $hw->groupid . '">' . ($icon ? '<i class="icon-' . $icon . '"></i> ' : '') . $hw->coursename . '</a></h4>';

		if ($showClassName) {
			$r .= '<h4>' . $hw->groupName() . '</h4>';
		}

		$desc = htmlentities($hw->description, ENT_COMPAT, 'UTF-8', false);
		$desc = nl2br($desc);
		$r .= '<p>' . $desc;
		$r .= '<span class="duration"><i class="icon-time"></i> You should spend ' . $this->showDuration($hw->duration) . ' on this.</span>';
		$r .= '</p>';

		$r .= '</li>';
		return $r;
	}

	private function showDuration($duration)
	{
		$r .= '';

		list($min, $max) = explode('-', $duration);

		if ($min == 0 && $max) {
			$r .= 'up to ' . $this->displayMinutes($max);
		} elseif ($max) {
			$r .= 'between ' . $this->displayMinutes($min) . ' and ' . $this->displayMinutes($max);
		} else {
			$r .= $this->displayMinutes($min);
		}

		return $r;
	}

	private function displayMinutes($mins)
	{
		$hours = floor($mins / 60);
		$mins = $mins % 60;

		$str = '';

		if ($hours) {
			$str .= $hours . ' hour' . $this->s($hours);
		}

		if ($mins) {
			if ($hours) {
				$str .= ' ';
			}
			$str .= $mins . ' minute' . $this->s($mins);
		}

		return $str;
	}

	/**
	* Returns an s to pluralize a word depending on if the given number is greater than 1
	*/
	private function s($num)
	{
		return $num == 1 ? '' : 's';
	}
}
