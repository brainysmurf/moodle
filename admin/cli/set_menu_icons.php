<?php

	/*
		This script goes through every course and subcategory in a particular category and a sets a default icon
		
		If you run it in the command line it won't set invisible categories/courses.
		To fix that, comment out define('CLI_SCRIPT', true);
		and then run it in the browser (by going to http://dragonnet.ssis-suzhou.net/admin/ci/set_menu_icons.php)
		
		The icon is decided from the first subcategory.
		e.g.
		Teaching & Learning
			English
				--everything below here will have the icon for English
			Arts
				--everything below here will have the icon for Arts
	*/

	#define('CLI_SCRIPT', true);
	echo '<pre>';
	
	require(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once($CFG->libdir.'/coursecatlib.php');
	
	require_once( $CFG->libdir.'/ssismetadata.php' );
	$SSISMETADATA = new ssismetadata();
	
	function set_course_icons($category, $icon)
	{
		if ( !$icon ) { return false; }
		global $SSISMETADATA;
				
		echo "\nCategory: ".$category->id.' '.$category->name.' --> '.$icon;
		
		if ( !$icon ) { return; }
		
		//Set icon for all of the courses
		$courses = $category->get_courses(array('recursive'=>true));
		foreach ( $courses as $course )
		{
			echo "\n\tCourse: ".$course->id.' '.$course->fullname.' --> '.$icon;
			
			//Set icon
			$SSISMETADATA->setCourseField($course->id , 'icon' , $icon);
		}
		
		//Set icons for this category and subcategories
		set_category_icons($category , $icon);
	}
	
		function set_category_icons($category , $icon='')
		{
			global $SSISMETADATA;
			
			//Set the icon for this category
			$SSISMETADATA->setCategoryField( $category->id , 'icon' , $icon);
			
			$subcategories = $category->get_children();
			
			foreach ( $subcategories as $subcategory )
			{
				echo "\n\tSubcategory: ".$subcategory->id.' '.$subcategory->name.' --> '.$icon;
				set_category_icons($subcategory , $icon);
			}
		}
	
	//Returns an icon for a category name
	function get_icon( $name )
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
	
	
	
	//Running time...

	//Set all children of the 'Activities' category to 'rocket'
	set_course_icons( coursecat::get(1) , 'rocket' );
	
	//Set all children of the 'Curriculum' category to 'save'
	set_course_icons( coursecat::get(64) , 'save' );
	
	//Set all children of the 'Parents' category to 'info-sign'
	set_course_icons( coursecat::get(68) , 'info-sign' );
	
	//Get all the categories in 'Teaching & Learning'
	$teaching_learning_categories = coursecat::get(50)->get_children();
	
	//For each of the direct children of the T&L category
	foreach ( $teaching_learning_categories as $category )
	{
		//Decide what icon to set the children of this category to from the category's name
		$icon = get_icon($category->name);	
		set_course_icons($category, $icon);	
	}

?>