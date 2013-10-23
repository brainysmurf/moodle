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
		
			$col = 1;
			$i = 0;
			foreach ( $icons as $class => $unicode )
			{
				++$i;
				
				$niceName = str_replace('icon-','',$class);
				echo '<li><span class="'.$class.' icon-2x"></span> '.$niceName.'</li>';
				
				if ( $col < 3 && $i == $split )
				{
					echo '</ul><ul class="iconList">';
					$i = 0;
					++$col;
				}
			}
		
		echo '</ul>';

	echo $OUTPUT->footer();
