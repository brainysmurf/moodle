<?php

// Allow caching for 30 minutes
session_cache_expire(30);
session_cache_limiter('public');

/**
 * Return an iCal compatible feed of a user's homework
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

$username = required_param('u', PARAM_RAW);
$key = required_param('k', PARAM_RAW);

// Get the user from their username
$user = $DB->get_record('user', array('username' => $username), '*', MUST_EXIST);

// Include the homework stuff
require dirname(__DIR__) . '/HomeworkBlock/Block.php';
$hwblock = new \SSIS\HomeworkBlock\Block;

$hwblock->userID = $user->id;

// Check the key
if ($key != $hwblock->generateFeedKey($user)) {
	die("Invalid key");
}

// Get the user's group (class) IDs
$groupIDs = $hwblock->getUsersGroupIDs($user->id, true);

$homework = $hwblock->getHomework(
	$groupIDs, //$groupIDs = false,
	false, //$courseIDs = false,
	false, //$assignedFor = false,
	true, //$approved = true,
	true ,//$distinct = true,
	false, //$past = false,
	false, //$dueDate = false,
	null, //$order = null,
	null, //$assignedRangeStart = null,
	null, //$assignedRangeEnd = null,
	true //$includePrivate = false
);


function formatDescription($text)
{
	$text = str_replace("\r\n", "\\n", $text);
	$text = str_replace("\n", "\\n", $text);
	#$text = htmlspecialchars($text);
	return $text;
}

function lines($text)
{
	// Using join instead of wordwrap because of https://bugs.php.net/bug.php?id=22487
	return join("\r\n ", str_split($text, 75));
}

/**
 * Output the feed...
 */

// the iCal date format. Note the Z on the end indicates a UTC timestamp.
define('DATE_ICAL', 'Ymd');

$eol = "\r\n";

$output = "BEGIN:VCALENDAR" . $eol .
	"VERSION:2.0" . $eol .
	"PRODID:-//project/author//NONSGML v1.0//EN" . $eol .
	"CALSCALE:GREGORIAN" . $eol;

foreach ($homework as $hw) {

	$url = $CFG->wwwroot . '/blocks/homework/hw.php?id=' . $hw->id;

	$timestamp = strtotime($hw->duedate);

	$desc = $hw->description;
	if ($notes = $hw->getNotes($user->id)) {
		$desc .= "\n---Your Notes---\n" . $notes;
	}

	$output .=
	"BEGIN:VEVENT" . $eol .
	"UID:" . $hw->id . $eol .
	"DTSTART;VALUE=DATE:" . date(DATE_ICAL, $timestamp) . $eol .
	"DTEND;VALUE=DATE:" . date(DATE_ICAL, strtotime("+1 DAY", $timestamp)) . $eol .
	"DTSTAMP:" . date(DATE_ICAL, $timestamp) . 'T000000' . $eol .
	lines("SUMMARY:" . htmlspecialchars($hw->getTitle())) . $eol .
	lines("DESCRIPTION:" . formatDescription($desc)) . $eol .
	"URL;VALUE=URI:" . htmlspecialchars($url) . $eol .
	"END:VEVENT" . $eol;
}

$output .= "END:VCALENDAR";

echo $output;
