<?php

require_once($CFG->dirroot.'/course/lib.php');

class theme_nimble_core_renderer extends core_renderer {
    protected function render_custom_menu(custom_menu $menu) {
        // First check if the user is logged in. No point proceeding if they arn't
        if (isloggedin())
	  if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
	    $sort = 1000;
	    $mycourses = get_course_category_tree();
	    foreach ($mycourses as $category) {
	      $this->add_category_to_custom_menu_for_admins($menu, $category);
	    }
	  } else {
              // Get the my courses branch. If it doesn't exist (not enrolled in any courses) then
              // this will be false otherwise it will be a navigation_node instance.
              //$mycourses = $this->page->navigation->get('mycourses');
              $mycategories = enrol_get_my_courses('category', 'visible DESC, fullname ASC');
              foreach ($mycategories as $catnode) {
		print_object($catnode);
		$this->add_category_to_custom_menu($menu, $catnode);
	      }
              //if ( $mycourses  && ($mycourses->has_children()) ) {
                  // Get the category nodes within the my courses branch. This will return an array of navigation_node instances.
                  // If there arn't any categories this will return an empty array.
                  //$categories = $mycourses->children->type(navigation_node::TYPE_CATEGORY);
                  //foreach ($categories as $catnode) {
                      // Add each category to the custom menu structure we already have (gets added to the end)
	              //    $this->add_category_to_custom_menu($menu, $catnode);
	          //}
              }
	//}
        return parent::render_custom_menu($menu);
    }

    protected function add_category_to_custom_menu_for_admins($menu, $category) {
        // We use a sort starting at a high value to ensure the category gets added to the end
        static $sort = 1000;
	$old_sort = $sort;
	$node = $menu->add($category->name, new moodle_url('/course/category.php', array('id' =>  $category->id)), NULL, NULL, $sort++);

        // Add subcategories to the category node by recursivily calling this method.
        $subcategories = $category->categories;
        foreach ($subcategories as $subcategory) {
            // We need to provide the category node and the subcategory to add
	    //$node = $menu->add($subcategory->name, new moodle_url('/course/category.php', array('id' =>  $category->id)));
            $this->add_category_to_custom_menu_for_admins($node, $subcategory);
        }

        // Now we add courses to the category node in the menu
        $courses = $category->courses;
        foreach ($courses as $course) {
            $node->add($course->fullname, new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
        }
	$sort = $old_sort + 2;
    }

    protected function add_category_to_custom_menu($menu, stdClass $category) {
        // We use a sort starting at a high value to ensure the category gets added to the end
        static $sort = 1000;
	$old_sort = $sort;
	static $teachinglearning = array("English", "Arts", "Science", "World Languages", "Humanities", "Design", "Library, Study Skills, Other", "IB", "Physical Education", "Math", "Homeroom", "Chinese");
	static $top_level = array(
		"Parents" => 500,
		"Curriculum" => 450,
		"Teaching & Learning" => 300,
		"School Life" => 250,
		"Community" => 200,
		"Other" => 400);

        $category_title = $category->get_title();

	if ($category_title == "Invisible") {
		// Do not show at all
		return ;
	}

	if (in_array($category_title, $top_level)) {
	  $this_sort = $top_level[$category_title];
        }

	// We need to figure out what to make node
	// node should point to the teaching & learning node (which is captured along the way)
	// so that courses go on there directly without having subcategories
	// However, if the number of courses is too large (which happens with some teachers, esp heads)
	// We SHOULD use categories!

	// So, get the courses and subcategories first so we can do the right math
	$courses = $category->children->type(navigation_node::TYPE_COURSE);
	$subcategories = $category->children->type(navigation_node::TYPE_CATEGORY);

	// If we are inside a category we want to hide...
	if (in_array($category_title, $teachinglearning) && count($courses) < 10) {
	  //...set node to the captured teaching & learning node...
	  $node = $this->captured_teaching_learning;
	} else {
	  //...otherwise continue on as normal
          $node = $menu->add($category->get_title(), $category->action, $category->get_title(), $sort++);
	}

        if ($category_title == "Teaching & Learning") {
	  $this->captured_teaching_learning = $node;
	}

        // Add subcategories to the category node by recursivily calling this method.
        foreach ($subcategories as $subcategory) {
            // We need to provide the category node and the subcategory to add

            $this->add_category_to_custom_menu($node, $subcategory);
        }

        // Now we add courses to the category node in the menu
        foreach ($courses as $course) {
            $node->add($course->get_title(), $course->action, $course->get_title());
        }

	$sort = $old_sort + 2;
    }
}
