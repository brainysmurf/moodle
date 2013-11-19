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
	private $custom_menu; 	//Custom menu is the 'Navigate' menu item, which is defined using the theme settings page on the web

	function __construct( &$page )
	{
		$this->page = $page;
		global $CFG;
		require_once($CFG->libdir.'/coursecatlib.php');
		
		//Setup the cache (for this session)
		$this->cache = cache::make_from_params(cache_store::MODE_SESSION, 'theme_decaf', 'awesomebar');
		
		//Should we use the cache?
		$this->use_cached = true;
		$this->save_in_cache = true;
		
		//For development - add ?refreshawesomebar to the URL to update the menu
		if ( isset($_GET['refreshawesomebar']) )
		{ 
			$this->use_cached = false;
		}

		//If we're on the site admin course page, don't use a cached version and don't create a cached version
		if ( $this->page->course->id == '1266' )
		{
			$this->use_cached = false;
			$this->save_in_cache = false;
		}
	}
	
	
	// !HTML Rendering
	
	/*
	* Returns the complete HTML for the awesomebar menus
	*/
	public function create()
	{
		//Begin ul
        $content = html_writer::start_tag('ul', array('class'=>'dropdown dropdown-horizontal'));
        
        	//Login Button / User Menu (returns an li)
        	//We always use a fresh user menu
			$content .= $this->render_array_as_menu_item($this->get_user_menu());
        	
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
		if ( $this->use_cached && $content = $this->cache->get('awesomebar') )
		{
			return $content;
		}
		
		global $SESSION;
		
		$loggedIn = isloggedin();
		if ( $loggedIn )
		{
		
			if ( $this->custom_menu )
			{
				//Navigate (Custom menu from decaf theme settings)
	            foreach ($this->custom_menu->get_children() as $menu)
	            {
	            	$menu = $this->convert_custom_menu_item_to_array($menu);
	                $content .= $this->render_array_as_menu_item($menu);
	            	unset($menu);
	            }
			}
			
			//Course Menus
			//Each top level category becomes a button on the awesomebar
			//The dropdown for that button shows the courses / categories within it
			 //We don't show courses on page 1266 so that there's more room for the site admin menu
			if ( !isguestuser() && $this->page->course->id != '1266' )
			{
				$menu = $this->get_course_menus();
				$content .= $this->render_array_as_menu_item($menu);
            	unset($menu);
			}       
		}

        $content .= html_writer::end_tag('ul'); // end whole ul
       
		if ( $this->save_in_cache )
		{
			//Save in the cache
			$this->cache->set('awesomebar',$content);	
		}
        
        return $content;
	}
	
	
	
	/*
	*	Takes an array and builds an HTML menu from it
	*/
	private function render_array_as_menu_item( array $menu , $depth=0 )
	{
		$html = '';
		foreach ( $menu as $item )
		{
			if ( is_string($item) && $item == 'hr' )
			{
				$html .= html_writer::empty_tag('hr');
				continue;
			}
		
			$html .= html_writer::start_tag('li');
						
				$hasSubmenu = isset($item['submenu']) && count($item['submenu']) > 0;
						
				if ( $hasSubmenu && $depth > 0 )
				{
					//Right arrow
					$html .= html_writer::tag('i', '', array('class'=>'pull-right icon-caret-right'));
				}		 
			
				if ( $item['icon'] )
				{
					$icon = html_writer::tag('i', '', array('class'=>'pull-left icon-'.$item['icon']));
				}
				else
				{
					$icon = false;
				}
			
				if ( isset($item['url']) )
				{
    			    $html .= html_writer::tag('a', $icon.$item['text'], array('href'=>$item['url']));
				}
				else
				{
    			    $html .= html_writer::tag('span', $icon.$item['text']);
				}
				
				if ( $hasSubmenu )
				{
					$html .= html_writer::start_tag('ul');
						$html .= $this->render_array_as_menu_item($item['submenu'] , $depth+1);
					$html .= html_writer::end_tag('ul');
				}
			
			$html .= html_writer::end_tag('li');
		}
		return $html;
	}
	
	
	// !Custom Menu (Navigate)
	
	public function set_custom_menu( $menu )
	{
		$this->custom_menu = $menu;
	}
	
	/*
	*	Converts the custom menu from the decaf settings page into an array to put on the awesomebar
	*/
	private function convert_custom_menu_item_to_array( custom_menu_item $menunode )
	{
		$menu = array();
		
		if ( $menunode->get_text() == 'hr' )
		{
			$item = 'hr';
		}
		else
		{
	    	$item = array(
	    		'text'=>$menunode->get_text(),
	    		'icon'=> str_replace('icon-','',$menunode->get_title()),
	    		'url' => $menunode->get_url() ? $menunode->get_url()->out(false) : null
	    	);

			if ( $menunode->has_children() )
			{
				$item['submenu'] = array();
				foreach ( $menunode->get_children() as $child )
				{
					$child = $this->convert_custom_menu_item_to_array( $child );
					$item['submenu'][] = $child[0];
				}
			}
		}
    
    	$menu[] = $item;
		
		return $menu;
	}
	
	
	// !User Menu
	
	/*
	*	Create the 'User' menu (or the Login button if user isn't logged in)
	*/
	private function get_user_menu( $loggedIn=false )
	{
		$menu = array();
		$loggedIn = isloggedin();

		if ( !$loggedIn )
		{
			$menu[] = array( 'text'=>'Login', 'icon'=>'signin', 'url' => '/login' ); //Login button
		}
		else
		{
			//Current user button
			global $USER, $DB;
			
			$course = $this->page->course;

			//Show username
			if ( session_is_loggedinas() ) //Used 'login as' to login as a different user
			{
            	$realuser = session_get_realuser();
	            $menu[] = array( 'icon'=>'user', 'text' => fullname($realuser).' -> '.fullname($USER) );	
			}
			else if ( !empty($course->id) && is_role_switched($course->id) ) //Role is switched
			{
				//Find out what role we switched to
				$context = context_course::instance($course->id);
                $rolename = '';
                if ( $role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path])) )
                {
                    $rolename = role_get_name($role, $context);
                }
                $loggedinas = get_string('loggedinas', 'moodle', $username).$rolename;

	            $menu[] = array( 'icon'=>'user', 'text' => $rolename );
			}
			else //Normal user
			{
	            $menu[] = array( 'icon'=>'user', 'text' => fullname($USER) );	
			}
			
			$submenu = array();
			
			if ( isguestuser() ) // Any special items for guest users??
			{
				
			}
		    else //Items for regular logged in users
		    {
	  	        $courseid = $this->page->course->id;
    			$context = context_course::instance($courseid);

				//FIXME: This is broke
				if ( session_is_loggedinas() )
				{
					//Undo 'login as user'
        			$submenu[] = array(
        				'text' => 'Return to normal',
        				'icon' => 'user',
        				'url' => new moodle_url('/course/loginas.php', array('id'=>$courseid, 'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)))
        			);
        			
        			$submenu[] = 'hr';
				}
		        else if ( is_role_switched($courseid) ) //Role is switched - show 'Return to normal' button
        		{	
        			$submenu[] = array(
        				'text' => 'Return to normal',
        				'icon' => 'user',
        				'url' => new moodle_url('/course/switchrole.php', array('id'=>$courseid, 'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)))
        			);
        			
        			$submenu[] = 'hr';
		        }
		        else if ( has_capability('moodle/role:switchroles', $context) ) //Become Student button
		        {
		            $roles = get_switchable_roles($context);
    				if ( count($roles) > 0 && $role = $roles[5] )
    				{
    					$submenu[] = array(
	        				'text' => 'Become '.$role,
	        				'icon'=>'user',
	        				'url' => new moodle_url('/course/switchrole.php', array('id'=>$courseid, 'sesskey'=>sesskey(), 'switchrole'=>5, 'returnurl'=>$this->page->url->out_as_local_url(false))),
	        			);
    				
    					$submenu[] = 'hr';
				    }
        		}

				$submenu[]= array( 'text'=>'Edit Profile', 'icon'=>'edit', 'url'=>"/user/edit.php?id=$USER->id&course=1" ); //Edit Profile
    			$submenu[] = array( 'text'=>'Change Password', 'icon'=>'edit', 'url'=>'/login/change_password.php?id=1' ); //Change Password
				$submenu[] = 'hr';
				$submenu[] = array( 'text'=>'My DragonNet', 'icon'=>'anchor', 'url' => '/my' ); //My DragonNet
				$submenu[] = array( 'text'=>'Home (Front Page)', 'icon'=>'home', 'url'=>'/' ); //Home
				$submenu[] = 'hr';    			
    		} //end if not guest user

			$submenu[] = array( 'text'=>'Logout', 'icon'=>'signout', 'url'=>'/login/logout.php?sesskey='.sesskey() ); //Logout

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
	*   		Music (6)
	*    	Drama
	*    		Drama (6)
	*    		
	*    Becomes:
	*    array( Music (6) , Drama(6) );
    */
    private function extract_courses_from_branch($branch)
    {
		if ( !$branch ) { return; }
		
		$courses = array();
		
		foreach ($branch['courses'] as $course)
    	{
        	$courses[] = $course;
	    }
	    
	    foreach ( $branch['categories'] as $category )
	    {
	    	foreach ( $this->extract_courses_from_branch($category) as $course )
        	{
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
		foreach ( $courses as $category )
		{
			//Non admins can be enrolled in invisible courses - but we don't want those to appear on the menu
			if ( $category['name'] === 'Invisible' && !$SESSION->userIsSiteAdmin ) { continue; }
			
			$this->add_category_to_menu($menu,$category);
		}
		
		return $menu;
	}
	
		private function add_category_to_menu( &$menu , $category )
		{
			//Collapse the teaching and learning menu if it doesn't have many items in it
			if ( $category['name'] == 'Teaching & Learning' )
			{
				$courses = $this->extract_courses_from_branch($category);
				if ( count($courses) <= 20 )
				{
					$category['courses'] = $courses;
					$category['categories'] = array();
				}
			}
			
			//See if an icon for this category is set in the SSIS metadata
			$category_icon = course_get_category_icon($category['id']);
			
			//Backward compatibility: If no icon was set in the metadata, use the ones defined in this class
			//This can be removed when all category icons have been set in the metadata
			if ( !$category_icon )
			{
				$category_icon = $this->get_category_icon($category['name']);
			}
		
			//Add category to menu
			$item = array(
				'text' => $category['name'],
				'icon' => strtolower($category_icon),
				'submenu' => array()
			);
			
			//Add a link to the user's OLP (if they have one) at the top of the teaching and learning menu
			global $USER;
			if ( $category['name'] == 'Teaching & Learning' && $olpCourseID = get_olp_courseid($USER->idnumber) )
			{
				$item['submenu'][] = array(
					'text' => 'My Online Portfolio',
					'url' => '/course/view.php?id='.$olpCourseID,
					'icon' => 'certificate'
				);
			}
			
				//Add subcategories to menu
				foreach ( $category['categories'] as $subcategory )
				{
					$this->add_category_to_menu($item['submenu'],$subcategory);
				}
				
				//Add courses to menu
				foreach ( $category['courses'] as $course )
				{
					//See if an icon for this course is set in the SSIS metadata
					$course_icon = course_get_icon($course['id']);
					$course_icon = strtolower($course_icon);
					
					//For courses in the Activities category, replace text in (parentheses) with an "icon" on the right
					if ( $category['id'] == 1 )
					{
						//Match all text in parentheses
						//  if ( preg_match_all('/\((.*?)\)/', $course['fullname'], $matches) )
						
						//Match specific text in parentheses
						if ( preg_match_all('/\((S1|S2|S3|ALL|FULL)\)/i', $course['fullname'], $matches) )
						{
							foreach ($matches[0] as $i => $matchedText)
							{
								$icon = '<i class="pull-right icon-text">'.$matches[1][$i].'</i>';
								$course['fullname'] = str_replace($matchedText, $icon, $course['fullname']);
								$course['fullname'] = trim($course['fullname']);	
							}
						}						
					}
				
					$item['submenu'][] = array(
						'text' => $course['fullname'],
						'url' => '/course/view.php?id='.$course['id'],
						'icon' => $course_icon,
					);	
				}
				
			//Add browse all courses link to teaching & learning menu
			if ( $category['name'] == 'Teaching & Learning' )
			{
				$item['submenu'][] = array(
					'text' => 'Browse All DragonNet Courses',
					'url' => '/course/index.php?categoryid=50',
					'icon' => 'archive'
				);
			}
				
			$menu[] = $item;
		}
		
		
	/*
	*	Returns the icon for a category (from its name)
	*/
	private function get_category_icon( $category_name )
	{
		switch( $category_name )
		{
			case 'Teaching & Learning': return 'magic';
			case 'Activities': return 'rocket';
			case 'School Life': return 'ticket';
			case 'Curriculum': return 'save';
			case 'Parents': return 'info-sign';
			case 'Invisible': return 'star-half-empty';
			default: return '';
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

		if ( $SESSION->userIsSiteAdmin )
		{
			//Admins can see all courses
			$enrolledCourses = false;
		}
		else
		{
			//Get courses a user is enrolled in
			$enrolledCourses = enrol_get_my_courses();
		}
		
		//Start with category and add all the child categories to the menu
		$categories = coursecat::get(0)->get_children();
		foreach ( $categories as $category )
		{
			$this->add_category_to_tree( $tree , $category , $enrolledCourses );
		}
			
		return $tree;
	}
		
		/*
		*	Add a category and its subcategories and its courses to the category tree (used by get_users_course_tree)
		*/
		private function add_category_to_tree( &$tree , $category , &$enrolledCourses )
		{
			$branch = array(
				'name'=>$category->name,
				'id'=>$category->id,
				'idnumber'=>$category->idnumber,
				'categories'=>array(),
				'courses'=>array()
			);
			
			//Add all the subcategories to the 'categories' array
			foreach ( $category->get_children() as $subcategory )
			{
				$this->add_category_to_tree( $branch['categories'] , $subcategory , $enrolledCourses );
			}
			
			//Add courses in this category to the 'courses' array
			foreach ( $category->get_courses() as $course )
			{
				//But only If the user is enrolled in this course...
				//FIXME Add check so that we don't get PHP notice about undefined offset
				if ( $enrolledCourses === false || $enrolledCourses[$course->id] !== null )
				{
					$branch['courses'][] = array(
						'id'=>$course->id,
						'fullname'=>$course->fullname,
						'shortname'=>$course->shortname,
					);
				}
			}
			
			//Don't bother adding empty categories
			if ( count($branch['categories']) > 0 || count($branch['courses']) > 0 )
			{
				$tree[] = $branch;
			}
		}

}

?>
