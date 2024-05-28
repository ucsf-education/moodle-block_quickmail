<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Quickmail upgrade script.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Run all Quickmail upgrade steps between the current DB version and the current version on disk.
 * @param int $oldversion The old version of quickmail in the DB.
 * @return bool
 * @throws coding_exception
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_block_quickmail_upgrade($oldversion): bool {
    global $DB;

    $result = true;

    $dbman = $DB->get_manager();

    // 1.9 to 2.0 upgrade.
    if ($oldversion < 2011021812) {
        // Changing type of field attachment on table block_quickmail_log to text.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('attachment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'message');

        // Launch change of type for field attachment.
        $dbman->change_field_type($table, $field);

        // Rename field timesent on table block_quickmail_log to time.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('timesent', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, null, '0', 'format');

        // Conditionally launch rename field timesent.
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'time');
        }

        // Define table block_quickmail_signatures to be created.
        $table = new xmldb_table('block_quickmail_signatures');

        // Adding fields to table block_quickmail_signatures.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', true, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_CHAR, '125', null, null, null, null);
        $table->add_field('signature', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('default_flag', XMLDB_TYPE_INTEGER, '1', true, XMLDB_NOTNULL, null, '1');

        // Adding keys to table block_quickmail_signatures.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_quickmail_signatures.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_drafts to be created.
        $table = new xmldb_table('block_quickmail_drafts');

        // Adding fields to table block_quickmail_drafts.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', true, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', true, XMLDB_NOTNULL, null, '0');
        $table->add_field('mailto', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('attachment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
        $table->add_field('format', XMLDB_TYPE_INTEGER, '3', true, XMLDB_NOTNULL, null, '1');
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', true, null, null, null);

        // Adding keys to table block_quickmail_drafts.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_quickmail_drafts.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_config to be created.
        $table = new xmldb_table('block_quickmail_config');

        // Adding fields to table block_quickmail_config.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursesid', XMLDB_TYPE_INTEGER, '11', true, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '125', null, null, null, null);

        // Adding keys to table block_quickmail_config.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_quickmail_config.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint($result, 2011021812, 'quickmail');
    }

    if ($oldversion < 2012021014) {
        $table = new xmldb_table('block_quickmail_alternate');

        $field = new xmldb_field('id');
        $field->set_attributes(
            XMLDB_TYPE_INTEGER,
            '10',
            true,
            XMLDB_NOTNULL,
            true,
            null,
            null
        );

        $table->addField($field);

        $field = new xmldb_field('courseid');
        $field->set_attributes(
            XMLDB_TYPE_INTEGER,
            '10',
            true,
            XMLDB_NOTNULL,
            false,
            null,
            'id'
        );

        $table->addField($field);

        $field = new xmldb_field('address');
        $field->set_attributes(
            XMLDB_TYPE_CHAR,
            '100',
            null,
            XMLDB_NOTNULL,
            false,
            null,
            'courseid'
        );

        $table->addField($field);

        $field = new xmldb_field('valid');
        $field->set_attributes(
            XMLDB_TYPE_INTEGER,
            '1',
            true,
            XMLDB_NOTNULL,
            false,
            '0',
            'address'
        );

        $table->addField($field);

        $key = new xmldb_key('PRIMARY');
        $key->set_attributes(XMLDB_KEY_PRIMARY, ['id']);

        $table->addKey($key);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        foreach (['log', 'drafts'] as $table) {
            // Define field alternateid to be added to block_quickmail_log.
            $table = new xmldb_table('block_quickmail_' . $table);
            $field = new xmldb_field(
                'alternateid',
                XMLDB_TYPE_INTEGER,
                '10',
                true,
                null,
                null,
                null,
                'userid'
            );

            // Conditionally launch add field alternateid.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint($result, 2012021014, 'quickmail');
    }

    if ($oldversion < 2012061112) {
        // Restructure database references to the new filearea locations.
        foreach (['log', 'drafts'] as $type) {
            $params = [
                'component' => 'block_quickmail_' . $type,
                'filearea' => 'attachment',
            ];

            $attachments = $DB->get_records('files', $params);

            foreach ($attachments as $attachment) {
                $attachment->filearea = 'attachment_' . $type;
                $attachment->component = 'block_quickmail';

                $result = $result && $DB->update_record('files', $attachment);
            }
        }

        upgrade_block_savepoint($result, 2012061112, 'quickmail');
    }
    if ($oldversion < 2012061112) {
        migrate_quickmail_20();
    }

    if ($oldversion < 2014042914) {
         // Define field status to be dropped from block_quickmail_log.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('status');

        // Conditionally launch drop field status.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field status to be added to block_quickmail_log.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('failuserids', XMLDB_TYPE_TEXT, null, null, null, null, null, 'time');
        $field2 = new xmldb_field('additional_emails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'failuserids');
        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

         // Define field additional_emails to be added to block_quickmail_drafts.
        $table = new xmldb_table('block_quickmail_drafts');
        $field = new xmldb_field('additional_emails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'time');

        // Conditionally launch add field additional_emails.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2014042914, 'quickmail');
    }
    return $result;
}
