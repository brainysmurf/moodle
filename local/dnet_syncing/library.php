<?php
require_once dirname(dirname(__DIR__)) .  '/config.php';
require_once 'portables.php';
require_once dirname(__DIR__) . '/dnet_common/sharedlib.php';

setup_page('Library');

if (isset($_GET['idnumber'])) {
	// For easier testing, pass ?idnumber=123 in the URL
	$idnumber = $_GET['idnumber'];
} else {
	$idnumber = $USER->idnumber;
}

if (!$idnumber) {
    death("Your account doesn't seem to have a PowerSchool ID. That's strange!");
}

$user = $DB->get_record('user', array('idnumber' => $idnumber));

if (!$user) {
    death("No user found. That's strange!");
}

// Connect to Destiny database
require_once __DIR__ . '/destiny/Destiny.php';
$destiny = new Destiny();

if (strpos($user->idnumber, 'P') == 4) {
    $intro = "Your family's";
    $familyID = substr($user->idnumber, 0, 4);
    $data = $destiny->getFamilyCheckedOutBooks($familyID);
} else {
	$intro = "Your";
	$data = $destiny->getUsersCheckedOutBooks($user->idnumber);
}

$results = $DB->get_recordset_sql("select * from ssismdl_dnet_destiny_imported {$wherephrase} ORDER BY due ASC", $params);
if (!$results) {
    death('We could not find any destiny records for this account');
}

?>
<div class="local-alert"><i class="icon-info-sign pull-left icon-4x"></i>
	<p style="font-weight:bold;font-size:18px;"><?php echo $intro ?> Library Information</p>
</div>

<br/>

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
	foreach ($data as $item) {
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
