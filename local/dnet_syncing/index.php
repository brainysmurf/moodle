<?php
require_once '../../config.php';
require_once 'portables.php';
require_once '../../local/dnet_common/sharedlib.php';

#require_login();
setup_page();

$user = $DB->get_record('user', array('idnumber'=>$USER->idnumber));

if (!$user) {
    death("No user found. That's strange!");
}

if ( !$user->idnumber ) {
    death("Your account doesn't seem to have a PowerSchool ID. That's strange!");
}

if ( strpos($user->idnumber, 'P') == 4 ) {
    $intro = "Your family's";
    $sub = 'P ' . substr($user->idnumber, 0, 4);
    $wherephrase = "where patron_barcode like ?";
    $params = array($sub.'%');
} else {
    $intro = "Your individual";
    $barcode = 'P ' . $user->idnumber;
    $wherephrase = "where patron_barcode = ?";
    $params = array($barcode);
}

$results = $DB->get_recordset_sql("select * from ssismdl_dnet_destiny_imported {$wherephrase} ORDER BY due ASC", $params);
if (!$results) {
    death('We could not find any destiny records for this account');
}

$lastImport = local_dnet_syncing_last_run();

?>
<div class="local-alert"><i class="icon-info-sign pull-left icon-4x"></i>
	<p style="font-weight:bold;font-size:18px;"><?php echo $intro ?> Library Information</p>
	Below are all our records as of <strong><?=date('l F jS Y H:i A', $lastImport->time)?></strong>.
</div>
<br />
<table class="userinfotable htmltable" width="100%">
<thead>
<tr>
<th>Patron</th>
<th>Item Title</th>
<th>Call Number</th>
<th>Due Date</th>
</tr>
</thead>
<?php

foreach ($results as $item) {
	$overdue = $item->due < time();
    ?>
    <tr>
	    <td class="cell c0"><p><?php echo $item->patron_name; ?></p></td>
	    <td class="cell c1"><p><?php echo $item->title ?></p></td>
	    <td class="cell c0"><p><?php echo $item->call_number ?></p></td>
	    <td class="cell c1"><p <?=($overdue?'class="red"':'')?>><?php echo date('l F jS Y', $item->due) ?></p></td>
    </tr>
    <?php
}

?>
</table>

<?php
$results->close();

echo $OUTPUT->footer();
