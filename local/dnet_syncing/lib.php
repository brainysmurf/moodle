<?php

function local_dnet_syncing_cron() {
	// 24-hour format of an hour without leading zeros
	$hour = date('G');

	// If the current hour is 3AM
	if ($hour == 3) {
		// Check when the last import was
		$lastRun = local_dnet_syncing_last_run();

		// If the last import was not in the last 12 hours, run...
		if (true || now() - $lastRun->time >= 43200) {
			local_dnet_syncing_run_destiny_import();
		}
	}
}

function local_dnet_syncing_last_run() {
	global $DB;
	$row = $DB->get_record_sql('SELECT * FROM {dnet_destiny_log} ORDER BY id DESC LIMIT 1');
	// Add the date as a timestamp since that's what everything calling this function wants
	$row->time = strtotime($row->date);
	return $row;
}

function local_dnet_syncing_run_destiny_import() {

	global $DB;

	// Get the data from Destiny
	require_once __DIR__ . '/destiny/Destiny.php';
	$destiny = new Destiny();
	$destinyData = $destiny->getOverdueBooks();

	if (!$destinyData) {
		return false;
	}

	// Clear existing data in the table
	$DB->delete_records('dnet_destiny_imported');

	$recordCount = count($destinyData);

	// Add each row from Destiny to the imported table
	foreach ($destinyData as $row) {
		// Convert due date to a timestamp
		$row['due'] = strtotime($row['due']);
		$DB->insert_record('dnet_destiny_imported', $row);
	}

	// Save info about this import in the log table
	$logRow = new stdClass;
	$logRow->date = date('Y-m-d H:i:s');
	$logRow->records = $recordCount;
	$DB->insert_record('dnet_destiny_log', $logRow);

	return $recordCount;
}
