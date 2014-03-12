<?php

function xmldb_local_dnet_reset_passwords_install($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    // Define table dnet_pwreset_keys to be created.
    $table = new xmldb_table('dnet_pwreset_keys');

    // Adding fields to table dnet_pwreset_keys.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_field('key', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('used', XMLDB_TYPE_INTEGER, '1', null, null, null, null);

    // Adding keys to table dnet_pwreset_keys.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for dnet_pwreset_keys.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // Dnet_reset_passwords savepoint reached.
    upgrade_plugin_savepoint(true, 2014022305, 'local', 'dnet_reset_passwords');
}
