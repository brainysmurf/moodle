<?php
/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/
 *
 * MediaCore's local plugin
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');
global $CFG;
require_once($CFG->dirroot. '/mod/lti/locallib.php');

//constants
define('MEDIACORE_PLUGIN_NAME', 'local_mediacore');
define('MEDIACORE_SETTINGS_NAME', 'local_mediacore');


/**
 * A class that encapsulated the MediaCore Moodle Config
 * Config values in config_plugins table as local_mediacore
 */
class mediacore_config
{
    public $version;
    public $url = 'http://demo.mediacore.tv';
    public $consumer_key;
    public $shared_secret;
    private $_webroot;

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG, $DB;

        $this->_webroot = $CFG->wwwroot;
        $query = "SELECT *
                  FROM {config_plugins}
                  WHERE plugin = :plugin";

        $records = $DB->get_records_sql($query, array(
            'plugin' => MEDIACORE_SETTINGS_NAME,
        ));
        if (!empty($records)) {
            foreach ($records as $r) {
                if (!empty($r->value)) {
                    $this->{$r->name} = $r->value;
                }
            }
        }
    }

    /**
     * Whether lti is configured
     * @return boolean
     */
    public function has_lti_config() {
        return (!empty($this->url) &&
                !empty($this->consumer_key) &&
                !empty($this->shared_secret));
    }

    /**
     * Get the local_media plugin version
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get the mediacore url
     * @return string
     */
    public function get_url() {
        return rtrim($this->url, '/');
    }

    /**
     * Get the lti consumer key
     * @return string
     */
    public function get_consumer_key() {
        return $this->consumer_key;
    }

    /**
     * Get the lti consumer shared secret
     * @return string
     */
    public function get_shared_secret() {
        return $this->shared_secret;
    }

    /**
     * Get the moodle webroot
     * @return string
     */
    public function get_webroot() {
        return $this->_webroot;
    }
}

/**
 * The MediaCore Moodle Client
 * Encapsulated the client access endpoints and lti helpers
 */
class mediacore_client
{
    private $_config;
    private $_hostname;
    private $_port = '80';
    private $_scheme = 'http';
    private $_chooser_url = '/chooser';
    private $_chooser_js_url = '/api/chooser.js';
    private $_ieframe_url = '/chooser/ieframe_proxy';
    private $_media_api_url = '/api/media';
    private $_media_get_api_url = '/api/media/get';

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG;

        $this->_config = new mediacore_config();
        $this->_scheme = parse_url($this->_config->get_webroot(),
            PHP_URL_SCHEME);

        $mcore_url_components = parse_url($this->_config->get_url());
        $this->_hostname = $mcore_url_components['host'];
        if (isset($mcore_url_components['port'])) {
            $this->_port = $mcore_url_components['port'];
        }
    }

    /**
     * The mediacore_config object
     * @return mediacore_config
     */
    public function get_config() {
        return $this->_config;
    }

    /**
     * Whether the config is setup for lti
     * @return boolean
     */
    public function has_lti_config() {
        return $this->_config->has_lti_config();
    }

    /**
     * Get the mediacore base url
     * @return string|NULL
     */
    public function get_baseurl() {
        $ret = NULL;
        if (!empty($this->_hostname)) {
            $ret = $this->_scheme . '://' . $this->_hostname;
            if ($this->_port != '80' && $this->_port != '443') {
                $ret .= ':' . $this->_port;
            }
        }
        return $ret;
    }

    /**
     * Get the hostname and port alone
     * @return string|NULL;
     */
    public function get_hostname_and_port() {
        $ret = NULL;
        if (!empty($this->_hostname)) {
            $ret = $this->_hostname;
            if ($this->_port != '80' && $this->_port != '443') {
                $ret .= ':' . $this->_port;
            }
        }
        return $ret;
    }

    /**
     * Get the mediacore hostname
     * @return string|NULL
     */
    public function get_hostname() {
        return $this->_hostname;
    }

    /**
     * Get the mediacore port
     * @return string
     */
    public function get_port() {
        return $this->_port;
    }

    /**
     * Get the mediacore scheme
     * @return string
     */
    public function get_scheme() {
        return $this->_scheme;
    }

    /**
     * Get the moodle webroot
     * @return string
     */
    public function get_webroot() {
        return $this->_config->get_webroot();
    }

    /**
     * Get the chooser js url
     * LTI-signed if there's a course_id and an lti config
     * @return string
     */
    public function get_chooser_js_url() {
        return $this->get_baseurl() . $this->_chooser_js_url;
    }

    /**
     * Sign and return the chooser.js endpoint using LTI
     * XXX: Not used
     * @param string|int $course_id
     * @return string
     */
    public function get_signed_chooser_js_url($course_id) {
        $endpoint = $this->get_baseurl() . $this->_chooser_js_url;
        return $endpoint . '?' . $this->url_encode_params($this->get_signed_lti_params(
                $endpoint, $course_id)
            );
    }

    /**
     * Get the chooser url
     * @return string
     */
    public function get_chooser_url() {
        global $COURSE;
        return ($this->_config->has_lti_config() && isset($COURSE->id))
            ? $this->get_signed_chooser_url($COURSE->id)
            : $this->get_baseurl() . $this->_chooser_url;
    }

    /**
     * Sign and return the LTI-signed chooser endpoint
     * @param string|int $course_id
     * @return string
     */
    public function get_signed_chooser_url($course_id) {
        $endpoint = $this->get_baseurl() . $this->_chooser_url;
        return $endpoint . '?' . $this->url_encode_params($this->get_signed_lti_params(
                $endpoint, $course_id)
            );
    }

    /**
     * Get the ieframe proxy url
     * @return string
     */
    public function get_ieframe_url() {
        global $COURSE;
        return ($this->_config->has_lti_config() && isset($COURSE->id))
            ? $this->get_signed_ieframe_url($COURSE->id)
            : $this->get_baseurl() . $this->_ieframe_url;
    }

    /**
     * Sign and return the LTI-signed ieframe endpoint
     * @param string|int $course_id
     * @return string
     */
    public function get_signed_ieframe_url($course_id) {
        $endpoint = $this->get_baseurl() . $this->_ieframe_url;
        return $endpoint . '?' . $this->url_encode_params($this->get_signed_lti_params(
                $endpoint, $course_id)
            );
    }

    /**
     * Get the media api endpoint url
     * @return string
     */
    public function get_media_api_url() {
        return $this->get_baseurl() . $this->_media_api_url;
    }

    /**
     * Get the media/get api endpoint url
     * @return string
     */
    public function get_media_get_api_url() {
        return $this->get_baseurl() . $this->_media_get_api_url;
    }

    /**
     * Get the signed lti parameters
     * uses Oauth-1x
     * @param string $endpoint
     * @param int $course_id
     * @param array $params
     * @return array
     */
    public function get_signed_lti_params($endpoint, $course_id, $params=array()) {
        global $DB;

        if (!$this->_config->has_lti_config()) {
            die('There are no lti configuration params!');
        }
        if (empty($course_id)) {
            die('LTI signing must contain a course id!');
        }
        $course = $DB->get_record('course', array('id' => (int)$course_id), '*', MUST_EXIST);
        $key = $this->_config->get_consumer_key();
        $secret = $this->_config->get_shared_secret();
        $request_params = $this->get_lti_request_params($course);
        return lti_sign_parameters(array_merge($request_params, $params), $endpoint, 'GET', $key, $secret);
    }

    /**
     * Get the base lti request params
     * @param int $course_id
     * @return array
     */
    public function get_lti_request_params($course) {
        global $USER, $CFG;

        $user_given = (isset($USER->firstname)) ? $USER->firstname : '';
        $user_family = (isset($USER->lastname)) ? $USER->lastname : '';
        $user_full = (isset($USER->firstname, $USER->lastname))
            ? $USER->firstname . ' ' . $USER->lastname
            : '';
        $user_email = (isset($USER->email)) ? $USER->email: '';

        return array(
            'context_id' => $course->id,
            'context_label' => $course->shortname,
            'context_title' => $course->fullname,
            'debug' => ((boolean)$CFG->debugdisplay) ? 'true' : 'false',
            'ext_lms' => 'moodle-2',
            'lis_person_name_family' =>  $user_family,
            'lis_person_name_full' =>  $user_full,
            'lis_person_name_given' =>  $user_given,
            'lis_person_contact_email_primary' => $user_email,
            'lti_message_type' =>'basic-lti-launch-request',
            'lti_version' => 'LTI-1p0',
            'roles' => lti_get_ims_role($USER, 0, $course->id),
            'tool_consumer_info_product_family_code' => 'moodle',
            'tool_consumer_info_version' => (string)$CFG->version,
            'user_id' => $USER->id,
        );
    }

    /**
     * Get a curl response as JSON
     * @param string $url
     * @param array $params
     * @return string|FALSE|NULL
     */
    public function get_curl_response_as_json($url, $params=array()) {
        $options = array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_URL => $url . (strpos($url, '?') === FALSE ? '?' : '') .
                    $this->url_encode_params($params),
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) { //curl failed
            return FALSE;
        }
        $obj = json_decode($result);
        if (isset($obj->error)) { //no result found
            return NULL;
        }
        return $obj; //result found
    }

    /**
     * Urlencode the parameter values as a query string
     * @param array $params
     * @return string
     */
    public function url_encode_params($params) {
        $encoded_params = '';
        foreach ($params as $k=>$v) {
            $encoded_params .= "$k=" . urlencode($v) . "&";
        }
        return substr($encoded_params, 0 , -1);
    }


    /**
     * Get the custom tinymce params
     * TODO namespace these keys
     * @return array
     */
    public function get_tinymce_params() {
        global $CFG, $COURSE;
        if ($this->has_lti_config() && isset($COURSE->id)) {
            $lti_params = array(
                    'origin' => $this->get_webroot(),
                );
            $params['chooser_query_str'] = $this->url_encode_params(
                    $this->get_signed_lti_params(
                        $this->get_chooser_url(),
                        $COURSE->id,
                        $lti_params
                    )
                );
            $params['ieframe_query_str'] = $this->url_encode_params(
                    $this->get_signed_lti_params(
                        $this->get_ieframe_url(),
                        $COURSE->id,
                        $lti_params
                    )
                );
        }
        $params['chooser_js_url'] = $this->get_chooser_js_url();
        $params['host'] = $this->get_hostname_and_port();
        $params['scheme'] = $this->get_scheme();
        return $params;
    }
}

/**
 * A class that encapsulated results fetched from the media api endpoints
 */
class mediacore_media
{
    private $_client;
    private $_course;
    private $_course_id;
    private $_curr_pg;
    private $_has_next_pg;
    private $_has_prev_pg;
    private $_hostname;
    private $_limit;
    private $_next_pg;
    private $_pg_count;
    private $_prev_pg;
    private $_port;
    private $_rowset;
    private $_rowset_count;
    private $_scheme;
    private $_search;

    /**
     * Constructor
     * @param {mediacore_client} $client D.I.
     */
    public function __construct($client) {
        $this->_client = $client;
    }

    /**
     * Fetch media from the media api endpoint url
     * LTI signed if applicable
     * @param int $curr_pg
     * @param string $search
     * @param int $limit
     * @param int|NULL $course_id
     * @return array
     */
    public function fetch_media($curr_pg=0, $search='', $limit=6, $course_id=NULL) {

        $this->_curr_pg = $curr_pg; //zero-indexed
        $this->_search = $search;
        $this->_limit = $limit;

        $params = array(
            'type' => 'video',
            'limit' => $this->_limit,
            'offset' => $this->_curr_pg * $this->_limit,
            'search' => $this->_search,
        );

        if ($this->_client->has_lti_config() && $course_id) {
            $params = $this->_client->get_signed_lti_params($this->_client->get_media_api_url(),
                    $course_id, $params);
        }
        $result_obj = $this->_client->get_curl_response_as_json(
                $this->_client->get_media_api_url(), $params);
        if (empty($result_obj)) {
            return $result_obj;
        }

        //build result data
        $this->_rowset = $result_obj->media;
        $this->_rowset_count = $result_obj->count;
        $this->_pg_count = ceil($this->_rowset_count / $this->_limit);
        $this->_next_pg = $this->_curr_pg + 1;
        $this->_has_next_pg = ($this->_next_pg < $this->_pg_count);
        $this->_prev_pg = $this->_curr_pg - 1;
        $this->_has_prev_pg = ($this->_prev_pg >= 0);
        return $this->_rowset;
    }

    /**
     * Fetch the media embed
     * LTI signed if applicable
     * @param string $slug
     * @param int|NULL $course_id
     */
    public function fetch_media_embed($slug, $course_id=NULL) {
        $params = array('slug' => $slug);
        if ($this->_client->has_lti_config() && $course_id) {
            $params = $this->_client->get_signed_lti_params(
                $this->_client->get_media_get_api_url(), $course_id, $params);
        }
        $result_obj = $this->_client->get_curl_response_as_json(
                $this->_client->get_media_get_api_url(), $params);
        if (empty($result_obj)) {
            return $result_obj;
        }
        return ($this->_client->has_lti_config() && $course_id)
            ? $this->_get_embed_iframe_with_lti_params($result_obj->embed, $course_id)
            : $result_obj->embed;
    }

    /**
     * Get the iframe embed with signed lti parameters
     * @param string $embed_html
     * @param int $course_id
     * @return string
     */
    private function _get_embed_iframe_with_lti_params($embed_html, $course_id) {
        $baseurl_regex = '/' . str_replace('/', '\/', $this->_client->get_baseurl()) . '\/[^"]+/i';
        preg_match($baseurl_regex, $embed_html, $matches);
        if (isset($matches[0])) {
            $url_components = parse_url($matches[0]);
            $url = $this->_client->get_baseurl() . $url_components['path'];
            $params = array('iframe' => 'True');
            $lti_params = $this->_client->get_signed_lti_params($url, $course_id, $params);
            $new_iframe_src = $url . '?' . $this->_client->url_encode_params($lti_params);
            $embed_html = str_replace($matches[0], $new_iframe_src, $embed_html);
        }
        return str_replace('&', '&amp;', $embed_html);
    }


    /**
     * Get a media row object
     * @param object
     * @return mediacore_media_row
     */
    public function get_media_row($media) {
        return new mediacore_media_row($media);
    }

    /**
     * Get the current media rowset page number
     * zero-indexed
     * @return int
     */
    public function get_current_page() {
        return $this->_curr_pg;
    }

    /**
     * Get the current media rowset page number
     * @return int
     */
    public function get_current_page_str() {
        return '' . $this->_curr_pg + 1;
    }

    /**
     * Get the previous rowset page number
     * @return int
     */
    public function get_previous_page() {
        return $this->_prev_pg;
    }

    /**
     * Whether the rowset has a previous page
     * @return boolean
     */
    public function has_previous_page() {
        return $this->_has_prev_pg;
    }

    /**
     * Get the next rowset page number
     * @return int
     */
    public function get_next_page() {
        return $this->_next_pg;
    }

    /**
     * Whether the rowset has a next page
     * @return boolean
     */
    public function has_next_page() {
        return $this->_has_next_pg;
    }

    /**
     * Get the rowset page count
     * @return int
     */
    public function get_page_count() {
        return $this->_pg_count;
    }

    /**
     * Get the rowset page count string
     * i.e. Page 1 of 2
     * @return string
     */
    public function get_page_count_str() {
        if ($this->_pg_count > 0) {
            return 'Page ' . $this->get_current_page_str() . ' of ' .
                $this->get_page_count();
        } else {
            return '';
        }
    }

    /**
     * Get the current search query
     * @return string
     */
    public function get_search_query() {
        return $this->_search;
    }

    /**
     * Get the rowset count
     * @return int
     */
    public function get_rowset_count() {
        return $this->_rowset_count;
    }
}



/**
 * A class that encapsulated a media row object
 */
class mediacore_media_row
{
    private $_duration;
    private $_publish_on;
    private $_row_obj;
    private $_thumbs;
    private $_title;
    private $_url;

    /**
     * Constructor
     * @param object $media
     */
    public function __construct($media) {
        $this->_duration = $media->duration;
        $this->_publish_on = $media->publish_on;
        $this->_row_obj = $media;
        $this->_thumbs = $media->thumbs;
        $this->_title = $media->title;
        $this->_url = $media->url;
    }

    /**
     * Get the media item's url
     * @return string
     */
    public function get_url() {
        /**
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
         */
        $podcast = explode("/podcasts/", $this->_url);
        if (isset($podcast[1]) && $podcast[1]) {
            $uri = explode("/", $podcast[1]);
            $this->_url = $podcast[0] . "/media/" . $uri[1];
        }
        return $this->_url;
    }

    /**
     * Get the media item's small thumb url
     * @return string
     */
    public function get_thumbs_small_url() {
        return $this->_thumbs->s->url;
    }

    /**
     * Get the title
     * @return string
     */
    public function get_title() {
        return htmlentities($this->_title, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get the escaped title safe for js insertion
     * @return string
     */
    public function get_escaped_title() {
        return htmlentities(addslashes($this->_title), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get the duration in hh:mm:ss
     * @return int
     */
    public function get_duration() {
        return $this->_sec2hms($this->_duration, TRUE);
    }

    /**
     * Get the publish on date relative to today
     * @return string
     */
    public function get_publish_on() {
        return $this->_relative_time(strtotime($this->_publish_on));
    }

    /**
     * Convert seconds to hh:mm:ss
     * @param int $sec
     * @param bool $pad_hours
     */
    private function _sec2hms ($sec, $pad_hours=FALSE)
    {
        $hms = "";
        $sec = (int)$sec;

        $hours = (int)($sec/3600);

        $hms .= ($pad_hours)
            ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
            : $hours. ":";

        $minutes = (int)(($sec / 60) % 60);
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
        $seconds = (int)($sec % 60);
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

        return $hms;
    }

    /**
     * Convert the time to relative time
     * @param int $time
     */
    private function _relative_time($time)
    {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");

        $now = time();
        $difference = $now - $time;
        $tense = "ago";

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);
        if ($difference != 1) {
            $periods[$j].= "s";
        }
        return "$difference $periods[$j] ago ";
    }
}
