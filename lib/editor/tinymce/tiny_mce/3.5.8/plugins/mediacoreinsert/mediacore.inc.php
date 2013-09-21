<?php
/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/   
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___   
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/   
 *                       TinyMCE Plugin
 *
 */

$media_api = $mediacore_url . '/api/media';
// TODO The following need to be escaped, but htmlspecialchars throws 500 error
$pager = $_GET["page"];
$searchquery = $_GET["search"];
$previous = $pager - $max_per_page;
$next = $pager + $max_per_page;
$offset = $pager;
if($searchquery) {
	$searchlabel = $searchquery;
} else {
	$searchlabel = "Search";
}
$result = fetch_media_list(array(
  'type' => 'video',
  'limit' => $max_per_page,
  'offset' => $offset,
  'search' => $searchquery
));
$videos = $result->media;
$counted = $result->count;
$howmanypages = ceil($counted / $max_per_page);
$currentpage = $offset / $max_per_page + 1;
if($currentpage >= $howmanypages) $maxedout = 1;

/**
 * Functions
 */

function fetch_media_list($data) {
  global $media_api;
  $uri = $media_api . '?' . http_build_query($data);
  return json_decode(file_get_contents($uri));
}

/**
 *
 * Because the API doesn't provide the embed URL separately (obscures it in the
 * iframe src) and instead supplies a permalink which can differ (e.g. podcasts) 
 * from the direct play URL, we need to intercept podcast URIs and reformat them
 * so that the iframe can play it properly; if we pass the given permalink e.g.
 * 
 * http://demo.mediacore.tv/podcasts/imperial-rome-and-ostia/the-construction-of-imperial-rome
 *
 * straight through, the iframe comes up 404. We need to replace the "podcasts"
 * with "media" and strip out the second portion (third portion is the slug) of 
 * the URI entirely so that the above example would read: 
 *
 * http://demo.mediacore.tv/media/the-construction-of-imperial-rome
 *
 */
function embeddable($url) {
	$podcast = explode("/podcasts/", $url);
	if($podcast[1]) {
		$uri = explode("/", $podcast[1]);
		$url = $podcast[0] . "/media/" . $uri[1];
	}
	return $url;
}


function sec2hms ($sec, $padHours = false) 
{

// start with a blank string
$hms = "";

// do the hours first: there are 3600 seconds in an hour, so if we divide
// the total number of seconds by 3600 and throw away the remainder, we're
// left with the number of hours in those seconds
$hours = intval(intval($sec) / 3600); 

// add hours to $hms (with a leading 0 if asked for)
$hms .= ($padHours) 
      ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
      : $hours. ":";

// dividing the total seconds by 60 will give us the number of minutes
// in total, but we're interested in *minutes past the hour* and to get
// this, we have to divide by 60 again and then use the remainder
$minutes = intval(($sec / 60) % 60); 

// add minutes to $hms (with a leading 0 if needed)
$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

// seconds past the minute are found by dividing the total number of seconds
// by 60 and using the remainder
$seconds = intval($sec % 60); 

// add seconds to $hms (with a leading 0 if needed)
$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

// done!
return $hms;

}


function ago($time)
{
   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");

   $now = time();

       $difference     = $now - $time;
       $tense         = "ago";

   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }

   $difference = round($difference);

   if($difference != 1) {
       $periods[$j].= "s";
   }

   return "$difference $periods[$j] ago ";
}
