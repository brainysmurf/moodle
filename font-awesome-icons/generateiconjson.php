<?php

	//Prints out all the icons from fontawesome as an array

	//Can only be run from command line
	if ( php_sapi_name() !== 'cli' ) { die(); }

	$pattern = '/\.(icon-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';
	$subject = file_get_contents('../font-awesome/css/font-awesome.css');

	preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);

	$icons = array();

	foreach($matches as $match)
	{
    	$icons[$match[1]] = stripslashes($match[2]);
	}


	//Sort alpabetically
	function iconcompare($a, $b)
	{
		$a = str_replace('icon-','',$a);
		$b = str_replace('icon-','',$b);
		return strcmp($a,$b);
	}
	uksort($icons,'iconcompare');


	//Output to json file
	file_put_contents('icons.json', json_encode($icons) );
?>