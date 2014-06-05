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
			'overview' => array('index.php', '<i class="icon-ok-sign"></i> Overview'),
			'all-elem' => array('all-elem.php', '<i class="icon-rocket"></i> Pick Elementary Activities'),
			'all-sec' => array('all-sec.php', '<i class="icon-rocket"></i> Pick Secondary Activities'),
			'pdframework' => array('pd-framework.php', '<i class="icon-star"></i> Choose PD Strand'),
			'goals' => array('goals.php', '<i class="icon-pencil"></i> Enter Your Goals'),
		),
	);

	public function __construct(ActivityCenter $activityCenter)
	{
		$this->activityCenter = $activityCenter;

		global $PAGE, $OUTPUT;
		$this->output = $OUTPUT;
		// Loading JS from the homework block!
		$PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
		$PAGE->requires->js('/blocks/homework/assets/js/filter.js');

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

	public function displayEnterComment($userid, $text)
	{
		global $CFG;
		$info = json_decode($text->data);

		$ret = '<div class="courseList">';   # this is needed just for the CSS

		$ret .= '<p margin-top:10px;><b>Department Goal(s):</b>';
		$ret .= ' (These goals come directly from your Department.)</p>';
		$ret .= '<textarea id="department_goal" class="filter" rows="3" style="font-size:18px;" />';
		$ret .= $info->department;
		$ret .= '</textarea>';

		$ret .= '<br/><p><b>Individual Goal(s):</b>';
		$ret .= ' (Forumlate a goal according to the listed items 1-5 in the linked document.)</p>';
		$ret .= '<textarea id="individual_goal" class="filter" rows="3" style="font-size:18px;" />';
		$ret .= $info->individual;
		$ret .= '</textarea>';

		$ret .= '<br/><p><b>Pastoral or Leadership Goal(s)</b>' ;
		$ret .= ' (Those who are not PoRs formulate a SMART goal relevant to their role of supporting student well-being. Those who are PoRs formulate a leadership SMART goal relevant to their development as leaders.)</p>';
		$ret .= '<textarea id="pastleadership_goal" class="filter" rows="3" style="font-size:18px;" />';
		$ret .= $info->pastleadership;
		$ret .= '</textarea>';

		$ret .= '<br/><p><b>Additional Individual Goal(s):</b>';
		$ret .= ' (Addtitional goals that do not fit in the above headings <i>e.g.</i> some initiative.)</p>';
		$ret .= '<textarea id="additional_goal" class="filter" rows="3" style="font-size:18px;" />';
		$ret .= $info->additional;
		$ret .= '</textarea>';

        $ret .= '<ul class="buttons"><br />';
        $ret .= '<a id="submit_button" href="'.$CFG->wwwroot.'" class="btn"><i class="icon-plus-sign "></i> (Re-)submit This Goal</a>';
        $ret .= '</ul>';
		$ret .= '
			<script>
					$("#submit_button").bind("click", function(e) {
        			e.preventDefault();
			        var formURL = "submit_goal.php";
			        var formData = {
			            "department": $("#department_goal").val(),
			            "individual": $("#individual_goal").val(),
			            "pastleadership": $("#pastleadership_goal").val(),
			            "additional": $("#additional_goal").val(),
			            "userid": "'.$userid.'"
			        };
			        $.ajax(
			        {
			            url : formURL,
			            data: formData,
			            async: true,
			            type: "GET",
			            success: function(data, textStatus, jqXHR)
			            {
			                $("#dialog").dialog("open");
			                window.location.reload();
			            },
			            error: function(jqXHR, textStatus, errorThrown)
			            {
			                alert(\'Something wrong!\');
			            }
			        });
        		});
        	</script>';
		return $ret;
	}

	public function displayComment($userid)
	{

	}


	public function PDSelection($pdchoice)
	{
		if (!$pdchoice){
			echo 'No PD data to display<br />';
			return;
		}

		echo $pdchoice->data;
	}

	public function displayPDFramework($stuff)
	{
		if (empty($stuff->data)) {
			return "Nothing to see";
		}
		return $stuff->data;
	}

	public function displayPDFrameworkChoices($userid)
	{

		$ret = '<form id="choice_form" action="#">';

		$choices = array(
			array('text'=>' Learning Engagements (Inquiry)', 'value'=>'(S1) Learning Engagement'),
			array('text'=>' Assessment & Feedback', 'value'=>'(S2) Assessment & Feedback'),
			array('text'=>' Differentiation', 'value'=>'(S3) Differentiation')
			);

		$ret .= '
		<style type="text/css">
		.tftable {font-size:18px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
		.tftable th {font-size:18px;color:#eee;background-color:#1662A3;border-width: 1px;padding: 8px;border-style: solid;border-color: #000;text-align:left;}
		.tftable tr {background-color:#d4e3e5;}
		.tftable td {font-size:18px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;}
		</style>';

		$starttable = '<table class="tftable" border="1">';
		$startrow = '<tr>';
		$endrow = '</tr>';
		$endtable = '</table>';
		$seasons = array(
			"S1"=>"Season 1",
			"S2"=>"Season 2",
			"S3"=>"Season 3"
			);

		$ret .= $starttable;
		$ret .= $startrow;
		$ret .= '<td style="color:#eee;background-color:#eee;"></td>';
		foreach ($seasons as $season) {
			$ret .= '<th>'.$season.'</th>';
		}
		$ret .= $endrow;

		$ret .= $startrow;
		$ret .= '<td><b>Choose PD:</b></td>';
		foreach ($choices as $area) {
			$ret .= '<td><input type="radio" name="group1" value="'.$area['value'].'">'.$area['text'].'</td>';
		}
		$ret .= $endrow;
		$ret .= $endtable;

		$ret .= '<br />';
		$ret .= $starttable;
		$ret .= $startrow;
		$ret .= '<td style="color:#eee;background-color:#eee;"></td>';
		$ret .= '<th>Survey: How would you prefer to engage on your chosen PD? (non-binding)</th>';
		$ret .= $endrow;

		$ret .= $startrow;
		$ret .= '<td><b>Choose How:</b></td>';

		$subchoices = array(
			array('text'=>' School Improvement Teams', 'value'=>'SIT'),
			array('text'=>' Reflective Teaching', 'value'=>'Reflective'),
			array('text'=>' Skill Share', 'value'=>'Skill Share')
			);

		$ret .= '<td>';
		foreach ($subchoices as $area) {
			$ret .= '<input type="radio" name="group2" value="'.$area['value'].'">'.$area['text'].'<br/>';
		}
		$ret .= '</td>';

		$ret .= $endrow;
		$ret .= $endtable;

		$ret .= '</form>';

        $ret .= '<ul class="buttons"><br />';
        $ret .= '<a id="submit_button" href="'.$CFG->wwwroot.'" class="btn"><i class="icon-plus-sign "></i> (Re-)submit This Choice</a>';
        $ret .= '</ul>';
		$ret .= '
			<script>
				$("#submit_button").bind("click", function(e) {
        			e.preventDefault();
			        var formURL = "submit_choice.php";
			        var formData = {
			            "category": $("input[name=group1]:radio:checked").val(),
			            "implementation": $("input[name=group2]:radio:checked").val(),
			            "userid": "'.$userid.'"
			        };
			        $.ajax(
			        {
			            url : formURL,
			            data: formData,
			            async: true,
			            type: "GET",
			            success: function(data, textStatus, jqXHR)
			            {
			                $("#dialog").dialog("open");
			                window.location.reload();
			            },
			            error: function(jqXHR, textStatus, errorThrown)
			            {
			                alert(\'Something wrong, did you not select two choices?\');
			            }
			        });
        		});
        	</script>';

		return $ret;
	}


	public function overview($goal, $courses, $pd)
	{
		?>

		<style type="text/css">
		.tftable {font-size:18px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
		.tftable th {font-size:18px;color:#eee;background-color:#1662A3;border-width: 1px;padding: 8px;border-style: solid;border-color: #000;text-align:left;}
		.tftable tr {background-color:#d4e3e5;}
		.tftable td {font-size:18px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;}
		</style>

		<?php

		$info_by_seasons = array(
			"S1" => array(),
			"S2" => array(),
			"S3" => array()
			);
		$pattern = '/^\((.*?)\)/';  # start of string has a parens
		foreach ($courses as $course) {
			$season = preg_match($pattern, $course->fullname, $matches);
			if (!$season) {
				continue;
			}
			$season = $matches[1];
			$name = trim(preg_split($pattern, $course->fullname)[1]);
			if ($season == 'ALL') {
				foreach (array("S1", "S2", "S3") as $this_season) {
					$info_by_seasons[$this_season][] = $name;
				}
			} else {
				$info_by_seasons[$season][] = $name;
			}
		}
		$pd_data = json_decode($pd->data);

		$conflict = false;
		if ($pd_data->season and $something = $info_by_seasons[$pd_data->season]) {
			echo $this->output->sign('question-sign', 'PD and Activities Conflict?', 'Are you sure you want to double-book yourself like that?');
			$conflict = $pd_data->season;
		}

		$goal_data = json_decode($goal->data);

		if (!$goal_data) {
			echo $this->output->sign("plus-sign", 'No Goal Entered', 'Click "Enter Your Goal" tab to enter it.');
		} else {
			foreach (array(
				array('var'=>'department', 'name'=>'Department Goals'),
				array('var'=>'individual', 'name'=>'Individual Goal(s)'),
				array('var'=>'pastleadership', 'name'=>'Pastoral or Leadership Goal(s)'),
				array('var'=>'additional', 'name'=>'Additional Goal(s)')) as $item) {

				echo '<p style="font-size:18px;margin-left:50px;padding-top:10px;"><b>'.$item['name'].':</b> <br />';
				$variable = $item['var'];
				echo $goal_data->$variable;
				echo '</p>';
			}
		}

		$starttable = '<table class="tftable" border="1">';
		$startrow = '<tr>';
		$endrow = '</tr>';
		$endtable = '</table>';
		$seasons = array(
			"S1"=>"Season 1",
			"S2"=>"Season 2",
			"S3"=>"Season 3"
			);

		echo $starttable;
		echo $startrow;
		echo '<td style="color:#eee;background-color:#eee;"></td>';
		foreach ($seasons as $season) {
			echo '<th>'.$season.'</th>';
		}
		echo $endrow;

		echo $startrow;
		echo '<td><b>Activities</b></td>';
		foreach ($info_by_seasons as $items) {
			echo '<td>';
			foreach ($items as $item) {
				echo $item.'<br />';
			}
			echo '</td>';
		}
		echo $endrow;

		echo $startrow;
		echo '<td><b>PD</b></td>';
		foreach ($seasons as $season=>$data) {
			echo '<td>';

			if ($pd_data->season == $season) {
				if ($conflict) {
					echo '<b>'.$pd_data->strand.'</b>';
				} else {
					echo $pd_data->strand;
				}
			}
			echo '</td>';
		}
		echo $endrow;

		echo $endtable;

	}

	/**
	 * Show an array of courses as buttons, with a filter box
	 */
	public function activityList($courses, $url = '/course/view.php?id=', $listClasses = '')
	{
		global $PAGE;

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
