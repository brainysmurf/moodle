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

output_tabs('For: Students');

?>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">Cannot login to DragonNet?</p>
You must ask a teacher to help reset your password for you.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">Cannot login to DragonTV?</p>
You must ask a teacher to reset your DragonNet password. You will then need to change your password on DragonNet, and that will be your password for DragonTV and StudentEmail as well.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">Cannot login to StudentEmail?</p>
You must ask a teacher to reset your DragonNet password. You will then need to change your password on DragonNet, and that will be your password for DragonTV and StudentEmail as well.
</div>


<?php

echo $OUTPUT->footer();
