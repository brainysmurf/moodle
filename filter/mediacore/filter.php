<?php
/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/
 *
 * Automatic media embedding filter class.
 *
 * @package    filter
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Invalid access');
global $CFG;
require_once($CFG->dirroot . '/local/mediacore/lib.php');
require_once($CFG->libdir . '/filelib.php');


/**
 * Find instances of MediaCore.tv links and replace the link with embed code
 * from the MediaCore API.
 */
class filter_mediacore extends moodle_text_filter {

    private $_mcore_client;
    private $_mcore_media;

    /**
     * Constructor
     * @param object $context
     * @param object $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);
        $this->_mcore_client = new mediacore_client();
        $this->_mcore_media = new mediacore_media($this->_mcore_client);
    }

    /**
     * Filter the text
     * @param string $html
     * @param array $options
     * @return string
     */
    public function filter($html, array $options = array()) {
	if (strpos($html, '/podcasts/')) {
	    return $html;
	}

        if (empty($html) || !is_string($html) || stripos($html, '</a>' === FALSE) ||
        	!$this->string_contains_media_url($html)) {
                return $html;
            }
        $dom = new DomDocument();
        $dom->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));
        $xpath = new DOMXPath($dom);
        foreach($xpath->query('//a') as $node) {
            $href = $node->attributes->getNamedItem('href')->nodeValue;

            if ($this->string_contains_media_url($href)) {
                $new_node  = $dom->createDocumentFragment();
                $new_node->appendXML($this->_fetch_embed_code($href));
                $node->parentNode->replaceChild($new_node, $node);
            }

        }
        return $dom->saveHTML();
    }

	private function string_contains_media_url($string)
	{
		if (stripos($string, $this->_mcore_client->get_hostname()) !== false) {
			return true;
		}

		return false;
	}

    /**
     * Change links to MediaCore into embedded MediaCore videos
     * @TODO handle fetch error states
     * @return string
     */
    private function _fetch_embed_code($href) {

		// Always provide a link at least
		$link = '<a href="' . $href . '">'. $href . '</a>';

        global $COURSE;
        $course_id = (isset($COURSE->id)) ? $COURSE->id: NULL;
        $msg = get_string('filter_no_video_found', 'filter_mediacore');

        // Parse the link so we can get to the slug and type_id (if applicable).
        $uri_components = parse_url($href);
        $path_arr = explode('/', $uri_components['path']);
        $slug = end($path_arr);

        $result = $this->_mcore_media->fetch_media_embed($slug, $course_id);
        if (empty($result)) {
        	#return $this->_get_embed_error_html($msg, $result);
        	return $link;
        } else {
        	return $result;
		#return $link . '<br/>' . $result;
        }
    }


    /**
     * Get the error string
     * TODO check $bool for NULL (no result), FALSE (conn error)
     *      and display the appropriate message
     * @param string $msg The error message string
     * @return string
     */
    private function _get_embed_error_html($msg='', $bool) {
        if (empty($msg)) {
            $msg = get_string('filter_no_video_found', 'filter_mediacore');
        }
        return '<div class="mcore-no-video-found-error"><p>' . $msg . '</p></div>';
    }
}
