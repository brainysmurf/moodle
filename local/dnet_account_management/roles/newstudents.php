<?php

require_once '../../../config.php';
require_once '../output.php';
require_once '../../../local/dnet_common/sharedlib.php';

setup_page();

$powerschoolID = optional_param('powerschool', '', PARAM_RAW);
if (!empty($powerschoolID)) {
    $user = $DB->get_record('user', array('idnumber'=>$powerschoolID));
    $family_id = substr($powerschoolID, 0, 4);
}
$reset_password = optional_param('reset_password', '', PARAM_RAW);
$email = optional_param('email', '', PARAM_RAW);

output_tabs('For: New Students');

if (isloggedin()) {
    death('This section is intended for parents or new students who want to know their DragonNet username. You have to be logged out to access.');
} else {

?>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">How and when can I get my DragonNet account?</p>
They are created automatically the first day that you attend SSIS. Your teachers are emailed your details.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">How and when can I get my DragonNet Parent account?</p>
They are created automatically, along with new student accounts, on the first day that your child attend SSIS. Parents should receive an email with the subject "Your SSIS DragonNet Parent Account"
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">What is my username?</p>
Your username is created according to your passport name and the year you graduate.
For example, if your family name is "Student" and your given name is "Happy", and you will graduate from high school in the year 2020, your username is happystudent20.
</div>

<div class="local-alert"><i class="icon-info-sign icon-4x pull-left"></i>
<p style="font-size:18px;font-weight:bold;">What is my email address?</p>
Your username + @student.ssis-suzhou.net.
</div>


<?php

}
echo $OUTPUT->footer();
