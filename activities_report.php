<?php

/*
	This page lists all the icons available in fontawesome
*/

require_once(dirname(__FILE__) . '/config.php');
require_login();

$PAGE->set_url('/activities_report.php');
$PAGE->set_title('Activity Report for Parents');
$PAGE->set_heading("Your Childrens' Activities");

echo $OUTPUT->header();

# sql query
global $DB;
global $USER;
$family_id = str_replace('P', '', $USER->idnumber);
if (!empty($family_id)) {
	$results = $DB->get_recordset_sql("select crs.id as course_id, concat(usr.firstname, ' ', usr.lastname), regexp_replace(crs.fullname, '\(.*\)', '') as fullname from ssismdl_enrol enrl join ssismdl_user_enrolments usrenrl on usrenrl.enrolid = enrl.id join ssismdl_course crs on enrl.courseid = crs.id join ssismdl_user usr on usrenrl.userid = usr.id where visible = 1 and usr.idnumber like '".$family_id."%' and usr.idnumber not like '%P' and enrl.enrol = 'self'");
	if (!empty($results)) {
		echo '<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>If you wish to <strong>remove a child from an activity</strong>, click on the link and then, on that page, go to the <strong>"Course Administration"</strong> menu and choose the item to remove.</div>';
		echo "<br />";

		echo '<table class="userinfotable htmltable" width="100%">';
		echo "<thead>";

		foreach ($results as $item) {
			echo '<tr class="r0 lastrow">';
			echo '<td class="cell c0"><p>'.$item->concat.'</p></td>';
			echo '<td class="cell c1 lastcol"><p>'.'<a target="_new" href="http://dragonnet.ssis-suzhou.net/course/view.php?id='.$item->course_id.'">'.$item->fullname.'</a></p></td>';

			echo '</tr>';
		}
		$results->close();

	} else {

		echo '<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>This page will display a list of activites your children are enrolled in.</div>';

	}

	echo "<tbody>";

} else {
	echo '<div class="local-alert"><i class="icon-thumbs-down pull-left icon-4x"></i><strong>Apologies, there is something wrong with your account.</strong> <br />Please contact Adam Morris at adammorris@ssis-suzhou.net with the subject header "Message from DragonNet".<br />Be sure to include your DragonNet username. Either that, or your account isn\'t a parent account."</a>"';
}


# loop



#<tr class="r0 lastrow">
#<td class="cell c0" style=""><p>Happy Student</p></td>
#<td class="cell c1 lastcol" style=""><p>Some activity</p></td>
#</tr>
echo '</tbody></table>';

echo $OUTPUT->footer();
