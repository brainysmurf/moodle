<?php

	/*
		This script goes through every course in a particular menu
		
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
	echo '<pre>';
	
	require(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once($CFG->libdir.'/coursecatlib.php');
	
	require_once( $CFG->libdir.'/ssismetadata.php' );
	$SSISMETADATA = new ssismetadata();
	
	function set_course_icons($category)
	{
		global $SSISMETADATA;
		
		//Get the icon
		$icon = get_tl_icon($category->name);
		
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
	function get_tl_icon( $name )
	{
	        return 'rocket';
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
	
	//Start running from category 50 (teaching and learning)

        set_course_icons( coursecat::get(1) );

	//$teaching_learning_categories = coursecat::get(1)->get_courses();
        //var_dump($teaching_learning_categories);
	//foreach ( $teaching_learning_categories as $category )
	//{
	//	set_course_icons( $category );	
	//}

?>