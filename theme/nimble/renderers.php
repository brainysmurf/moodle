<?php

require_once($CFG->dirroot.'/course/lib.php');

class theme_nimble_core_renderer extends core_renderer {

    protected function seek(&$branch) {
      foreach ($branch as $b) {
	  if ($b->categories) {
	      $this->seek($b->categories);
	  }

        foreach ( $b->courses as $course ) {
	    if (! array_key_exists($course->id, $this->user_courses)) {
	        unset($b->courses[$course->id]);
	    }
        }

      }
    }

    protected function ridof(&$branch) {
        for ($i = 0, $size = count($branch); $i < $size; $i++) {
	    if (isset($branch[$i]->categories) && ! empty($branch[$i]->categories)) {
	        $this->ridof($branch[$i]->categories);
	    }
	    if (isset($branch[$i]->depth) && empty($branch[$i]->courses) && empty($branch[$i]->categories)) {
	        unset($branch[$i]);
            }
	}
    }

    protected function howmany($branch) {
        $this_total = 0;
	if (! $branch) { return 0; }
        foreach ($branch as $b) {
	    if ($b->courses) {
	        $this_total += count($b->courses);
	    }
	    if ($b->categories) {
		$this_total += $this->howmany($b->categories); 
	    }
        }
	return $this_total;
    }

    protected function return_courses($branch) {
      if (! $branch) { return $branch; }
        $these_courses = array();
	foreach ($branch as $b) {
            if ($b->categories) {
	        foreach ($this->return_courses($b->categories) as $course) {
		    $these_courses[$course->id] = $course;
		    $these_courses[$course->id]->category = 50;
	        }
	    }
	    if ($b->courses) {
	        foreach ($b->courses as $course) {
	            $these_courses[$course->id] = $course;
		    $these_courses[$course->id]->category = 50;
	        }
            }
	}
	return $these_courses;
    }

    protected function spellout(&$branch) {
        $teachinglearningbranchindex = 1;
	$maximumnumber = 20;

	if (array_key_exists($teachinglearningbranchindex, $branch)) {
	    $teachinglearningbranch = array($branch[$teachinglearningbranchindex]);
	} else {
	    $teachinglearningbranch = NULL;
	}
        if ($teachinglearningbranch && $this->howmany($teachinglearningbranch) <= 20) {
	    $branch[$teachinglearningbranchindex]->courses = $this->return_courses($teachinglearningbranch);
	    $branch[$teachinglearningbranchindex]->categories = array();
	}
    }

    public function setup_courses() {
         $this->my_courses = get_course_category_tree();
	 $this->all_courses = $this->my_courses;  // copies it
         $this->user_courses = enrol_get_my_courses('category', 'visible DESC, fullname ASC');
	 $this->seek($this->my_courses);
	 $this->ridof($this->my_courses);
	 $this->spellout($this->my_courses);
     }

    protected function render_custom_menu(custom_menu $menu) {
        if (isloggedin())
	  if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
	    $sort = 1000;
	    $mycourses = get_course_category_tree();
	    foreach ($mycourses as $category) {
	      $this->add_category_to_custom_menu_for_admins($menu, $category);
	    }
	  } else {
    	      $this->setup_courses();
	      $this->teachinglearningnode = NULL;
	      $this->add_to_custom_menu($menu, $this->my_courses);
	      if ($this->teachinglearningnode) {
	          $this->teachinglearningnode->add('Browse ALL Courses', new moodle_url('/course/category.php', array('id' => 50)), 'Browse ALL Courses');
	      }
          }
        return parent::render_custom_menu($menu);
    }

    protected function add_category_to_custom_menu_for_admins($menu, $category) {
        // We use a sort starting at a high value to ensure the category gets added to the end
        static $sort = 1000;
	$old_sort = $sort;
	$node = $menu->add($category->name, new moodle_url('/course/category.php', array('id' =>  $category->id)), NULL, NULL, $old_sort++);

        // Add subcategories to the category node by recursivily calling this method.
        $subcategories = $category->categories;
        foreach ($subcategories as $subcategory) {
            $this->add_category_to_custom_menu_for_admins($node, $subcategory);
        }

        // Now we add courses to the category node in the menu
        $courses = $category->courses;
        foreach ($courses as $course) {
            $node->add($course->fullname, new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
        }
	$sort = $old_sort + 2;
    }

    protected function add_to_custom_menu($menu, $array) {
	foreach ($array as $a) {
	    $categories_no_click = NULL; // no clicking, change this to a url if you want clicking

	    if ($a->name == 'Invisible') { continue; }

	    $node = $menu->add($a->name, $categories_no_click, NULL, NULL, $a->sortorder);
	    if ($a->name == 'Teaching & Learning') {
	        $this->teachinglearningnode = $node;
	    }

            $this->add_to_custom_menu($node, $a->categories);

            foreach ($a->courses as $course) {
	      $node->add($course->fullname, new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
            }
        }
    }
}
