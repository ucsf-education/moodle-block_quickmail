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
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// library functions for the admin email part of quickmail.

class Message {
    public $subject,
            $text,
            $html,
            $users,
            $admins,
            $warnings,
            $noreply,
            $sentusers,
            $failuserids,
            $starttime,
            $endtime;

    /*  Message constructor
     *  Constructs a message object for mailing to groups filtered by admin_email
     *  @param array $data - an array of variables related to the emailing
     *  @param array $users - an array of users to be emailed
     * @return void / nothing
     */
    public function __construct($data, $users) {
        global $DB;
        $this->warnings = [];

        $this->subject  = $data->subject;
        $this->html     = $data->message_editor['text'];
        $this->text     = strip_tags($data->message_editor['text']);
        $this->noreply  = $data->noreply;
        $this->warnings = [];
        $this->users    = array_values($DB->get_records_list('user', 'id', $users));
        $this->failuserids = [];
    }

    /*
     * sends the message
     * @params array $users
     * @return array $this->failuserids;
     */
    public function send($users = null) {

        $this->startTime = time();
        $users = empty($users) ? $this->users : $users;

        $noreplyuser                = new stdClass();
        $noreplyuser->firstname     = 'Moodle';
        $noreplyuser->lastname      = 'Administrator';
        $noreplyuser->username      = 'moodleadmin';
        $noreplyuser->email         = $this->noreply;
        $noreplyuser->maildisplay   = 2;
        $noreplyuser->alternatename = "";
        $noreplyuser->firstnamephonetic = "";
        $noreplyuser->lastnamephonetic = "";
        $noreplyuser->middlename = "";
        if (empty($users)) {
            $this->warnings[] = get_string('no_users', 'block_quickmail');
        }
        foreach ($users as $user) {
            $success = email_to_user(
                $user, // to
                $noreplyuser, // from
                $this->subject, // subj
                $this->text, // body in plain text
                $this->html, // body in HTML
                '', // attachment
                '', // attachment name
                true, // user true address ($USER)
                $this->noreply, // reply-to address
                get_string('pluginname', 'block_quickmail') // reply-to name
            );
            if (!$success) {
                $this->warnings[] = get_string('email_error', 'block_quickmail', $user);
                $this->failuserids[] = $user->id;
            } else {
                $this->sentUsers[] = $user->username;
            }
        }

        $this->endTime = time();

        return $this->failuserids;
    }

    /* builds a receipt emailed to admin that displays details of the group message
     * @return string $usersLine.$warnline.$timeLine.$msgLine.$recipLine
     */

    public function buildadminreceipt() {
        global $CFG, $DB;
        $adminids     = explode(',', $CFG->siteadmins);
        $this->admins = $DB->get_records_list('user', 'id', $adminids);

        $usersline      = quickmail::_s('message_sent_to') . " " . count($this->sentUsers) . " " . quickmail::_s('users') . " <br /> ";
        $timeline       = quickmail::_s('time_elapsed') . " " . ($this->endTime - $this->startTime) . " " . quickmail::_s('seconds') . " <br />";
        $warnline       = quickmail::_s('warnings') . " " . count($this->warnings) . " <br />";
        $msgline        = quickmail::_s('message_body_as_follows') . " <br/><br/><hr/>" . $this->html . "<hr />";
        if (count($this->sentUsers) > 0) {
            $recipline      = quickmail::_s("sent_successfully_to_the_following_users") . " <br/><br/> " . implode(', ', $this->sentUsers);
        } else {
            $recipline  = quickmail::_s('something_broke');
        }
        return $usersline . $timeline . $warnline . $msgline . $recipline;
    }

    /*
     * sends the admin receipt
     */
    public function sendadminreceipt() {
        $this->html = $this->buildAdminReceipt();
        $this->text = $this->buildAdminReceipt();
        $this->subject  = quickmail::_s("admin_email_send_receipt");
        $this->send($this->admins);
    }
}
