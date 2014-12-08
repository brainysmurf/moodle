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

output_tabs('About DragonNet Passwords');

?>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">DragonNet passwords MUST have a symbol character, such as ! or @ or #?</p>
This is the most common problem when attempting to login to DragonNet. The use of symbol characters is highly recommended for DragonNet and all online websites that you use. It does make it significantly more secure.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">How many passwords do I need for SSIS?</p>
You only need one. The most common online tools at SSIS (DragonNet, DragonTV, and Student Email) all share the same password. Changing the DragonNet password automatically changes the password on the other two.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">Who can reset passwords?</p>
Teachers can only reset students' passwords, and secretaries can reset everyone's password. DragonNet site administrators can also reset everyone's passwords.
</div>

<?php

echo $OUTPUT->footer();
