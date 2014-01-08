<?php

/*
	Class for creating the navigtion bar at the top of DragonNet
*/

class awesomebar
{
	private $cache; //Awesomebar cache store
	private $use_cache; //Should we use a cached version of the menu?
	private $save_in_cache; //Should we save the menu in the cache?
	private $page; //Reference to the current page

	function __construct(&$page)
	{
		$this->page = $page;
		global $CFG;
		require_once($CFG->libdir . '/coursecatlib.php');
		require_once($CFG->dirroot . '/course/lib.php');

		//Setup the cache (for this session)
		$this->cache = cache::make_from_params(cache_store::MODE_SESSION, 'theme_decaf', 'awesomebar');

		//Should we use the cache?
		$this->use_cached = true;
		$this->save_in_cache = true;

		//For development - add ?refreshawesomebar to the URL to update the menu
		if (isset($_GET['refreshawesomebar'])) {
			$this->use_cached = false;
		}

		//If we're on the site admin course page, don't use a cached version and don't create a cached version
		if ($this->page->course->id == '1266') {
			$this->use_cached = false;
			$this->save_in_cache = false;
		}
	}

	// !HTML Rendering

	/*
	* Returns the complete HTML for the awesomebar menus
	*/
	public function create($forceRefresh = false)
	{
		if ($forceRefresh) {
			$this->use_cached = false;
			$this->save_in_cache = true;
		}
	
		//Begin ul
		$content = html_writer::start_tag('ul', array('class' => 'dropdown dropdown-horizontal'));

		//Login Button / User Menu (returns an li)
		//We always use a fresh user menu
		$content .= $this->render_array_as_html_list($this->get_user_menu());

		//Get the rest of the menu
		$content .= $this->get_static_menus();

		//End ul
		$content .= '</ul>';

		return $content;
	}

	/*
	* Returns the HTML for the cacheable parts of the menu
	* (Navigate onwards - user menu doesn't get cached)
	*/
	private function get_static_menus()
	{
		//Now the rest of the menu might be cached, if so we append that and return
		if ($this->use_cached && $content = $this->cache->get('awesomebar')) {
			return $content;
		}

		$content = '';

		global $SESSION;

		$loggedIn = isloggedin();
		if ($loggedIn) {

			//Navigate (Custom menu from decaf theme settings)
			$custom_menus = $this->get_custom_menus();
			if (!empty($custom_menus)) {
				$content .= $this->render_array_as_html_list($custom_menus);
			}
			unset($custom_menus);

			//Course Menus
			//Each top level category becomes a button on the awesomebar
			//The dropdown for that button shows the courses / categories within it
			//We don't show courses on page 1266 so that there's more room for the site admin menu
			if (!isguestuser() && $this->page->course->id != '1266') {
				$menu = $this->get_course_menus();
				$content .= $this->render_array_as_html_list($menu);
				unset($menu);
			}
		}

		$content .= html_writer::end_tag('ul'); // end whole ul

		if ($this->save_in_cache) {
			//Save in the cache
			$this->cache->set('awesomebar', $content);
		}

		return $content;
	}

	/*
	*	Takes an array and builds an HTML menu from it
	*/
	private function render_array_as_html_list(array $menu, $depth = 0)
	{
		global $CFG, $USER, $SESSION;
		require_once($CFG->dirroot.'/cohort/lib.php');
			
		$html = '';
		foreach ($menu as $item) {
			
			//Restricted to certain cohorts
			if (!empty($item['cohorts']) && !$SESSION->userIsSiteAdmin) {
			
				$canSee = false;
				
				foreach ($item['cohorts'] as $cohort) {
					if (cohort_is_member_by_idnumber($cohort, $USER->id)) {
						$canSee = true;
						break;
					}
				}
				
				if (!$canSee) {
					continue;
				}
			}
			
			//Just an <hr> tag?
			if (!empty($item['hr'])) {
				$html .= html_writer::empty_tag('hr');
				continue;
			}

			if (!empty($item['icon'])) {
				$icon = html_writer::tag('i', '', array('class' => 'pull-left icon-' . $item['icon']));
			} else {
				$icon = false;
			}
			
			//A header?
			if (!empty($item['header'])) {
				$headerText = html_writer::tag('span', $item['header']);
				$html .= html_writer::tag('li', $icon . $headerText, array('class' => 'header'));
				//if (!empty($item['submenu'])) {
				//	echo 'goinside';
				//	foreach ($item['submenu'] as $submenuitem) {
				//		$html .= html_writer::tag('li', $submenuitem['text']);
				//	}
				//}
				continue;
			}

			$html .= html_writer::start_tag('li');

			$hasSubmenu = !empty($item['submenu']);

			if ($hasSubmenu && $depth > 0) {
				//Right arrow
				$html .= html_writer::tag('i', '', array('class' => 'pull-right icon-caret-right'));
			}

			if (!empty($item['url'])) {
				$html .= html_writer::tag('a', $icon . $item['text'], array('href' => $item['url']));
			} else {
				$html .= html_writer::tag('span', $icon . $item['text']);
			}

			if ($hasSubmenu) {
				$html .= html_writer::start_tag('ul');
				$html .= $this->render_array_as_html_list($item['submenu'], $depth + 1);
				$html .= html_writer::end_tag('ul');
			}

			$html .= html_writer::end_tag('li');
		}
		return $html;
	}

	private function is_beta_tester() {
		// returns true if the current user is a beta tester
		global $USER, $DB;

		#if (in_array($USER->username, array('admin', 'geoffreyderry', 'bevanjames', 'sammyadams'))) {
		#	return true;
		#}

		$courseid = $DB->get_field('course', 'id', array('fullname'=>'Beta Test'));
		if (empty($courseid)) {
			echo 'make an activity called "Beta Test" and self enrol into it to get the new Dragonnet menu    ';
			return false;
		}
		$enrolid = $DB->get_field('enrol', 'id', array('courseid'=>$courseid, 'enrol'=>'self'));
		$beta_testers = $DB->get_records('user_enrolments', array('enrolid'=>$enrolid));
		$beta_tester_array = array();
		foreach ($beta_testers as $beta_tester) {
			$beta_tester_array[] = $beta_tester->userid;
		}

		return in_array($USER->id, $beta_tester_array);
	}

	// !Custom Menus (Navigate)

	/*
	* Returns the HTMLified custom menus from the "custom menu items" text area in the decaf theme settings admin page
	*/
	private function get_custom_menus()
	{
		// DragonNet beta testers get a different menu

		if (empty($this->page)) {
			return false;
		}

		if ( $this->is_beta_tester() ) {
			// gets custom menu from decaf theme
			$custommenuitems = $this->page->theme->settings->custommenuitems;
		} else {
			// gets from moodle's built-in custom menu
			global $CFG;
			$custommenuitems = $CFG->custommenuitems;
		}
		
		return $this->convert_custom_menu_text_to_array($custommenuitems);
	}



	/**
	 * Convert the custom menu defined in the textarea in theme settings to an array of menu items
	 *
	 * Based on custom_menu::convert_text_to_menu_nodes
	 * found in /lib/outputcomponents:2763
	 * But we return an array instead of a custom_menu object
	 * Plus support for headers & permissions
	 *
	 * Structure:
	 *	   text|url|icon|cohorts
	 *	   The number of hyphens at the start determines the depth of the item.
	 *
	 * Example structure:
	 
			Search Engines Menu||icon-search
			-Google|http://www.google.com|icon-google-plus
			--Google Docs|http://docs.google.com|icon-paper-clip
			--Google Maps|http://maps.google.com|icon-map-marker
			---UK|http://maps.google.co.uk|icon-map-marker
			---HK|http://mail.google.com.hk|icon-map-marker
			--Google Mail|http://mail.google.com|icon-envelope
			-Microsoft|http://www.microsoft.com|icon-desktop
			--Bing|http://www.bing.com|icon-search
			Another Menu||icon-home
			-Child|http://www.example.com
			-Another Child|http://www.example.com
			
	 * Returns an array of menu arrays
	 * @param string $text the menu items definition
	 * @return array
	 */
	public static function convert_custom_menu_text_to_array($text) {
		
		$menus = array();
	
		$lines = explode("\n", $text); //Split the text into lines

		$lastDepth = 0;
		$itemsAtDepth = array();
		
		foreach ($lines as $line) {
		
			$line = trim($line); //Remove whitespace from line

			//How many dashes at the start?
			if (preg_match('/^(\-*)/', $line, $match)) {
				$depth = strlen($match[1]);
			} else {
				$depth = 0;
			}
			
			$line = ltrim($line, '-'); //Done with the dashes now, remove them from the line
			
			$menuItem = array();
			
			$bits = explode('|', $line, 4); //Split by | (into 4 parts at most)
			
			//Title
			if (!empty($bits[0])) {
				
				//Horizontal lines
				if ($bits[0] == 'hr') {
					$menuItem['hr'] = true;
				} elseif (preg_match('/^header\=(.+)$/i', $bits[0], $matches)) {
					$menuItem['header'] = $matches[1];
				} else {
					$menuItem['text'] = $bits[0];
				}
				
			} else {
				//All items must at least have a name. If no name, skip this item
				continue;
			}
			
			//URL
			if (!empty($bits[1])) {
				$menuItem['url'] = $bits[1];
			}
			
			//Icon
			if (!empty($bits[2])) {
				$menuItem['icon'] = $bits[2];
			}
			
			//Permission (who can view)
			//Comma seperated list of cohorts
			//If specified, only those cohorts can see it. Otherwise everyone can
			if (!empty($bits[3])) {
				$menuItem['cohorts'] = explode(',', $bits[3]);
			}
			
			$menuItem['submenu'] = array();
			
			//Icons can be specified with or without the "icon-" at the start
			//Remove icon- from icon names
			if (isset($menuItem['icon'])) {
				$menuItem['icon'] = preg_replace('/^(icon\-)/i', '', $menuItem['icon']);
			}
			
			//Now add the menu item to the menu...
			if ($depth == 0) {
			
				//Depth = 0 means a new top level menu straight on the awesomebar
				//Just stick it at the end of the menus array
				$menus[] = $menuItem;
				
			} else if ($depth > 0) {
			
				if ($depth > $lastDepth) {
					//Started a new submenu
					$itemsAtDepth[$depth] = 0;
				}
			
				//Where do we put it...
				//This is a bit gross. We build an array index to get a reference to the submenu element we want to add the item to
				$path = '';
				for ($i = 0; $i < $depth; ++$i) {
					$path .= '[' . ($itemsAtDepth[$i]-1) . ']["submenu"]';
				}
				$submenu = eval('$submenu =& $menus'.$path.'; return $submenu;'); //Get a reference to the right submenu in the array
				$submenu[] = $menuItem; //Add this menu item to the submenu
				unset($submenu); //Unlink the reference
			}
			
			$lastDepth = $depth;
			if (isset($itemsAtDepth[$depth])) {
				++$itemsAtDepth[$depth];
			} else {
				$itemsAtDepth[$depth] = 1;
			}
			
		}
		
		return $menus;
	}


	// !User Menu

	/*
	*	Create the 'User' menu (or the Login button if user isn't logged in)
	*/
	private function get_user_menu($loggedIn = false)
	{
		$menu = array();
		$loggedIn = isloggedin();

		if (!$loggedIn) {
			$menu[] = array('text' => 'Login', 'icon' => 'signin', 'url' => '/login'); //Login button
		} else {
			//Current user button
			global $USER, $DB;

			$course = $this->page->course;

			//Show username
			if (session_is_loggedinas()) //Used 'login as' to login as a different user
			{
				$realuser = session_get_realuser();
				$menu[] = array('icon' => 'user', 'text' => fullname($realuser) . ' -> ' . fullname($USER));
			} else if (!empty($course->id) && is_role_switched($course->id)) //Role is switched
			{
				//Find out what role we switched to
				$context = context_course::instance($course->id);
				$rolename = '';
				if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
					$rolename = role_get_name($role, $context);
				}
				$loggedinas = get_string('loggedinas', 'moodle', $username) . $rolename;

				$menu[] = array('icon' => 'user', 'text' => $rolename);
			} else //Normal user
			{
				$menu[] = array('icon' => 'user', 'text' => fullname($USER));
			}

			$submenu = array();

			if (isguestuser()) {
			
				// Any special items for guest users??
			
			} else {
			
				//Items for regular logged in users

				$courseid = $this->page->course->id;
				$context = context_course::instance($courseid);



				if (session_is_loggedinas()) {
					if ($this->is_beta_tester()) {
						$submenu[] = array('header'=> 'Change Identity');				
				 	}
					//Undo 'login as user'
					$submenu[] = array(
						'text' => 'Return to normal',
						'icon' => 'user',
						'url' => new moodle_url('/course/loginas.php', array(
							'id' => $courseid,
							'sesskey' => sesskey(),
							'switchrole' => 0,
							'returnurl' => $this->page->url->out_as_local_url(false)
						))
					);
					
				} elseif (is_role_switched($courseid)) {
					if ($this->is_beta_tester()) {
						$submenu[] = array('header'=> 'Change Identity');
					}

					//Role is switched - show 'Return to normal' button
					$submenu[] = array(
						'text' => 'Return to normal',
						'icon' => 'user',
						'url' => new moodle_url('/course/switchrole.php', array(
							'id' => $courseid,
							'sesskey' => sesskey(),
							'switchrole' => 0,
							'returnurl' => $this->page->url->out_as_local_url(false)
						))
					);

				} elseif (has_capability('moodle/role:switchroles', $context)) {
					if ($this->is_beta_tester()) {
						$submenu[] = array('header'=> 'Change Identity');
					}
					//Become Student button
					$roles = get_switchable_roles($context);
					if (count($roles) > 0 && $role = $roles[5]) {
						$submenu[] = array(
							'text' => 'Become ' . $role,
							'icon' => 'user',
							'url' => new moodle_url('/course/switchrole.php', array(
								'id' => $courseid,
								'sesskey' => sesskey(),
								'switchrole' => 5,
								'returnurl' => $this->page->url->out_as_local_url(false)
							)),
						);
					}
				}
				
				if ($this->is_beta_tester()) {
					$submenu[] = array('header' => 'Frequent Locations');
				}
				
				$submenu[] = array('text' => 'My DragonNet', 'icon' => 'anchor', 'url' => '/my'); //My DragonNet
				$submenu[] = array('text' => 'Home (Front Page)', 'icon' => 'home', 'url' => '/'); //Home
				
				if ($this->is_beta_tester()) {							
					$submenu[] = array('header'=> 'Account Management');
				}
				
				//Edit Profile
				$submenu[] = array(
					'text' => 'Edit Profile',
					'icon' => 'edit',
					'url' => "/user/edit.php?id=$USER->id&course=1"
				);
				
				//Change Password
				$submenu[] = array(
					'text' => 'Change Password',
					'icon' => 'edit',
					'url' => '/login/change_password.php?id=1'
				);
				
			} //end if not guest user

			//Logout
			$submenu[] = array(
				'text' => 'Logout',
				'icon' => 'signout',
				'url' => '/login/logout.php?sesskey=' . sesskey()
			);

			//Add submenu to menu	
			$menu[0]['submenu'] = $submenu;
		}

		return $menu;
	}

	// !Course Menus

	/*
	*	Finds all the courses within a category tree and returns them
	*	e.g.
	*	Arts
	*		Music
	*	 		Music (6)
	*	  	Drama
	*	  		Drama (6)
	*
	*	  Becomes:
	*	  array( Music (6) , Drama(6) );
	*/
	private function extract_courses_from_branch($branch)
	{
		if (!$branch) {
			return;
		}

		$courses = array();

		foreach ($branch['courses'] as $course) {
			$courses[] = $course;
		}

		foreach ($branch['categories'] as $category) {
			foreach ($this->extract_courses_from_branch($category) as $course) {
				$courses[] = $course;
			}
		}

		return $courses;
	}

	/*
	*	Returns an array of menu items for the courses a user is enrolled in
	*/
	private function get_course_menus()
	{
		global $SESSION;
		$courses = $this->get_users_course_tree();

		$menu = array();
		foreach ($courses as $category) {
			//Non admins can be enrolled in invisible courses - but we don't want those to appear on the menu
			if ($category['name'] === 'Invisible' && !$SESSION->userIsSiteAdmin) {
				continue;
			}

			$this->add_category_to_menu($menu, $category);
		}

		return $menu;
	}

	private function add_category_to_menu(&$menu, $category, $depth=0)
	{

		if (!$this->is_beta_tester()) {
			//Collapse the teaching and learning menu if it doesn't have many items in it
			if ($category['name'] == 'Teaching & Learning') {
				$courses = $this->extract_courses_from_branch($category);
				if (count($courses) <= 20) {
					$category['courses'] = $courses;
					$category['categories'] = array();
				}
			}
		}

		//See if an icon for this category is set in the SSIS metadata
		$category_icon = course_get_category_icon($category['id']);

		//Backward compatibility: If no icon was set in the metadata, use the ones defined in this class
		//This can be removed when all category icons have been set in the metadata
		if (!$category_icon) {
			$category_icon = $this->get_category_icon($category['name']);
		}

		if ($this->is_beta_tester()) {
			//Add category to menu

			if ($depth == 1) {			
				//If this is a depth = 1 item (a category that's a direct child of a categories ON the awesomebar,
				//show the title as a header
				$item = array('header' => $category['name']);	
			} else {		
				$item = array(
					'text' => $category['name'],
					'icon' => strtolower($category_icon),
					'submenu' => array()
				);
			}

		} else {
			$item = array(
				'text' => $category['name'],
				'icon' => strtolower($category_icon),
				'submenu' => array()
			);
		}

		//Add static headings for the first items in the menus
		if ($depth == 0) {
		
			switch ($category['name']) {
			
				case 'Teaching & Learning':
					if ($this->is_beta_tester()) {
						$item['submenu'][] = array(	'header' => 'General');
					}
		 		
			 		//My Online Portfolio
	 				//Add a link to the user's OLP (if they have one) at the top of the teaching and learning menu
			 		global $USER;
			 		if ($olpCourseID = get_olp_courseid($USER->idnumber)) {
						$item['submenu'][] = array(
							'text' => 'My Online Portfolio',
							'url' => '/course/view.php?id=' . $olpCourseID,
							'icon' => 'certificate'
						);
					}
					
			 		$item['submenu'][] = array(
			 			'text' => 'Browse All DragonNet Courses',
			 			'url' => '/course/index.php?categoryid=50',
			 			'icon' => 'search'
			 		);
					break;
					
				case 'Activities':
					if ($this->is_beta_tester()) {
						$item['submenu'][] = array('header' => 'General');
						$item['submenu'][] = array(
							'text' => 'All Activity Information', 
							'url' => '',
							'icon' => 'home');
						$item['submenu'][] = array('header' => 'Signing Up');
						$item['submenu'][] = array(
							'text' => 'This Season\'s Handbook', 
							'url' => '', 
							'icon' => 'file-text');
						$item['submenu'][] = array(
							'text' => 'Browse Elementary Activities', 
							'url' => '', 
							'icon' => 'search');						
						$item['submenu'][] = array(
							'text' => 'Browse Secondary Activities', 
							'url' => '', 
							'icon' => 'search');						
						
						$item['submenu'][] = array(	'header' => 'My Activities');
					}  http://dragonnet.ssis-suzhou.net/course/view.php?id=1221
					break;

				case 'Curriculum':
					if ($this->is_beta_tester()) {
						$item['submenu'][] = array('header' => 'Notice');
						$item['submenu'][] = array(
							'text' => 'Curriculum menu to be removed', 
							'url' => '', 
							'icon' => 'bullhorn');
						$item['submenu'][] = array(
							'text' => 'See Navigate menu instead', 
							'url' => '', 
							'icon' => 'bullhorn');
					}
					break;
			}			
		}

		//Add subcategories to menu
		foreach ($category['categories'] as $subcategory) {
			$this->add_category_to_menu($item['submenu'], $subcategory, $depth+1);
		}

		//Add courses to menu
		foreach ($category['courses'] as $course) {
			//See if an icon for this course is set in the SSIS metadata
			$course_icon = course_get_icon($course['id']);
			$course_icon = strtolower($course_icon);

			//For courses in the Activities category, replace text in (parentheses) with an "icon" on the right
			if ($category['id'] == 1) {
				//Match specific text in parentheses
				if (preg_match_all('/\((S1|S2|S3|ALL|FULL)\)/i', $course['fullname'], $matches)) {
					foreach ($matches[0] as $i => $matchedText) {
						$icon = '<i class="pull-right icon-text">' . $matches[1][$i] . '</i>';
						$course['fullname'] = str_replace($matchedText, $icon, $course['fullname']);
						$course['fullname'] = trim($course['fullname']);
					}
				}
				
				//Remove empty parentheses
				$course['fullname'] = str_replace('( )', '', $course['fullname']);
			}

			$item['submenu'][] = array(
				'text' => $course['fullname'],
				'url' => '/course/view.php?id=' . $course['id'],
				'icon' => $course_icon,
			);
		}

		if ($this->is_beta_tester()) {
			if (!empty($item['header']) && !empty($item['submenu'])) {
				// This is a header with a submenu, 
				// So move anything in a header's submenu out into the parent, remove the submenu
				// and add things non-standard-like

				// oh and we don't want the icon to display in the header
				unset($item['icon']);
				$menu[] = $item;

				foreach ($item['submenu'] as $submenuitem) {
					$menu[] = $submenuitem;
				}
				unset($item['submenu']);
	 		} else {
	 			// This is a normal item, add as normal
	 			$menu[] = $item;
	 		}	

 		} else {
 			$menu[] = $item;
 		}
	}

	/*
	*	Returns the icon for a category (from its name)
	*/
	private function get_category_icon($category_name)
	{
		switch ($category_name) {
			case 'Teaching & Learning':
				return 'magic';
			case 'Activities':
				return 'rocket';
			case 'School Life':
				return 'ticket';
			case 'Curriculum':
				return 'save';
			case 'Parents':
				return 'info-sign';
			case 'Invisible':
				return 'star-half-empty';
			default:
				return '';
		}
	}

	// !Course Data
	//This shouldn't really be here because this is data stuff not presentation. But we need it for the awesomebar and Moodle sucks

	/*	
	*	Create a category tree with only the courses the user is enrolled in
	*	Unless they're an admin - in that case returns every course on DragonNet
	*	
	*	Our own version to replace the depreciated get_course_category_tree function
	*/
	private function get_users_course_tree()
	{
		global $SESSION;

		$tree = array();
		if ($SESSION->userIsSiteAdmin) {
			//Admins can see all courses
			$enrolledCourses = false;
		} else {
			//Get courses a user is enrolled in
			$enrolledCourses = enrol_get_my_courses();
		}

		//Start with category and add all the child categories to the menu
		$categories = coursecat::get(0)->get_children();
		foreach ($categories as $category) {
			$this->add_category_to_tree($tree, $category, $enrolledCourses);
		}

		return $tree;
	}

	/*
	*	Add a category and its subcategories and its courses to the category tree (used by get_users_course_tree)
	*/
	private function add_category_to_tree(&$tree, $category, &$enrolledCourses)
	{
		$branch = array(
			'name' => $category->name,
			'id' => $category->id,
			'idnumber' => $category->idnumber,
			'categories' => array(),
			'courses' => array()
		);

		//Add all the subcategories to the 'categories' array
		foreach ($category->get_children() as $subcategory) {
			$this->add_category_to_tree($branch['categories'], $subcategory, $enrolledCourses);
		}

		//Add courses in this category to the 'courses' array
		foreach ($category->get_courses() as $course) {
			//But only If the user is enrolled in this course...
			if ($enrolledCourses === false || isset($enrolledCourses[$course->id])) {
				$branch['courses'][] = array(
					'id' => $course->id,
					'fullname' => $course->fullname,
					'shortname' => $course->shortname,
				);
			}
		}

		//Don't bother adding empty categories
		if (count($branch['categories']) > 0 || count($branch['courses']) > 0) {
			$tree[] = $branch;
		}
	}
}

?>
