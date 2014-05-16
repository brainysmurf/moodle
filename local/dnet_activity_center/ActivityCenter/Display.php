<?php

/**
 * Class for generating HTML to display things in the Activity Center
 */

namespace SSIS\ActivityCenter;

class Display
{
	private $activityCenter;
	private $tabs = array( // Array of which tabs are shown in differnet modes
		'teacher' => array(
			'myactivities' => array('index.php', '<i class="icon-ok-sign"></i> My Activities'),
			'all' => array('all.php', '<i class="icon-rocket"></i> Pick Activities'),
			'suggest' => array('suggest.php', '<i class="icon-plus-sign"></i> Suggest A New Activity'),
		),
	);

	public function __construct(ActivityCenter $activityCenter)
	{
		$this->activityCenter = $activityCenter;

		global $PAGE;
		$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');
		$PAGE->requires->css('/blocks/homework/assets/css/homework.css');
		$PAGE->requires->jquery();
		$PAGE->requires->js(ActivityCenter::ACTIVITY_CENTER_PATH . 'assets/js/activitycenter.js');
	}

	public function showTabs($mode, $current)
	{
		$tabs = $this->tabs[$mode];
		$t = '<div class="tabs">';
			$t .= '<ul>';
			foreach ($tabs as $name => $tab) {
				$t .= '<li>';
					$t .= '<a ' . ($name == $current ? 'class="selected"': '') . 'href="' . $tab[0] . '">' . $tab[1] . '</a>';
				$t .= '</li>';
			}
			$t .= '</ul>';
		$t .= '</div>';
		return $t;
	}


	/**
	 * Show an array of courses as buttons, with a filter box
	 */
	public function activityList($courses, $url = '/course/view.php?id=', $listClasses = '')
	{
		global $PAGE;

		// Loading JS from the homework block!
		$PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
		$PAGE->requires->js('/blocks/homework/assets/js/filter.js');

		$r  = '<div class="courseList ' . $listClasses . '">';
		$r .= '<input type="text" class="filter" placeholder="Type here to filter by name..." />';

		$r .= '<div class="row courses">';

		foreach ($courses as $course) {

			// Find the activity manager
			$managers = $this->activityCenter->data->getActivitiesManaged($course->id);
			$managerNames = array();
			foreach ($managers as $manager) {
				$managerNames[] = $manager->firstname . ' ' . $manager->lastname;
			}

			$icon = course_get_icon($course->id);
			$r .= '<div class="col-sm-3"><a href="' . ($url ?  $url . $course->id : '#') . '" class="btn" data-courseid="'. $course->id . '" data-fullname="' . $course->fullname . '">';

				if (preg_match_all('/\((S1|S2|S3|ALL|FULL)\)/i', $course->fullname, $matches)) {
					foreach ($matches[0] as $i => $matchedText) {
						$icon = '<i class="pull-right icon-text">' . $matches[1][$i] . '</i>';
						$course->fullname = str_replace($matchedText, $icon, $course->fullname);
						$course->fullname = trim($course->fullname);
					}
				}

				$r .= $course->fullname;

				if (count($managerNames) > 0 ) {
					$r .= '<span class="green">' . implode(', ', $managerNames) . '</span>';
				} else {
					$r .= '<span class="red"><em>No supervisors</em></span>';
				}
			$r .= '</a></div>';
		}

		$r .= '</div>';
		$r .= '<div class="clear"></div>';
		$r .= '</div>';

		return $r;
	}
}
