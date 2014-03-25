<?php
/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/   
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___   
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/   
 *                        Content Filter
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');

/**
 * Find instances of MediaCore.tv links and replace the link with embed code 
 * from the MediaCore API.
 */
class filter_mediacorefilter extends moodle_text_filter {
	function filter($text, array $options = array()) {
		global $CFG;

		if (!is_string($text) or empty($text)) {
			// non string data can not be filtered anyway
			return $text;
		}
		if (stripos($text, '</a>') === false) {
			// performance shortcut - all regexes bellow end with the </a> tag,
			// if not present nothing can match
			return $text;
		}

		$newtext = $text; // we need to return the original value if regex fails!
		
		/**
		 * TODO
		 * The following doesn't work correctly; we get an array with only letters:
		 *
		 * [0]=>"<a href="http://link.mediacore.tv/media/video">link</a>", 
		 * [1]=>"n", 
		 * [2]=>"N";
		 *
		 * This effects the actual filter function below.
		 *
		 */
		$search = '/<a\s[^>]+http:\/\/([0-9A-Za-z])+\.mediacore\.tv\/([0-9A-Za-z])[^>]+>([0-9A-Za-z])+[^>]+>/';
		$newtext = preg_replace_callback($search, 'filter_mediacorefilter_callback', $newtext);
		
		if (empty($newtext) or $newtext === $text) {
			  // error or not filtered
			  mtrace('link empty');
			  unset($newtext);
			  return $text;
		}
		
		return $newtext;
	}

}

/**
 * Change links to MediaCore into embedded MediaCore videos
 *
 * @param  $link
 * @return string
 */
function filter_mediacorefilter_callback($link) {

	global $CFG;

	if (filter_mediacorefilter_ignore($link[0])) {
		return $link[0];
	}
	//
	// TODO fix the regex so that we can construct the URL from the passed array
	// since I can't make it pass us the correct variables (see above).
	//
	// Since the regex passes us the HTML link, first we have to parse the link 
	// so we can get to the slug.
	//
	$murl = explode('href=', $link[0]);
	$mcore = explode('"', $murl[1]);
	$uri_elements = explode("/", $mcore[1]);
	$slug = end($uri_elements);
	//
	// Then use the slug to query the MediaCore API to get the embed code
	//
	$media_api = $CFG->filter_mediacorefilter_url . '/api/media/get?slug=' . $slug;
	$result = json_decode(file_get_contents($media_api));
	if($result) {
		$output = $result->embed;
	} else {
		$output = '<p><em>No video found.</em></p>';
	}
	return $output;
}


/**
 * Should the current tag be ignored in this filter?
 * @param string $tag
 * @return bool
 */
function filter_mediacorefilter_ignore($tag) {
	if (preg_match('/class="[^"]*nomediaplugin/i', $tag)) {
		return true;
	} else {
		false;
	}
}


