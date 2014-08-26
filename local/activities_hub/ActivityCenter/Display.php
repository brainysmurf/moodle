<?php

/**
 * Class for generating HTML to display things in the Activity Center
 */

namespace SSIS\ActivityCenter;

class Display
{
    private $activityCenter;
    public $tabs = array( // Array of which tabs are shown in differnet modes
        'teacher' => array(
            'overview' => array('teacher/index.php', '<i class="icon-ok-sign"></i> Overview'),
            'goals' => array('teacher/goals.php', '<i class="icon-pencil"></i> Enter Your Goals'),
            'pdframework' => array('teacher/pd-framework.php', '<i class="icon-star"></i> Choose PD Strand'),
            'all-sec' => array('teacher/all-sec.php', '<i class="icon-rocket"></i> Pick Secondary Activities'),
            'all-elem' => array('teacher/all-elem.php', '<i class="icon-rocket"></i> Pick Elementary Activities'),
        ),
        'admin' => array(
            'activities' => array('session_mod.php?submode=activities&value=YES', '<i class="icon-rocket"></i> Modify Activities'),
            'individuals' => array('session_mod.php?submode=individuals&value=YES', '<i class="icon-user"></i> Manage Individuals'),
            'summary-sec' => array('session_mod.php?submode=individuals&value=YES', '<i class="icon-user"></i> Seconday Summary'),
            'summary-elem' => array('session_mod.php?submode=individuals&value=YES', '<i class="icon-user"></i> Elementary Summary'),

            'newactivity' => array('view.php?view=admin/newactivity', '<i class="icon-plus-sign"></i> Create New Activity')
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
        $PAGE->requires->css('/local/activities_hub/assets/css/styles.css');
        $PAGE->requires->jquery();
        $PAGE->requires->js(ActivityCenter::PATH . 'assets/js/activitycenter.js?v=3');
    }

    /**
     * Displays the tabs at the top of a page for switching mode and view (page)
     */
    public function showTabs($mode, $current)
    {
        $tabs = $this->tabs[$mode];

        $t = '';

        $t .= $this->modeTabs();

        $t .= '<div class="tabs">';
            $t .= '<ul>';
            foreach ($tabs as $name => $tab) {
                $t .= '<li>';
                    $t .= '<a ' . ($name == $current ? 'class="selected"': '') . 'href="' . $this->activityCenter->getPath() . $tab[0] . '">' . $tab[1] . '</a>';
                $t .= '</li>';
            }
            $t .= '</ul>';
        $t .= '</div>';

        return $t;
    }

    private function modeTabs()
    {
        $possibleModes = $this->activityCenter->getPossibleModes();
        $currentMode = $this->activityCenter->getCurrentMode();

        if (count($possibleModes) < 2) {
            return false;
        }

        $modeLabels = array(
            'teacher' => '<i class="icon-magic"></i> Teacher Mode',
            'student' => '<i class="icon-user"></i> Student Mode',
            'admin' => '<i class="icon-wrench"></i> Activity Admin Mode',
        );

        $t = '<div class="tabs noborder">';
        $t .= '<ul class="additional-tabs">';
        foreach ($possibleModes as $mode) {
            $t .= '<li>';
            $t .= '<a ' . ($mode == $currentMode ? 'class="selected"': '') . 'href="' . $this->activityCenter->getPath() . 'changemode.php?mode=' . $mode . '">' . $modeLabels[$mode] . '</a>';
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

        $ret .= '<p margin-top:10px;><b>A. Department Goal(s):</b>';
        $ret .= ' (Heads of Department/Subject Leaders <b>only</b>, enter the department goals here..)</p>';
        $ret .= '<textarea id="department_goal" class="filter" rows="3" style="font-size:18px;" />';
        $ret .= $info->department;
        $ret .= '</textarea>';

        $ret .= '<br/><p><b>B. Individual Goal(s):</b>';
        $ret .= ' (Enter one or two goals based on the Approaches to Teaching, see <a href="https://dragonnet.ssis-suzhou.net/pluginfile.php/74998/mod_resource/content/0/Goal%20Setting%20Guidance%202014-15.pdf" target="_new">Goal Setting Guidance</a>.)</p>';
        $ret .= '<textarea id="individual_goal" class="filter" rows="3" style="font-size:18px;" />';
        $ret .= $info->individual;
        $ret .= '</textarea>';

        $ret .= '<br/><p><b>C. Pastoral or Leadership Goal(s)</b>' ;
        $ret .= '(Enter only one goal. PoRs enter a SMART goal relevant to your leadership role.  Non-PoRs enter a goal relevant to your role of supporting student well-being.)</p>';
        $ret .= '<textarea id="pastleadership_goal" class="filter" rows="3" style="font-size:18px;" />';
        $ret .= $info->pastleadership;
        $ret .= '</textarea>';

        $ret .= '<br/><p><b>D. Additional Individual Goal(s):</b>';
        $ret .= ' (<b>Optional</b>. Additional goals that do not fit in the above headings e.g. some initiative, see <a href="https://dragonnet.ssis-suzhou.net/pluginfile.php/74998/mod_resource/content/0/Goal%20Setting%20Guidance%202014-15.pdf" target="_new">Goal Setting Guidance</a>.)</p>';
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
        if ($userid) {
            // Load the user's previous info to show what they had selected previously
            $usersChoices = $this->activityCenter->data->getUserPDSelection($userid, true);
        } else {
            $usersChoices = new \stdClass();
        }

        $ret = '<form id="choice_form" action="#">';

        $choices = array(
            array('text' => 'Learning Engagements (Inquiry)', 'value' => '(S1) Learning Engagement'),
            array('text' => 'Assessment & Feedback', 'value' => '(S2) Assessment & Feedback'),
            array('text' => 'Differentiation', 'value' => '(S3) Differentiation')
            );

        $ret .= '
        <style type="text/css">
        .tftable {font-size:18px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
        .tftable th {font-size:18px;color:#eee;background-color:#1662A3;border-width: 1px;padding: 8px;border-style: solid;border-color: #000;text-align:left;}
        .tftable tr {background-color:#d4e3e5;}
        .tftable td {font-size:18px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;}
        .tftable label {font-weight:normal; font-size:14px;}
        </style>';

        $starttable = '<table class="tftable" border="1">';
        $startrow = '<tr>';
        $endrow = '</tr>';
        $endtable = '</table>';
        $seasons = array(
            "S1" => "Season 1",
            "S2" => "Season 2",
            "S3" => "Season 3"
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
            $selected = !empty($usersChoices->strand) && $usersChoices->strand == $area['value'];
            $ret .= '<td><label><input type="radio" name="group1" value="'.$area['value'].'" '. ($selected ? 'checked="checked"' : '') . '> '.$area['text'].'</label></td>';
        }
        $ret .= $endrow;
        $ret .= $endtable;

        $ret .= '<br />';
        $ret .= $starttable;
        $ret .= $startrow;
        $ret .= '<td style="color:#eee;background-color:#eee;"></td>';
        $ret .= '<th>Survey: How would you prefer to engage in your chosen PD? (non-binding)</th>';
        $ret .= $endrow;

        $ret .= $startrow;
        $ret .= '<td><b>Choose How:</b></td>';

        $subchoices = array(
            array('text'=>' School Improvement Teams', 'value'=>'SIT'),
            array('text'=>' Reflective Teaching', 'value'=>'Reflective'),
            array('text'=>' Skills Share /  Teacher-led Workshop', 'value'=>'Skill Share')
            );

        $ret .= '<td>';
        foreach ($subchoices as $area) {
            $selected = !empty($usersChoices->choice) && $usersChoices->choice == $area['value'];
            $ret .= '<label><input type="radio" name="group2" value="' . $area['value'] . '" '. ($selected ? 'checked="checked"' : '') . '>' . $area['text'] . '</label><br/>';
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

    public function summaryList($rows) {
        ?>

        <style type="text/css">
        .tftable {font-size:14px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
        .tftable th {font-size:14px;color:#eee;background-color:#1662A3;border-width: 1px;padding: 8px;border-style: solid;border-color: #000;text-align:left;}
        .tftable tr {background-color:#d4e3e5;}
        .tftable td {font-size:14px;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;}
        </style>

        <?php

        $starttable = '<table class="tftable" border="1">';
        $startrow = '<tr>';
        $endrow = '</tr>';
        $endtable = '</table>';

        $r = $starttable;
        $pattern = '/^\((.*?)\)/';  # start of string has a parens

        $r .= $startrow;
        $r .= '<td></td>';
        foreach (array("S1", "S2", "S3") as $season) {
            $r .= '<th>';
            $r .= $season;
            $r .= '</th>';
        }
        $r .= $endrow;

        foreach ($rows as $row) {
            $r .= $startrow;

            $r .= '<th>';
            $r .= $row->firstname. ' ' . $row->lastname;
            $r .= '</th>';

            $info_by_seasons = array(
                "S1" => array(),
                "S2" => array(),
                "S3" => array()
                );

            $pd = json_decode($row->pd);
            if (empty($pd->strand)) {
                $pd->strand = 'Not chosen';
            }
            $info_by_seasons[$pd->season][] = '<strong>PD: '.$pd->strand.'</strong>';

            foreach (explode('|', $row->activities) as $activity) {
                $seasons = preg_match($pattern, $activity, $matches);
                if (!$seasons) {
                    continue;
                }
                $seasons = $matches[1];

                $name = preg_split($pattern, $activity);
                $name = trim($name[1]);
                if ($seasons == 'ALL') {
                    foreach (array("S1", "S2", "S3") as $this_season) {
                        $info_by_seasons[$this_season][] = $name;
                    }
                } else {
                    foreach (explode(",", $seasons) as $season) {
                        $info_by_seasons[$season][] = $name;
                    }
                }

            }

            foreach ($info_by_seasons as $season=>$activities) {
                $r .= '<td>';
                foreach ($activities as $activity) {
                    $r .= $activity.'<br/>';
                }
                $r .= '</td>';
            }

            $r .= $endrow;
        }
        $r .= $endtable;
        return $r;
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
            $seasons = preg_match($pattern, $course->fullname, $matches);
            if (!$seasons) {
                continue;
            }
            $seasons = $matches[1];

            $name = preg_split($pattern, $course->fullname);
            $name = trim($name[1]);
            if ($seasons == 'ALL') {
                foreach (array("S1", "S2", "S3") as $this_season) {
                    $info_by_seasons[$this_season][] = $name;
                }
            } else {
                foreach (explode(",", $seasons) as $season) {
                    $info_by_seasons[$season][] = $name;
                }
            }
        }
        $pd_data = json_decode($pd->data);

        $conflict = false;
        if ($pd_data->season and $something = $info_by_seasons[$pd_data->season]) {
            echo $this->output->sign('question-sign', 'Note: PD and Activities Conflict', 'Are you sure you want to double-book yourself like that? Please double check your choices', 'redAlert alert-danger');
            $conflict = $pd_data->season;
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
        $goal_array = array(
                array('var'=>'department', 'name'=>'Department Goals'),
                array('var'=>'individual', 'name'=>'Individual Goal(s)'),
                array('var'=>'pastleadership', 'name'=>'Pastoral or Leadership Goal(s)'),
                array('var'=>'additional', 'name'=>'Additional Goal(s)')
                );

        $goal_data = json_decode($goal->data);

        if (!$goal_data) {
            echo $this->output->sign("plus-sign", 'No Goal Entered', 'Click "Enter Your Goal" tab to enter it.', 'redAlert alert-danger');
        } else {
            echo $starttable;
            foreach ($goal_array as $item) {
                echo $startrow;

                echo '<th style="width:280px;">';
                echo $item['name'];
                echo '</th>';

                echo '<td>';
                $variable = $item['var'];
                echo '<label style="font-size:14px;font-weight:normal;">'.$goal_data->$variable.'</label>';
                echo '</td>';

                echo $endrow;
            }

            // echo $startrow;
            // foreach ($goal_array as $item) {
            //  echo '<td>';
            //  $variable = $item['var'];
            //  echo $goal_data->$variable;
            //  echo '</td>';
            // }
            // echo $endrow;
            echo $endtable;
        }

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
            echo '<td><label style="font-size:14px;font-weight:normal;">';
            foreach ($items as $item) {
                echo $item.'<br />';
            }
            echo '</label></td>';
        }
        echo $endrow;

        echo $startrow;
        echo '<td><b>PD</b></td>';
        foreach ($seasons as $season=>$data) {
            echo '<td>';
            if ($pd_data->season == $season) {
                echo '<label style="font-size:14px;font-weight:normal;">';
                if ($conflict) {
                    echo '<strong class="red">'.$pd_data->strand.'</strong>';
                } else {
                    echo $pd_data->strand;
                }
                echo '</label>';
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
        global $PAGE, $CFG;

        require_once $CFG->libdir . '/ssismetadata.php';
        $courseMetaData = new \ssismetadata();

        $r  = '<div class="courseList ' . $listClasses . '">';
        $r .= '<input type="text" class="filter" placeholder="Type here to filter by name..." />';
        $r .= '<div class="row courses">';

        global $USER;

        foreach ($courses as $course) {

            // Find the activity manager
            $supervisors = $this->activityCenter->data->getActivitiesManaged($course->id);
            $supervisorNames = array();

            $iamasupervisor = false;
            foreach ($supervisors as $supervisor) {
                $supervisorNames[] = $supervisor->firstname . ' ' . $supervisor->lastname;
                if ($supervisor->userid == $USER->id) {
                    $iamasupervisor = true;
                }
            }

            $supervisorCount = count($supervisors);
            $supervisorsNeeded = $courseMetaData->getCourseField($course->id, 'activitysupervisor');
            if ($supervisorsNeeded == null or $supervisorsNeeded == 0) {
                continue;
            }

            $icon = course_get_icon($course->id);

            if ($supervisorsNeeded > 0 && $supervisorCount >= $supervisorsNeeded) {
                $color = 'danger';
            } elseif ($supervisorsNeeded > 0) {
                $color = 'success';
            } else {
                $color = '';
            }

            if ($iamasupervisor) {
                $color = 'mine';
            }

            $r .= '<div class="col-sm-3"><a href="' . ($url ?  $url . $course->id : '#') . '" class="btn ' . ($color ? 'btn-' . $color : '') . '" data-courseid="'. $course->id . '" data-fullname="' . $course->fullname . '">';

                if (preg_match_all('/\((([S1|S2|S3|ALL|FULL],?)+)\)/i', $course->fullname, $matches)) {

                    foreach ($matches[0] as $i => $matchedText) {

                        if ($matches[1][$i] === 'ALL') {
                            $matches[1][$i] = 'S1,S2,S3';
                        }

                        $icon = '<i class="pull-right icon-text">' . $matches[1][$i] . '</i>';
                        $course->fullname = str_replace($matchedText, $icon, $course->fullname);
                        $course->fullname = trim($course->fullname);
                    }

                }

                $r .= $course->fullname;

                $r .= '<span>' . $supervisorCount . '/' . $supervisorsNeeded . ' supervisors</span>';

                if ($iamasupervisor) {
                    $r .= '<span><i class="icon-heart"></i> You are a supervisor! <i class="icon-heart"></i></span>';
                }

                $r .= '<span class="desc" style="display:none;">' . htmlentities($course->summary) . '</span>';

            $r .= '</a></div>';
        }

        $r .= '</div>';
        $r .= '<div class="clear"></div>';
        $r .= '</div>';

        return $r;
    }
}
