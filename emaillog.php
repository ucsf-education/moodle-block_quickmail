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
 * The email history page.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);
$type = optional_param('type', 'log', PARAM_ALPHA);
$typeid = optional_param('typeid', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new moodle_exception('no_course', 'block_quickmail', '', $courseid);
}

$context = context_course::instance($courseid);

// Has to be in on of these.
if (!in_array($type, ['log', 'drafts'])) {
    throw new moodle_exception('not_valid', 'block_quickmail', '', $type);
}

$canimpersonate = has_capability('block/quickmail:canimpersonate', $context);
if (!$canimpersonate && $userid != $USER->id) {
    throw new moodle_exception('not_valid_user', 'block_quickmail');
}

$config = quickmail::load_config($courseid);

$validactions = ['delete', 'confirm'];

$cansend = has_capability('block/quickmail:cansend', $context);

$properpermission = ($cansend || !empty($config['allowstudents']));

// Managers can delete by capability 'candelete'.
// Those with 'cansend' (incl students, if $config['allowstudents']) can only delete drafts.
$candelete = (
    has_capability('block/quickmail:candelete', $context) ||
    ($cansend && $type == 'drafts') || ($properpermission && $type == 'drafts')
);

// Stops students from tempering with history.
if (!$properpermission || (!$candelete && in_array($action, $validactions))) {
    throw new moodle_exception('no_permission', 'block_quickmail');
}

if (isset($action) && !in_array($action, $validactions)) {
    throw new moodle_exception('not_valid_action', 'block_quickmail', '', $action);
}

if (isset($action) && empty($typeid)) {
    throw new moodle_exception('not_valid_typeid', 'block_quickmail', '', $action);
}

$blockname = quickmail::_s('pluginname');
$header = quickmail::_s($type);

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/quickmail/emaillog.php', ['courseid' => $courseid]);
$PAGE->set_pagetype(quickmail::PAGE_TYPE);
$PAGE->set_pagelayout('standard');

$dbtable = 'block_quickmail_' . $type;

$params = ['userid' => $userid, 'courseid' => $courseid];
$count = $DB->count_records($dbtable, $params);

switch ($action) {
    case "confirm":
        if (quickmail::cleanup($dbtable, $context->id, $typeid)) {
            $url = new moodle_url('/blocks/quickmail/emaillog.php', [
                'courseid' => $courseid,
                'type' => $type,
            ]);
            redirect($url);
        } else {
            throw new moodle_exception('delete_failed', 'block_quickmail', '', $typeid);
        }
        break;
    case "delete":
        $html = quickmail::delete_dialog($courseid, $type, $typeid);
        break;
    default:
        $html = quickmail::list_entries($courseid, $type, $page, $perpage, $userid, $count, $candelete);
}

if ($courseid == SITEID) {
    $html .= html_writer::link(
        new moodle_url('/blocks/quickmail/admin_email.php'),
        quickmail::_s('composenew')
    );
} else {
    $html .= html_writer::link(
        new moodle_url(
            '/blocks/quickmail/email.php',
            ['courseid' => $courseid]
        ),
        quickmail::_s('composenew')
    );
}

if ($canimpersonate && $USER->id != $userid) {
    $user = $DB->get_record('user', ['id' => $userid]);
    // See http://docs.moodle.org/dev/Additional_name_fields .
    $header .= ' for ' . fullname($user);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

if ($canimpersonate) {
    $getnamestring = 'u.firstname, u.lastname';

    if ($CFG->version >= 2013111800) {
        $getnamestring = block_quickmail_get_all_user_name_fields('u');
    }
    $sql = "SELECT DISTINCT(l.userid)," . $getnamestring . "
                FROM {block_quickmail_$type} l,
                     {user} u
                WHERE u.id = l.userid AND courseid = ? ORDER BY u.lastname";

    $users = $DB->get_records_sql($sql, [$courseid]);
    $useroptions = array_map(function ($user) {
        return fullname($user);
    }, $users);

    $url = new moodle_url('emaillog.php', [
        'courseid' => $courseid,
        'type' => $type,
    ]);

    $defaultoption = ['' => quickmail::_s('select_users')];

    echo $OUTPUT->single_select($url, 'userid', $useroptions, $userid, $defaultoption);
}

if (empty($count)) {
    echo $OUTPUT->notification(quickmail::_s('no_' . $type));

    if ($COURSE->id == 1) {
        echo $OUTPUT->continue_button('/blocks/quickmail/admin_email.php?courseid=' . $courseid);
    } else {
        echo $OUTPUT->continue_button('/blocks/quickmail/email.php?courseid=' . $courseid);
    }

    echo $OUTPUT->footer();
    exit;
}

echo $html;

echo $OUTPUT->footer();
