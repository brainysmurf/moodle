<?php

	/*
		This script goes through every category and course in the Teaching & Learning menu and sets the icons
		
		If you run it in the command line it won't set invisible categories/courses.
		To fix that, comment out define('CLI_SCRIPT', true);
		and then run it in the browser
		
		The icon is decided from the first subcategory.
		e.g.
		Teaching & Learning
			English
				--everything below here will have the icon for English
			Arts
				--everything below here will have the icon for Arts
	*/

	#define('CLI_SCRIPT', true);
	require(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once($CFG->libdir.'/coursecatlib.php');
	
	function set_course_icons($category)
	{
		global $DB;
		
		//Get the icon
		$icon = get_tl_icon($category->name);
		
		echo "\nCategory: ".$category->id.' '.$category->name.' --> '.$icon;
		
		if ( !$icon ) { return; }
		
		//Set icon for all of the courses
		$courses = $category->get_courses(array('recursive'=>true));
		foreach ( $courses as $course )
		{
			echo "\n\tCourse: ".$course->id.' '.$course->fullname.' --> '.$icon;
			$update = new stdClass();
			$update->id = $course->id;
			$update->shortname = $icon;
			$DB->update_record_raw('course',$update);
		}
		
		//Set icons for this category and subcategories
		set_category_icons($category , $icon);
	}
	
		function set_category_icons($category , $icon='')
		{
			global $DB;
			
			//Set the icon for this category
			$update = new stdClass();
			$update->id = $category->id;
			$update->idnumber = $icon;
			$DB->update_record_raw('course_categories',$update);
			
			$subcategories = $category->get_children();
			
			foreach ( $subcategories as $subcategory )
			{
				echo "\n\tSubcategory: ".$subcategory->id.' '.$subcategory->name.' --> '.$icon;
				set_category_icons($subcategory , $icon);
			}
		}
	
	//Returns an icon for a category name
	function get_tl_icon( $name )
	{
		switch ( $name )
		{
			case 'Arts': return 'picture';
			case 'English': return 'book';
			case 'Math': return 'bar-chart';
			case 'Science': return 'beaker';
			case 'Design': return 'sitemap';
			case 'Humanities': return 'male';
			case 'World Languages': return 'globe';
			case 'Chinese': return 'globe';
			case 'Homeroom': return 'heart';
			case 'Library, Study Skills, Other': return 'book';
			case 'Physical Education': return 'dribbble';
			case 'IB': return 'star';
			default: return '';
		}
	}
	
	//Start running from category 50
	$teaching_learning_categories = coursecat::get(50)->get_children();
	foreach ( $teaching_learning_categories as $category )
	{
		set_course_icons( $category );	
	}

?>