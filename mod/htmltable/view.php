<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * htmltable module version information
 *
 * @package    mod
 * @subpackage htmltable
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/htmltable/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // htmltable instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$page = $DB->get_record('htmltable', array('id'=>$p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('htmltable', $page->id, $page->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('htmltable', $id)) {
        print_error('invalidcoursemodule');
    }
    $page = $DB->get_record('htmltable', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/htmltable:view', $context);

add_to_log($course->id, 'htmltable', 'view', 'view.php?id='.$cm->id, $page->id, $cm->id);

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/htmltable/view.php', array('id' => $cm->id));

$options = empty($page->displayoptions) ? array() : unserialize($page->displayoptions);

if ($inpopup and $page->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$page->name);
    if (!empty($options['printheading'])) {
        $PAGE->set_heading($page->name);
    } else {
        $PAGE->set_heading('');
    }
    echo $OUTPUT->header();

} else {
    $PAGE->set_title($course->shortname.': '.$page->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($page);
    echo $OUTPUT->header();

    if (!empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($page->name), 2, 'main', 'pageheading');
    }
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($page->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'pageintro');
        echo format_module_intro('htmltable', $page, $cm->id);
        echo $OUTPUT->box_end();
    }
}


	$content = $page->content;
	$content = json_decode($content);
	
	require_once $CFG->dirroot.'/lib/markdown.php';
	
	//Replace empty cells with &nbsp; so that they have some content and appear the right size
	foreach ( $content as &$row )
	{
		foreach ( $row as &$value )
		{
			if ( !$value ) { $value = '&nbsp;'; }
			$value = Markdown($value);
		}
	}
	
	//Add table content to arrays to display
	$head = array();
	$data = array();
	foreach ( $content as $i => $row )
	{
		if ( $i == 0 )
		{
			$head = $row;
		}
		else
		{
			$data[] = $row;
		}
	}
	
	$table = new html_table();
    $table->attributes['class'] = 'userinfotable htmltable';
    $table->head = $head;
    $table->width = '100%';
    $table->data = $data;
	
	echo '<div class="generalbox">';
		echo html_writer::table( $table );
	echo '</div>';

$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($page->timemodified)."</div>";

echo $OUTPUT->footer();
