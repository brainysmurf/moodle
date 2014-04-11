<?php

require_once dirname(dirname(__DIR__)) .  '/config.php';
require_once dirname(__DIR__) . '/dnet_common/sharedlib.php';
require_once __DIR__ . '/destiny/Destiny.php';

// Show page header
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/library');
$PAGE->set_title('SSIS Library');
$PAGE->set_heading('Library');
echo $OUTPUT->header();

// Get the Powerschool ID number for the user to display
if (!empty($_GET['idnumber'])) {
	// For easier testing, add ?idnumber=123 to the URL
	$idnumber = $_GET['idnumber'];
} else {
	require_login();
	$idnumber = $USER->idnumber;
}

// Require an ID number to continue
if (empty($idnumber)) {
    death("Your account doesn't seem to have a PowerSchool ID. That's strange!");
}

// Create Destiny access object
$destiny = new Destiny();

// Is the user a parent on a "normal" user?
if (strpos($idnumber, 'P') === 4) {
	$parent = true;
	$familyID = substr($idnumber, 0, 4);
	$introText = "Listed below are your family's checked out library resources.";
	$data = $destiny->getFamilyCheckedOutBooks($familyID);
} else {
	$parent = false;
	$introText = "Listed below are your checked out library resources.";
	$data = $destiny->getUsersCheckedOutBooks($idnumber);
}

?>
<div class="local-alert"><i class="icon-info-sign pull-left icon-2x"></i>
	<p style="font-weight:bold;font-size:18px;"><?php echo $introText; ?></p>
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
		$item->due = strtotime($item->due);
		$overdue = $item->due < time();
	    ?>
	    <tr>
		    <td class="cell c0"><p><?php echo $item->patron_name; ?></p></td>
		    <td class="cell c1"><p><?php echo $item->title ?></p></td>
		    <td class="cell c0"><p><?php echo $item->call_number ?></p></td>
		    <td class="cell c1"><p <?=($overdue ? 'class="red"' : '')?>><?php echo date('l F jS Y', $item->due) ?></p></td>
	    </tr>
	    <?php
	}
	?>
</table>

<?php
echo $OUTPUT->footer();
