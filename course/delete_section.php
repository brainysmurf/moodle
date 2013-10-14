<?php

require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');

$id = required_param('courseid', PARAM_INT); // SECTION ID
$delete = optional_param('delete', '', PARAM_ALPHANUM); // delete confirmation hash

$site = get_site();

if (! $section = $DB->get_record("course_sections", array("id" => $id))) {
    print_error("Course section is incorrect");
}

if (! $course = $DB->get_record("course", array("id" => $section->course))) {
    print_error("Could not find the course!");
}

require_login($course->id);

$PAGE->set_url('/course/deletesection.php', array('id' => $id)); // Defined here to avoid notices on errors etc

$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_capability('moodle/course:manageactivities', $context);

if (!$delete) {
    $delete_section = get_string("deletesection");
    $delete_section_check = get_string("deletesectioncheck", '', $section->section);

    $navlinks[] = array('name' => $delete_section, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    echo $OUTPUT->header();

    echo $OUTPUT->confirm("{$delete_section_check}",
                 "deletesection.php?id={$section->id}&amp;delete=".md5($course->timemodified)."&amp;sesskey={$USER->sesskey}",
                 "view.php?id={$course->id}");

    echo $OUTPUT->footer();
    exit;
}

if ($delete != md5($course->timemodified)) {
    print_error("The check variable was wrong - try again");
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

delete_section($course->id, $section);

redirect("view.php?id=$course->id");

?>