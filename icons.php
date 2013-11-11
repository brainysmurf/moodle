<?php

/*
	This page lists all the icons available in fontawesome
*/
require_once(dirname(__FILE__) . '/config.php');

	$PAGE->set_url('/icons.php');
	$PAGE->set_title('Icons');
	$PAGE->set_heading('Icons');

	echo $OUTPUT->header();

		$icons = file_get_contents('font-awesome-icons/icons.json');
		$icons = json_decode($icons,true);
		
		$split = ceil(count($icons)/3);
		
		echo '<ul class="iconList">';
		
			$i = 0;
			foreach ( $icons as $class => $unicode )
			{
				$niceName = str_replace('icon-','',$class);
				echo '<li><i class="'.$class.' icon-2x"></i> '.$niceName.'</li>';
			}
		
		echo '</ul>';

	echo $OUTPUT->footer();
