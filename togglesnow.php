<?php

	if (!empty($_COOKIE['nosnow'])) {
		setcookie('nosnow', 0, time()+(86400*30), '/');
	} else {
		setcookie('nosnow', 1, time()+(86400*30), '/');	
	}
	
	header('Location: /');
	