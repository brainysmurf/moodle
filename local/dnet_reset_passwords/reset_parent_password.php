<?php

/**
 *  reset_password.php
 */
require_once '../../config.php';
require_once 'lib.php';

setup_account_management_page();

$key = required_param('key', PARAM_RAW);
$userID = required_param('userID', PARAM_RAW);
$confirm = optional_param('confirm', '', PARAM_RAW);


// Check key is valid
$select = '
select
    *
from
    ssismdl_dnet_pwreset_keys
where
    userid = ? and
    used = ? and
    key = ?';
$params = array($userID, 0, $key);

$row = $DB->get_record_sql($select, $params);

if (!$row) {
    print_object($select);
    print_object($params);
}

// How long should the link be valid for (in seconds)?
if (time() - $row->time > 86400) {
    die("Sorry, that link has expired");
}

// Get user
$user = $DB->get_record('user', array('id' => $userID));
if (!$user) {
    die("Could not find user!");
}

if ( $confirm == "YES") {

    //Password resetting time
    update_internal_user_password($user, 'changeme');
    set_user_preference('auth_forcepasswordchange', 1, $USER);

    // user the previously gotten row to set it
    $row->used = 1;
    $DB->update_record('dnet_pwreset_keys', $row);

    echo "Redirecting...";

    //Set the key as used
    redirect($CFG->wwwroot . '/login/');

} else {
    // Show form to enter a new password here
    ?>
        <div class="local-alert">
        <i class="icon-4x pull-left icon-comment"></i> <p style="font-weight:bold;font-size:18px;">Your username is "<?php echo $user->username ?>". Your password is "changeme".</p>
        <p>Click the button below. You will be prompted to login again. This time your password is "changeme".</p></div>
        <ul class="buttons">
            <li><a id="confirm" href="#" class="btn" id="reset_button"><i class="icon-hand-right"></i> Login again</a></li>
        </ul>

    <script>

    $('#confirm').on("click", function(e) {
        e.preventDefault();
        alert('Remember, your current password is "changeme". You will have to enter it twice.');
        location.href = "<?php echo derive_plugin_path_from('reset_parent_password?confirm=YES&userID='.$userID.'&key='.$key) ?>";
    });

    </script>

    <?php

}


echo $OUTPUT->footer();
