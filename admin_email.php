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
 * Page for the admin email form.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
global $CFG, $USER, $SESSION, $PAGE, $SITE, $OUTPUT, $DB;
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->libdir/adminlib.php");
require_once("$CFG->dirroot/user/filters/lib.php");
require_once('lib.php');
require_once('classes/message.php');
require_once('admin_email_form.php');

require_login();
// Get page params.
$page       = optional_param('page', 0, PARAM_INT);
$perpage    = optional_param('perpage', 20, PARAM_INT);
$sort       = optional_param('sort', 'lastname', PARAM_ACTION);
$direction  = optional_param('dir', 'ASC', PARAM_ACTION);
$courseid   = optional_param('courseid', '', PARAM_INT);
$type       = optional_param('type', '', PARAM_ALPHA);
$typeid     = optional_param('typeid', 0, PARAM_INT);
$fmid       = optional_param('fmid', 0, PARAM_INT);

$blockname  = get_string('pluginname', 'block_quickmail');
$header     = get_string('sendadmin', 'block_quickmail');

$context    = context_system::instance();

if (has_capability('block/quickmail:myaddinstance', $context) || is_siteadmin($USER)) {
    // Page params for ui filter.
    $filterparams = $typeid > 0 ? ['courseid' => $courseid, 'type' => $type, 'typeid' => $typeid] : null;

    $PAGE->set_context($context);
    $PAGE->set_url($CFG->wwwroot . '/blocks/quickmail/admin_email.php');
    $PAGE->navbar->add($blockname);
    $PAGE->navbar->add($header);
    $PAGE->set_heading($SITE->shortname . ': ' . $blockname);
    $PAGE->set_pagetype(quickmail::PAGE_TYPE);
    $PAGE->set_pagelayout('standard');

    if ($type == 'log') {
        $logmessage = $DB->get_record('block_quickmail_' . $type, ['id' => $typeid]);
        // Try to get the saved, serialized filters from mailto.
        if (isset($logmessage->mailto)) {
            // Will give a Notice if content of mailto in not unserializable.
            $filters = @unserialize($logmessage->mailto);
            if ($filters !== false && is_array($filters) && ( empty($_POST['addfilter']) && empty($_POST['removeselected']) )) {
                $SESSION->user_filtering = $filters;
            }
        }
    }

    // Get Our users.
    $fields = [
        'realname'      => 1,
        'lastname'      => 1,
        'firstname'     => 1,
        'email'         => 1,
        'city'          => 1,
        'country'       => 1,
        'confirmed'     => 1,
        'suspended'     => 1,
        'profile'       => 1,
        'courserole'    => 0,
        'systemrole'    => 0,
        'username'      => 0,
        'cohort'        => 1,
        'firstaccess'   => 1,
        'lastaccess'    => 0,
        'neveraccessed' => 1,
        'timemodified'  => 1,
        'nevermodified' => 1,
        'auth'          => 1,
        'mnethostid'    => 1,
        'language'      => 1,
        'firstnamephonetic' => 1,
        'lastnamephonetic' => 1,
        'middlename' => 1,
        'alternatename' => 1,
        ];

    $ufiltering         = new user_filtering($fields, null, $filterparams);
    [$sql, $params] = $ufiltering->get_sql_filter();
    $usersearchcount    = get_users(
        false,
        '',
        true,
        null,
        '',
        '',
        '',
        '',
        '',
        '*',
        $sql,
        $params
    );

    if ($fmid == 1) {
        $sql = 'id IN (' . $logmessage->failuserids . ')';
    }

    $displayusers  = empty($sql) ? [] :
        get_users_listing(
            $sort,
            $direction,
            $page * $perpage,
            $perpage,
            '',
            '',
            '',
            $sql,
            $params
        );

    $users          = empty($sql) ? [] :
        get_users_listing(
            $sort,
            $direction,
            0,
            0,
            '',
            '',
            '',
            $sql,
            $params
        );

    $editoroptions = [
            'trusttext' => true,
            'subdirs' => 1,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'accepted_types' => '*',
            'context' => $context,
        ];

    $form = new admin_email_form(null, [
        'editor_options' => $editoroptions,
    ]);

    // Process data submission.
    if ($form->is_cancelled()) {
        unset($SESSION->user_filtering);
        redirect(new moodle_url('/blocks/quickmail/admin_email.php'));
    } else if ($data = $form->get_data()) {
        $message = new Message($data, array_keys($users));

        // Todo: Refactor so that we're not building two similar structures, namely: $data and $message.
        $data->courseid   = SITEID;
        $data->userid     = $USER->id;
        $data->mailto     = isset($SESSION->user_filtering) ? serialize($SESSION->user_filtering) : "unknown filter";
        $data->format     = $data->message_editor['format'];
        $data->message    = $data->message_editor['text'];
        $data->attachment = '';
        $data->time = time();

        // Save record of the message, regardless of errors.
        $data->id = $DB->insert_record('block_quickmail_log', $data);
        // Send the messages and save the failed users if there are any.
        $data->failuserids = implode(',', $message->send());
        $message->sendAdminReceipt();

        // Finished processing.
        // Empty errors mean that you can go back home.
        if (empty($message->warnings)) {
            unset($SESSION->user_filtering);
            if (is_siteadmin($USER->id)) {
                redirect(new moodle_url('/blocks/quickmail/emaillog.php', ['courseid' => $COURSE->id]));
            } else {
                redirect(new moodle_url('/my', null));
            }
        } else {
            // Update DB to reflect fail status.
            $data->status = quickmail::_s('failed_to_send_to') + count($message->warnings) + quickmail::_s('users');
            $DB->update_record('block_quickmail_log', $data);
        }
    }

    // Get data for form.
    if (!empty($type)) {
        $data = $logmessage;
        $logmessage->messageformat = $USER->mailformat;
        $logmessage = file_prepare_standard_editor(
            $logmessage,
            'message',
            $editoroptions,
            $context,
            'block_quickmail',
            $type,
            $logmessage->id
        );
    } else {
        $logmessage = new stdClass();
    }

    // Begin output.
    echo $OUTPUT->header();
    echo $OUTPUT->heading($header);

    // Notify the admin.
    if (!empty($message->warnings)) {
        foreach ($message->warnings as $warning) {
            echo $OUTPUT->notification($warning);
        }
    }

    // Start work.
    if ($fmid != 1) {
        $ufiltering->display_add();
        $ufiltering->display_active();
    }

    $pagingbar = !$sql ? '' :
        $OUTPUT->paging_bar(
            $usersearchcount,
            $page,
            $perpage,
            new moodle_url('/blocks/quickmail/admin_email.php', [
                'sort' => $sort,
                'dir' => $direction,
                'perpage' => $perpage,
            ])
        );

    if (!empty($sql)) {
        echo $OUTPUT->heading("Found $usersearchcount User(s)");
    }

    echo $pagingbar;

    if (!empty($displayusers)) {
        $columns = ['firstname', 'lastname', 'email', 'city', 'lastaccess'];
        foreach ($columns as $column) {
            $direction = ($sort == $column && $direction == "ASC") ? "DESC" : "ASC";
            $$column = html_writer::link('admin_email.php?sort=' . $column . '&dir=' .
                $direction, get_string($column));
        }
        $table = new html_table();

        $table->head = ["$firstname / $lastname", $email, $city, $lastaccess];
        $table->data = array_map(function ($user) {
            $fullname = fullname($user);
            $email    = $user->email;
            $city     = $user->city;
            $lastaccesstime = isset($user->lastaccess) ?
                format_time(time() - $user->lastaccess) : get_string('never');
            return [$fullname, $email, $city, $lastaccesstime];
        }, $displayusers);
        echo html_writer::table($table);
    }

    // Need no-reply in both cases.
    $logmessage->noreply = $CFG->noreplyaddress;

    // Display form and done.
    $form->set_data($logmessage);
    echo $form->display();
    echo $pagingbar;
    echo $OUTPUT->footer();
}
