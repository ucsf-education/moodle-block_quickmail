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
 * Quickmail message file.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The quickmail message class.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Message {
    /** @var string The subject line of the message. */
    public $subject;

    /** @var string The message contents as plain text. */
    public $text;

    /** @var string The message contents as markup. */
    public $html;

    /** @var array A list of recipient user records. */
    public $users;

    /** @var array A list of administrator user records, indexed by their record id. */
    public $admins;

    /** @var array A list of warnings created during the sending of the message. */
    public $warnings;

    /** @var string The no-reply-to email address. */
    public $noreply;

    /** @var array A list of recipient user ids where message sending failed. */
    public $failuserids;

    /** @var array A list of recipient usernames where message sending succeeded. */
    public $sendusers;

    /** @var int Message sending start time. */
    public $starttime;

    /** @var int Message sending end time. */
    public $endtime;

    /**
     * Constructs a message object for mailing to groups filtered by admin_email.
     *
     * @param stdClass $data Message data.
     * @param array $users An array of users to be emailed.
     * @throws dml_exception
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
        $this->sendusers = [];
    }

    /**
     * Sends the message.
     *
     * @param array|null $users An optional list of recipients, overrides the recipients added to the $users attribute if given.
     * @return array A list of recipient user ids where delivery failed.
     * @throws coding_exception
     */
    public function send($users = null): array {

        $this->starttime = time();
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
                $user,
                $noreplyuser,
                $this->subject,
                $this->text,
                $this->html,
                '',
                '',
                true,
                $this->noreply,
                get_string('pluginname', 'block_quickmail')
            );
            if (!$success) {
                $this->warnings[] = get_string('email_error', 'block_quickmail', $user);
                $this->failuserids[] = $user->id;
            } else {
                $this->sendusers[] = $user->username;
            }
        }

        $this->endtime = time();

        return $this->failuserids;
    }

    /**
     * Builds a receipt emailed to admin that displays details of the group message.
     *
     * @return string The receipt.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function buildadminreceipt(): string {
        global $CFG, $DB;
        $adminids     = explode(',', $CFG->siteadmins);
        $this->admins = $DB->get_records_list('user', 'id', $adminids);

        $usersline = quickmail::_s('message_sent_to')
            . " " . count($this->sendusers)
            . " " . quickmail::_s('users')
            . " <br /> ";
        $timeline = quickmail::_s('time_elapsed')
            . " " . ($this->endtime - $this->starttime)
            . " "
            . quickmail::_s('seconds') . " <br />";
        $warnline = quickmail::_s('warnings') . " " . count($this->warnings) . " <br />";
        $msgline = quickmail::_s('message_body_as_follows') . " <br/><br/><hr/>" . $this->html . "<hr />";
        if (count($this->sendusers) > 0) {
            $recipline = quickmail::_s("sent_successfully_to_the_following_users")
                . " <br/><br/> "
                . implode(', ', $this->sendusers);
        } else {
            $recipline = quickmail::_s('something_broke');
        }
        return $usersline . $timeline . $warnline . $msgline . $recipline;
    }

    /**
     * Sends the admin receipt.
     */
    public function sendadminreceipt(): void {
        $this->html = $this->buildAdminReceipt();
        $this->text = $this->buildAdminReceipt();
        $this->subject  = quickmail::_s("admin_email_send_receipt");
        $this->send($this->admins);
    }
}
