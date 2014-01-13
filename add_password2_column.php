<?php

//Create the new "password2" column in the user table

require_once(dirname(__FILE__) . '/config.php');

$DB->execute('ALTER TABLE {user} ADD COLUMN password2 character varying(255)');