<?php

die();

/*
* !!! This is totally for testing only and should not be left in in production
*/

require dirname(__FILE__) . '/config.php';

echo hash_internal_user_password($_GET['p']);