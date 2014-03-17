<?php

require_once '../../../config.php';
require_once '../output.php';

setup_page();

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);
$email = optional_param('email', '', PARAM_RAW);

output_tabs('About Accounts');

?>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">What accounts do Parents have?</p>
Parents have one, and only one, "family" account; their username is an email address.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">What accounts do Students have?</p>
Students have an account for DragonNet, DragonTV, and Student Email. The password is exactly the same as their DragonNet password. They can reset your password for everything by reseting your DragonNet account.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">What accounts do Teachers have?</p>
Teachers have an account for DragonNet and DragonTV. The password for DragonNet is the same for both. Resetting their DragonNet account automatically resets both accounts.
</div>

<?php

echo $OUTPUT->footer();
