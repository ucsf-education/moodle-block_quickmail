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
 * Data generator for block_quickmail.
 *
 * @package    block_quickmail
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Quickmail block data generator class.
 *
 * @package    block_quickmail
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_quickmail_generator extends testing_block_generator {

    /**
     * Creates and returns a quickmail log (aka "sent message") record with the given data points.
     *
     * @param stdClass $user The message sender.
     * @param stdClass $course The course that this message was sent in.
     * @param array $recipients A list of user records that are recipients of this message.
     * @param stdClass|null $alternateemail An alternate email to be used as sender.
     * @param string|null $subject The message subject.
     * @param string|null $message The message contents.
     * @param string|null $attachment A comma-separated list of attachment names.
     * @param int|null $format The message format.
     * @param int|null $timesent The timestamp of when the message was sent.
     * @param string|null $faileduserids A comma-separated list of user IDs where message delivery failed.
     * @param string|null $additionalemails A comma-separated list of emails that this message was sent to.
     * @return stdClass The log record.
     * @throws dml_exception
     */
    public function create_quickmail_log(
        stdClass $user,
        stdClass $course,
        array $recipients = [],
        ?stdClass $alternateemail = null,
        ?string $subject = null,
        ?string $message = null,
        ?string $attachment = null,
        ?int $format = null,
        ?int $timesent = null,
        ?string $faileduserids = null,
        ?string $additionalemails = null,
    ): stdClass {
        global $DB;

        $record = [];
        $record['courseid'] = $course->id;
        $record['userid'] = $user->id;
        $record['alternateid'] = $alternateemail?->id;
        $recipientids = [];
        foreach ($recipients as $recipient) {
            $recipientids[] = $recipient->id;
        }
        $record['mailto'] = implode(',', $recipientids);
        $record['subject'] = $subject ?? 'test subject';
        $record['message'] = $message ?? 'test message';
        $record['attachment'] = $attachment ?? '';
        $record['format'] = $format ?? 0;
        $record['time'] = $timesent ?? time();
        $record['failuserids'] = $faileduserids ?? '';
        $record['additional_emails'] = $additionalemails ?? '';

        $id = $DB->insert_record('block_quickmail_log', $record);
        return $DB->get_record('block_quickmail_log', ['id' => $id]);
    }

    /**
     * Creates a quickmail draft (aka "message draft") record with the given data points.
     *
     * @param stdClass $user The message sender.
     * @param stdClass $course The course that this message will be sent in.
     * @param array $recipients A list of user records that will be recipients of this message.
     * @param stdClass|null $alternateemail An alternate email to be used as sender.
     * @param string|null $subject The message subject.
     * @param string|null $message The message contents.
     * @param string|null $attachment A comma-separated list of attachment names.
     * @param int|null $format The message format.
     * @param int|null $timecreated The timestamp of when the draft was created.
     * @param string|null $additionalemails A comma-separated list of emails that this message will be sent to.
     * @return stdClass The message draft record.
     * @throws dml_exception
     */
    public function create_quickmail_draft(
        stdClass $user,
        stdClass $course,
        array $recipients = [],
        ?stdClass $alternateemail = null,
        ?string $subject = null,
        ?string $message = null,
        ?string $attachment = null,
        ?int $format = null,
        ?int $timecreated = null,
        ?string $additionalemails = null,
    ): stdClass {
        global $DB;

        $record = [];
        $record['courseid'] = $course->id;
        $record['userid'] = $user->id;
        $record['alternateid'] = $alternateemail?->id;
        $recipientids = [];
        foreach ($recipients as $recipient) {
            $recipientids[] = $recipient->id;
        }
        $record['mailto'] = implode(',', $recipientids);
        $record['subject'] = $subject ?? 'test subject';
        $record['message'] = $message ?? 'test message';
        $record['attachment'] = $attachment ?? '';
        $record['format'] = $format ?? 0;
        $record['time'] = $timecreated ?? time();
        $record['additional_emails'] = $additionalemails ?? '';

        $id = $DB->insert_record('block_quickmail_drafts', $record);
        return $DB->get_record('block_quickmail_drafts', ['id' => $id]);

    }

    /**
     * Creates a quickmail email signature record with the given data points.
     * @param stdClass $user The signature owner.
     * @param string|null $title The title of this signature.
     * @param string|null $signature The email signature.
     * @param int|null $isdefault Set to 1 to indicate that this is the user's default signature, 0 otherwise.
     * @return stdClass The signature record.
     * @throws dml_exception
     */
    public function create_quickmail_signature(
        stdClass $user,
        ?string $title = null,
        ?string $signature = null,
        ?int $isdefault = null
    ): stdClass {
        global $DB;

        $record = [];
        $record['userid'] = $user->id;
        $record['title'] = $title ?? "email signature for user $user->id";
        $record['signature'] = $signature ?? "$user->id signature";
        $record['default_flag'] = $isdefault ?? 0;
        $id = $DB->insert_record('block_quickmail_signatures', $record);
        return $DB->get_record('block_quickmail_signatures', ['id' => $id]);
    }

    /**
     * Creates a quickmail alternate email sender record for the given course.
     *
     * @param stdClass $course The course.
     * @param ?string $email The alternate email address.
     * @param ?int $valid Set to 1 to indicate a valid record, 0 for invalid.
     * @return stdClass The alternate email record.
     * @throws dml_exception
     */
    public function create_quickmail_alternate(stdClass $course, ?string $email = null, ?int $valid = null): stdClass {
        global $DB;

        $record = [];
        $record['courseid'] = $course->id;
        $record['address'] = $email ?? "course$course->id@example.edu";
        $record['valid'] = $valid ?? 1;
        $id = $DB->insert_record('block_quickmail_alternate', $record);
        return $DB->get_record('block_quickmail_alternate', ['id' => $id]);
    }
}
