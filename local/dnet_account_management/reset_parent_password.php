<?php

/**
 *  reset_password.php
 */
require_once '../../config.php';
require_once 'portables.php';
require_once '../../local/dnet_common/sharedlib.php';

setup_page();

if (isloggedin()) {
    death("That's strange. You are trying to reset a password when you have already logged in? Fail!");
}

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
    redirect('/');
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
    ?>
        <div class="local-alert">
        <i class="icon-4x pull-left icon-user"></i> <p style="font-size:18px;">Your username is <strong><?php echo $user->username ?></strong>.</p>
        <p>&nbsp;</p></div>

        <div class="local-alert">
        <i class="icon-4x pull-left icon-key"></i> <p style="font-size:18px;">Your temporary password is <strong>changeme</strong>.</p>
        <p>&nbsp;</p></div>

        <div class="local-alert">
        <i class="icon-4x pull-left icon-question-sign"></i> <p style="font-weight:bold;font-size:18px;">Now login again with the above credentials.</p>
        <p><a id="confirm" href="#" class="btn" id="reset_button"><i class="icon-hand-right"></i> Login again</a></p></div>

        <div id="dialog" title="Reminder" style="display:none"> Remember, your current password is <b>changeme</b>. You will have to enter it twice.</div>

    <script>

    $('#confirm').on("click", function(e) {
        e.preventDefault();
        $("#dialog").dialog({
            minWidth: 450,
            draggable: false,
            model: true,
            buttons: [
                {
                    text: "OK",
                    click: function() {
                        location.href = "<?= derive_plugin_path_from('reset_parent_password.php?confirm=YES&userID='.$userID.'&key='.$key) ?>";
                    }
                },
            ]
        });


    });

    </script>

    <?php

}


echo $OUTPUT->footer();
