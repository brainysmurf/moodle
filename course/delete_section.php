<?php

require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');

$courseid = required_param('courseid', PARAM_INT); //Course ID
$section = required_param('section', PARAM_INT); // Section number
$delete = optional_param('delete', '', PARAM_ALPHANUM); // delete confirmation hash

$site = get_site();

//Get course by ID
if ( !$course = $DB->get_record("course", array("id" => $courseid)) )
{
    print_error("Could not find that course");
}

//Get section
if ( !$section = $DB->get_record("course_sections", array("course" => $courseid , "section" => $section)) )
{
    print_error("Invalid section");
}

//Make sure user has permission to edit sections
require_login($course->id);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:update', $context);

//Set page title
$PAGE->set_heading($course->fullname);
$PAGE->set_title('Delete section');
$PAGE->set_url('/course/delete_section.php', array('id' => $id)); // Defined here to avoid notices on errors etc

if ( $delete ) //Perform the delete
{
	if ( $delete != md5($course->timemodified) )
	{
	    print_error("The check variable was wrong - try again");
	}
	
	if ( !confirm_sesskey() )
	{
	    print_error('confirmsesskeybad', 'error');
	    exit;
	}
		
	delete_section($course->id, $section->id);

	redirect("view.php?id=$course->id");
}
else //Show confirmation page
{
    $delete_section = get_string("deletesection");

	$text = 'Are you sure you want to delete <strong>'.get_section_name($course, $section).'</strong> from <strong>'.$course->fullname.'</strong>?
	<br/>All activities and resources in this section will be deleted.
	<br/>This is <strong>not</strong> the same as hiding a section - this cannot be undone!';

    $navlinks[] = array('name' => $delete_section, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    echo $OUTPUT->header();

    echo $OUTPUT->confirm($text,
                 "delete_section.php?courseid=$courseid&amp;section={$section->section}&amp;delete=".md5($course->timemodified),
                 "view.php?id={$course->id}");

    echo $OUTPUT->footer();
    exit;
}

?>