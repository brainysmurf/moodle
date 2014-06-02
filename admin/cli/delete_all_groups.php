<?php

require_once( '../../config.php');
require_once('../../group/lib.php');

foreach ($DB->get_records('groups') as $group) {
    echo($group->name.'<br />');
    groups_delete_group($group);
}


echo 'oh';
