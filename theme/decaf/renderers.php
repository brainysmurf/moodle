<?php

	require_once($CFG->libdir.'/coursecatlib.php');

class theme_decaf_core_renderer extends core_renderer {

	protected $really_editing = false;
	private $cache;


	/*
		Create a cache object on construct
	*/
	function __construct( moodle_page $page, $target )
	{
		$this->cache = cache::make_from_params(cache_store::MODE_SESSION, 'theme_decaf', 'decafcache');
		parent::__construct( $page , $target );
	}



	/*
		Change the heading when on an activity inside a "->" course
		Show the activity (coursemodule) name as the heading instead
	*/
    public function header()
	{
		if ( strpos($this->page->heading, '-&gt') !== false && $this->page->cm)
		{
			$this->page->set_heading($this->page->cm->name);
		}
		return parent::header();
	}

    /*
    	Breadcrumbs
		SSIS modifies this to only include coures name followed by whatever activity after that
     */
    public function navbar()
    {
		global $CFG;
        $items = $this->page->navbar->get_items();
        
        $htmlblocks = array();
        // Iterate the navarray and display each node
        $itemcount = count($items);
        $separator = get_separator();
		$home = html_writer::tag('a', '<i class="icon-home"></i> Home', array('href'=>$CFG->wwwroot));
		$htmlblocks[] = html_writer::tag('li', $home);
        
        foreach ( $items as $item )
		{	
			if (!$item->parent) { continue; }
		    if ($item->title === null) { continue; }
		    if ($item->title === 'Invisible') { continue; }
		    if ($item->title === 'DragonNet Frontpage') { continue; }

            //$item->hideicon = true;
            $content = html_writer::start_tag('li');
			    if ( !($item->title==='') )
			    {
		        	if ( $item->text === strtoupper($item->text) )
		        	{
					    $output = $item->title;
					}
					else
					{
	  		    		$output = $item->title.': '.$item->text;
					}
				}
				else
				{
		        	$output = $item->text;
			    }
		    	$content .= html_writer::tag('a', $separator.$output, array('href'=>$item->action));
	    	$content .= html_writer::end_tag('li');

            $htmlblocks[] = $content;
        }

        //accessibility: heading for navbar list  (MDL-20446)
        $navbarcontent = html_writer::tag('span', get_string('pagepath'), array('class'=>'accesshide'));
        $navbarcontent .= html_writer::tag('ul', join('', $htmlblocks), array('role'=>'navigation'));

        return $navbarcontent;
    }


    /**
     * Returns HTML to display a "Turn editing on/off" button in a form.
     *
     * @param moodle_url $url The URL + params to send through when clicking the button
     * @return string HTML the button
     */
    public function edit_button(moodle_url $url) {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }

        return $this->single_button($url, $editstring);
    }

    public function set_really_editing($editing) {
        $this->really_editing = $editing;
    }
    /**
     * Outputs the page's footer
     * @return string HTML fragment
     */
    public function footer() {
        global $CFG, $DB, $USER;

        $output = $this->container_end_all(true);

        $footer = $this->opencontainers->pop('header/footer');

        if (debugging() and $DB and $DB->is_transaction_started()) {
            // TODO: MDL-20625 print warning - transaction will be rolled back
        }

        // Provide some performance info if required
        $performanceinfo = '';
        if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
            $perf = get_performance_info();
            if (defined('MDL_PERFTOLOG') && !function_exists('register_shutdown_function')) {
                error_log("PERF: " . $perf['txt']);
            }
            if (defined('MDL_PERFTOFOOT') || debugging() || $CFG->perfdebug > 7) {
                $performanceinfo = decaf_performance_output($perf);
            }
        }

        $footer = str_replace($this->unique_performance_info_token, $performanceinfo, $footer);

        $footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);

        $this->page->set_state(moodle_page::STATE_DONE);

        if(!empty($this->page->theme->settings->persistentedit) && property_exists($USER, 'editing') && $USER->editing && !$this->really_editing) {
            $USER->editing = false;
        }

        return $output . $footer;
    }

        /**
     * The standard tags (typically performance information and validation links,
     * if we are in developer debug mode) that should be output in the footer area
     * of the page. Designed to be called in theme layout.php files.
     * @return string HTML fragment.
     */
    public function standard_footer_html() {
        global $CFG;

        // This function is normally called from a layout.php file in {@link header()}
        // but some of the content won't be known until later, so we return a placeholder
        // for now. This will be replaced with the real content in {@link footer()}.
        $output = $this->unique_performance_info_token;
        // Moodle 2.1 uses a magic accessor for $this->page->devicetypeinuse so we need to
        // check for the existence of the function that uses as
        // isset($this->page->devicetypeinuse) returns false
        if ($this->page->devicetypeinuse=='legacy') {
            // The legacy theme is in use print the notification
            $output .= html_writer::tag('div', get_string('legacythemeinuse'), array('class'=>'legacythemeinuse'));
        }

        // Get links to switch device types (only shown for users not on a default device)
        $output .= $this->theme_switch_links();
        
       // if (!empty($CFG->debugpageinfo)) {
       //     $output .= '<div class="performanceinfo">This page is: ' . $this->page->debug_summary() . '</div>';
       // }
        if (debugging(null, DEBUG_DEVELOPER)) {  // Only in developer mode
            $output .= '<div class="purgecaches"><a href="'.$CFG->wwwroot.'/admin/purgecaches.php?confirm=1&amp;sesskey='.sesskey().'">'.get_string('purgecaches', 'admin').'</a></div>';
        }
        if (!empty($CFG->debugvalidators)) {
            $output .= '<div class="validators"><ul>
              <li><a href="http://validator.w3.org/check?verbose=1&amp;ss=1&amp;uri=' . urlencode(qualified_me()) . '">Validate HTML</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1=' . urlencode(qualified_me()) . '">Section 508 Check</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=0&amp;warnp2n3e=1&amp;url1=' . urlencode(qualified_me()) . '">WCAG 1 (2,3) Check</a></li>
            </ul></div>';
        }
        if (!empty($CFG->additionalhtmlfooter)) {
	     $output .= "\n".$CFG->additionalhtmlfooter;
        }
        return $output;
    }

    
    // !SSIS Awesomebar

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     * Super tweaked for SSIS to make the code easier
     * The custom menu this method override the render_custom_menu function
     * in outputrenderers.php
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu( custom_menu $menu )
    {
		//Return a cached menu if available
		if ( $content = $this->cache->get('awesomebar') )
		{
			echo 'cached';
			return $content;
		}
		
		require_once dirname(__FILE__).'/awesomebar.php';
		$awesomebar = new awesomebar($this->page);
		$awesomebar->set_custom_menu($menu);
		$content = $awesomebar->create();

		//Save in the cache
		$this->cache->set('awesomebar',$content);	

        return $content;
    }


	
    /**
     * Renders a pix_icon widget and returns the HTML to display it.
     *
     * @param pix_icon $icon
     * @return string HTML fragment
     */
    protected function render_pix_icon(pix_icon $icon) {
        $attributes = $icon->attributes;
        $attributes['src'] = $this->pix_url($icon->pix, $icon->component);
	switch ($icon->pix) {
	case 'i/navigationitem': return html_writer::tag('i', '', array('class'=>'icon-cog pull-left')); break;
	case 'i/edit': return html_writer::tag('i', '', array('class'=>'icon-edit pull-left')); break;
	case 'i/settings': return html_writer::tag('i', '', array('class'=>'icon-cogs pull-left')); break;
	case 'i/group': return html_writer::tag('i', '', array('class'=>'icon-group pull-left')); break;
	case 'i/filter': return html_writer::tag('i', '', array('class'=>'icon-filter pull-left')); break;
	case 'i/backup': return html_writer::tag('i', '', array('class'=>'icon-upload pull-left')); break;
	case 'i/import': return html_writer::tag('i', '', array('class'=>'icon-download pull-left')); break;
	case 'i/restore': return html_writer::tag('i', '', array('class'=>'icon-download pull-left')); break;
	case 'i/report': return html_writer::tag('i', '', array('class'=>'icon-file pull-left')); break;
	case 'i/permissions': return html_writer::tag('i', '', array('class'=>'icon-user-md pull-left')); break;
	case 'i/switchrole': return html_writer::tag('i', '', array('class'=>'icon-user pull-left')); break;
	case 'i/outcomes': return html_writer::tag('i', '', array('class'=>'icon-bar-chart pull-left')); break;
	case 'i/grades': return html_writer::tag('i', '', array('class'=>'icon-legal pull-left')); break;
	case 'i/enrolusers': return html_writer::tag('i', '', array('class'=>'icon-user pull-left')); break;
	case 'i/assignroles': return html_writer::tag('i', '', array('class'=>'icon-user pull-left')); break;
	case 'i/publish': return html_writer::tag('i', '', array('class'=>'icon-globe pull-left')); break;
	case 'i/return': return html_writer::tag('i', '', array('class'=>'icon-rotate-left pull-left')); break;
	case 'i/checkpermissions': return html_writer::tag('i', '', array('class'=>'icon-key pull-left')); break;

	}
        return html_writer::empty_tag('img', $attributes);
    }




    /**
     * Renders a custom menu node as part of a submenu
     *
     * The custom menu this method override the render_custom_menu_item function
     * in outputrenderers.php
     *
     * @see render_custom_menu()
     *
     * @staticvar int $submenucount
     * @param custom_menu_item $menunode
     * @return string
     */
   /* protected function render_custom_menu_item(custom_menu_item $menunode) {
        // Required to ensure we get unique trackable id's
        static $submenucount = 0;
        
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            $submenucount++;
	    $content = html_writer::start_tag('li');

	    $icon = html_writer::tag('i', '', array('class'=>'icon-none pull-left'));
	    if ($menunode->get_title()) {
	        // This adds the feature of using the title for the icon
	        $icon = html_writer::tag('i', '', array('class'=>$menunode->get_title().' pull-left'));
	    }

	    switch ($menunode->get_text()) {
	        case 'Teaching & Learning': $icon = html_writer::tag('i', '', array('class'=>'icon-magic pull-left')); break;
	        case 'Activities': $icon = html_writer::tag('i', '', array('class'=>'icon-rocket pull-left')); break;
	        case 'School Life': $icon = html_writer::tag('i', '', array('class'=>'icon-ticket pull-left')); break;
	        case 'Curriculum': $icon = html_writer::tag('i', '', array('class'=>'icon-save pull-left')); break;
	        case 'Parents': $icon = html_writer::tag('i', '', array('class'=>'icon-info-sign pull-left')); break;
	        case 'Invisible': $icon = html_writer::tag('i', '', array('class'=>'icon-star-half-empty pull-left')); break;
	    }

	    // Now we need to process whether to conver to a link and whether to append a right caret
	    $addcaret = '';
		if ( $parent = $menunode->get_parent() )
		{   
		    if ( !($parent->get_text() === 'root') )
		    {
		    	// filter out roots, so top level is ignored
		        $addcaret = html_writer::tag('i', '', array('class'=>'pull-right icon-caret-right'));
		    }
		}	    
	    
            if ($menunode->get_url() !== null )
            {
                $content .= html_writer::link($menunode->get_url(), $icon.$menunode->get_text().$addcaret);
            }
            else
            {
                $content .= $icon.$menunode->get_text().$addcaret;
            }

            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
	    $content .= html_writer::end_tag('li');

        } else {

	    // The node doesn't have children so produce a leaf

	    if ($menunode->get_text() === 'hr') {
	        $content = html_writer::empty_tag('hr');
	    } else {
		$content = html_writer::start_tag('li');
	        $icon = html_writer::tag('i', '', array('class'=>'icon-none'));

	        if ($menunode->get_title()) {
		    $icon = html_writer::tag('i', '', array('class'=>$menunode->get_title().' pull-left'));
	        }

                if ($menunode->get_url() !== null) {
                    $url = $menunode->get_url();
                } else {
                    $url = '#';
                }
                $content .= html_writer::link($url, $icon.$menunode->get_text(), array('title'=>$menunode->get_title()));
		$content .= html_writer::end_tag('li');
	    }
        }

        // Return the sub menu
        return $content;
    } */


/*
    // Copied from core_renderer with one minor change - changed $this->output->render() call to $this->render()
    protected function render_navigation_node(navigation_node $item) {
        $content = $item->get_content();
        $title = $item->get_title();
        if ($item->icon instanceof renderable && !$item->hideicon) {
                $icon = $this->render($item->icon);
            $content = $icon.$content; // use CSS for spacing of icons
        }
        if ($content === '') {
            return '';
        }

        if ($item->action instanceof action_link) {
            //TODO: to be replaced with something else
            $link = $item->action;
            if ($item->hidden) {
                $link->add_class('dimmed');
            }
            $content = $this->render($link);
        } else if ($item->action instanceof moodle_url) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::link($item->action, $content, $attributes);

        } else if (is_string($item->action) || empty($item->action)) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
        }

        return $content;
    } */





	// !Blocks

    /**
     * blocks_for_region() overrides core_renderer::blocks_for_region()
     *  in moodlelib.php. Returns a string
     * values ready for use.
     *
     * @return string
     */
    public function blocks_for_region($region) {
        if (!$this->page->blocks->is_known_region($region)) {
            return '';
        }
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $output = '';
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                if (!($this->page->theme->settings->hidesettingsblock && substr($bc->attributes['class'], 0, 15) == 'block_settings ')
                        && !($this->page->theme->settings->hidenavigationblock && substr($bc->attributes['class'], 0, 17) == 'block_navigation ')) {
                    $output .= $this->block($bc, $region);
                }
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }
    
    
    
    
    
    // !Tabs
    
    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
   protected function render_tabtree(tabtree $tabtree)
	{
		if ( empty($tabtree->subtree) ) { return ''; }

        $str = html_writer::start_tag('div', array('class' => 'tabs'));
	        #$str .= $this->render_tabobject($tabtree);
	        $str .= $this->render_tabs($tabtree->subtree);
	        
	        $rendered = $this->render_tabs($tabtree->subtree);
	        
	        foreach ( $rendered as $ul )
	        {
	        	echo $ul['ul'];
	        	if ( !emprty($ul['subtrees']) )
	        	{
	        		foreach ( $ul['subtrees'] as $subtree )
	        		{
	        			echo $subtree;
	        		}
	        	}
	        }
	        
		$str .= html_writer::end_tag('div');
		
		$str .= html_writer::tag('div', ' ', array('class' => 'clearer'));
		
        return $str;
    } 

	protected function render_tabs($tabobject , $depth=0 )
	{
		$subtrees = array();
		
		$ulclass= $depth > 0 ? 'additional-tabs' : '';

		$ul = '<ul class="'.$ulclass.'">';
		foreach ( $tabobject as $tab )
		{
			$ul .= '<li>';

				$attributes = array();
				if ( count($tab->data) )
				{
					foreach ( $tab->data as $key=>$value )
					{
						$attributes['data-'.$key] = $value;
					}
				}

				if ( $tab->selected || $tab->activated )
				{
					$attributes['class'] = 'selected';
					$ul .= html_writer::tag('span', $tab->text , $attributes);
				}
				else if ( !($tab->link instanceof moodle_url) )
	            {
	                // backward compatibility when link was set as a string instead of an object
	                $ul .= '<a href="'.$tab->link.'" title="'.$tab->title.'">'.$tab->text.'</a>';
	            }
	            else
	            {
	            	//Tab links
					$attributes['title'] = $tab->title;
					$ul .= html_writer::link($tab->link, $tab->text, $attributes);
	            }
	            
	            if ( !empty($tab->subtree) )
	            {
	            	$subtrees[] = $tab->subtree;
	            }
		
			$ul .= '</li>';

		}
		$ul .= '</ul>';
		
		if ( count($subtrees) > 0 )
		{
			foreach ( $subtrees as $subtree )
			{
				$ul .= $this->render_tabs($subtree , $depth+1 );
			}
		}
		
		return $ul;
	}     

}



/*
	For rendering the 'Course Administration' and 'Site Administration' menus
*/
class theme_decaf_topsettings_renderer extends plugin_renderer_base {

    public function settings_tree(settings_navigation $navigation) {
        global $CFG;
        return $this->navigation_node($navigation, array('class' => 'dropdown  dropdown-horizontal'));
    }

    public function settings_search_box() {
		return '';
    }

    public function navigation_tree(global_navigation $navigation) {
        return '';
        global $CFG;
		global $USER;
		//include_once($CFG->dirroot.'/calendar/lib.php');
		//$days_ahead = 30;
		//$cal_items = calendar_get_upcoming($this->user_courses, true, $USER->id, $days_ahead);	
		//$content .= html_writer::end_tag('ul');
    }

    protected function navigation_node(navigation_node $node, $attrs=array()) {
        global $PAGE;
        static $subnav;
        $items = $node->children;
        $hidecourses = (property_exists($PAGE->theme->settings, 'coursesloggedinonly') && $PAGE->theme->settings->coursesloggedinonly && !isloggedin());

        // exit if empty, we don't want an empty ul element
        if ($items->count() == 0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display) {
                continue;
            }
            if ($item->key === 'courses' && $hidecourses) {
                continue;
            }

            // Skip pointless "Current course" node, go straight to its last (sole) child
            if ($item->key === 'currentcourse') {
                $item = $item->children->last();
            }

	    $name = $item->get_content();

	    // Gets rid of "My profile settings" since we put it all in the user menu anyway
	    if ($name === 'My profile settings' || $name === 'Switch role to...') {
	        continue;
	    } 

	    if (!($this->page->course->id === '1266') && ($name === 'Site administration')) {
	        continue;
	    }

            $isbranch = ($item->children->count() > 0 || $item->nodetype == navigation_node::NODETYPE_BRANCH || (property_exists($item, 'isexpandable') && $item->isexpandable));
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }
	    
            $content = $this->output->render($item);

	    if (substr($name, -14) === 'administration') {
	        $content = html_writer::tag('i', '', array('class'=>'icon-wrench pull-left')).$content;
	    }

	    switch ($name) {
	        case 'My profile settings': $content = html_writer::tag('i', '', array('class'=>'icon-wrench pull-left')).$content; break;
	        case 'Switch role to...': $content = html_writer::tag('i', '', array('class'=>'icon-wrench pull-left')).$content; break;
	        case 'Front page settings': $content = html_writer::tag('i', '', array('class'=>'icon-wrench pull-left')).$content; break;
	    }

            if($isbranch && $item->children->count()==0) {
                // Navigation block does this via AJAX - we'll merge it in directly instead
                if(!$subnav) {
                    // Prepare dummy page for subnav initialisation
                    $dummypage = new decaf_dummy_page();
                    $dummypage->set_context($PAGE->context);
                    $dummypage->set_url($PAGE->url);

                    $subnav = new decaf_expand_navigation($dummypage, $item->type, $item->key);
                } else {
                    // re-use subnav so we don't have to reinitialise everything
                    $subnav->expand($item->type, $item->key);
                }
                if (!isloggedin() || isguestuser()) {
                    $subnav->set_expansion_limit(navigation_node::TYPE_COURSE);
                }
                $branch = $subnav->find($item->key, $item->type);
                if($branch!==false) $content .= $this->navigation_node($branch);
            } else {
                $content .= $this->navigation_node($item);
            }

            if($isbranch && !($item->parent->parent==null)) {
	      // TODO: Have to do the ugly thing here and take out the last part of the </a> tag for it to work better
	        $content = html_writer::tag('li', '<i class="pull-right icon-caret-right"></i>'.$content);
            } else {
                $content = html_writer::tag('li', $content);
            }
            $lis[] = $content;
        }

        if (count($lis)) {
            return html_writer::nonempty_tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

    public function search_form(moodle_url $formtarget, $searchvalue) {

        $content = html_writer::start_tag('span', array('class' =>'topadminsearchform'));
	$content .= $this->login_info();
        $content .= html_writer::end_tag('span');

	return $content;
    }

}

?>
