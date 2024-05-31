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
 * Class library for alternate email functionality.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
/**
 * Alternate action interface.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface quickmail_alternate_actions {
    /** @var string View action. */
    const VIEW = 'view';

    /** @var string Delete Action */
    const DELETE = 'delete';

    /** @var string Interact action. */
    const INTERACT = 'interact';

    /** @var string Confirmed action. */
    const CONFIRMED = 'confirmed';

    /** @var string Information action. */
    const INFORMATION = 'inform';

    /** @var string Verify action. */
    const VERIFY = 'verify';
}

/**
 * Alternate action base class.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class quickmail_alternate implements quickmail_alternate_actions {
    /**
     * Creates and returns a base URL to the alternate email page for a given course.
     *
     * @param int $courseid The course ID.
     * @param array $additional Additional URL query parameters.
     * @return moodle_url The generated URL.
     * @throws moodle_exception
     */
    private static function base_url($courseid, $additional = []) {
        $params = ['courseid' => $courseid] + $additional;
        return new moodle_url('/blocks/quickmail/alternate.php', $params);
    }

    /**
     * Retrieves a list of alternate email records for a given course.
     *
     * @param stdClass $course A course object.
     * @return array A list of alternate email records.
     * @throws dml_exception
     */
    public static function get($course) {
        global $DB;

        $params = ['courseid' => $course->id];
        return $DB->get_records('block_quickmail_alternate', $params, 'valid DESC');
    }

    /**
     * Retrieves a alternate email record by its ID.
     * @param int $id The ID.
     * @return stdClass The alternate email record.
     * @throws dml_exception If none or multiples are found.
     */
    public static function get_one($id) {
        global $DB;

        $params = ['id' => $id];
        return $DB->get_record('block_quickmail_alternate', $params, '*', MUST_EXIST);
    }

    /**
     * Returns the markup for an alternate email deletion confirmation dialog.
     * @param stdClass $course The course.
     * @param int $id The alternate email ID.
     * @return string The deletion confirmation dialog markup.
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function delete($course, $id) {
        global $OUTPUT, $DB;

        $email = self::get_one($id);

        $confirmurl = self::base_url($course->id, [
            'id' => $email->id, 'action' => self::CONFIRMED,
        ]);

        $cancelurl = self::base_url($course->id);

        return $OUTPUT->confirm(quickmail::_s('sure', $email), $confirmurl, $cancelurl);
    }

    /**
     * Deletes a given alternate email, then redirects the user to the alternate email page for a given course.
     *
     * @param stdClass $course The course.
     * @param int $id The alternate email ID.
     * @return null
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function confirmed($course, $id) {
        global $DB;

        $DB->delete_records('block_quickmail_alternate', ['id' => $id]);

        return redirect(self::base_url($course->id, ['flash' => 1]));
    }

    /**
     * Verifies the given alternate email and prints a notification dialog to the user on completion.
     *
     * @param stdClass $course The course.
     * @param int $id The alternate email ID.
     * @return string The notification dialog markup.
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function verify($course, $id) {
        global $DB, $OUTPUT;

        $entry = self::get_one($id);

        $value = optional_param('key', null, PARAM_TEXT);
        $userid = optional_param('activator', null, PARAM_INT);

        $params = [
            'instance' => $course->id,
            'value' => $value,
            'userid' => $userid,
            'script' => 'blocks/quickmail',
        ];

        $backurl = self::base_url($course->id);

        // Pass through already valid entries.
        if ($entry->valid) {
            redirect($backurl);
        }

        // Verify key.
        if (empty($value) || !$key = $DB->get_record('user_private_key', $params)) {
            $reactivate = self::base_url($course->id, [
                'id' => $id, 'action' => self::INFORMATION,
            ]);

            $html = $OUTPUT->notification(quickmail::_s('entry_key_not_valid', $entry));
            $html .= $OUTPUT->continue_button($reactivate);
            return $html;
        }

        // One at a time...They can resend the link if they want.
        delete_user_key('blocks/quickmail', $userid);

        $entry->valid = 1;
        $DB->update_record('block_quickmail_alternate', $entry);

        $entry->course = $course->fullname;

        $html = $OUTPUT->notification(quickmail::_s('entry_activated', $entry), 'notifysuccess');
        $html .= $OUTPUT->continue_button($backurl);

        return $html;
    }

    /**
     * Sends a verification email to a given alternate email and prints a notification dialog to the user on completion.
     * @param stdClass $course The course.
     * @param int $id The alternate email ID.
     * @return string The notification dialog markup.
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function inform($course, $id) {
        global $DB, $OUTPUT, $USER;

        $entry = self::get_one($id);

        // No restriction.
        // Valid forever.
        $value = get_user_key('blocks/quickmail', $USER->id, $course->id);

        $url = self::base_url($course->id);

        $approvalurl = self::base_url($course->id, [
            'id' => $id, 'action' => self::VERIFY,
            'activator' => $USER->id, 'key' => $value,
        ]);

        $a = new stdClass();
        $a->address = $entry->address;
        $a->url = html_writer::link($approvalurl, $approvalurl->out());
        $a->course = $course->fullname;
        $a->fullname = fullname($USER);

        $from = quickmail::_s('alternate_from');
        $subject = quickmail::_s('alternate_subject');
        $htmlbody = quickmail::_s('alternate_body', $a);
        $body = strip_tags($htmlbody);

        // Send email.
        $user = clone($USER);
        $user->email = $entry->address;
        $user->firstname = quickmail::_s('pluginname');
        $user->lastname = quickmail::_s('alternate');

        $result = email_to_user($user, $from, $subject, $body, $htmlbody);

        // Create the event, trigger it.
        $event = \block_quickmail\event\alternate_email_added::create([
            'courseid' => $course->id,
            'context' => context_course::instance($course->id),
            'other'    => [
                'address' => $entry->address,
            ],
        ]);
        $event->trigger();

        $html = $OUTPUT->box_start();

        if ($result) {
            $html .= $OUTPUT->notification(quickmail::_s('entry_saved', $entry), 'notifysuccess');
            $html .= html_writer::tag('p', quickmail::_s('entry_success', $entry));
        } else {
            $html .= $OUTPUT->notification(quickmail::_s('entry_failure', $entry));
        }

        $html .= $OUTPUT->continue_button($url);
        $html .= $OUTPUT->box_end();

        return $html;
    }

    /**
     * Returns edit form for a given alternate email, or saves/cancels edits.
     * @param stdClass $course The course used as context for the redirect on save/cancel.
     * @param int $id The alternate email id.
     * @return string|null The markup for the edit form, or no return value on save/cancel.
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function interact($course, $id) {
        $form = new quickmail_alternate_form(null, [
            'course' => $course, 'action' => self::INTERACT,
        ]);

        if ($form->is_cancelled()) {
            redirect(self::base_url($course->id));
        } else if ($data = $form->get_data()) {
            global $DB;

            // Check if email exists in this course.
            $older = $DB->get_record('block_quickmail_alternate', [
                'address' => $data->address, 'courseid' => $data->courseid,
            ]);

            if ($older) {
                $data->id = $older->id;
                $data->valid = $older->valid;
            } else if (!empty($data->id)) {
                // Changed address?
                if ($data->valid) {
                    $older = self::get_one($id);

                    $valid = $older->address != $data->address ? 0 : 1;

                    $data->valid = $valid;
                }

                $DB->update_record('block_quickmail_alternate', $data);
            } else {
                unset($data->id);
                $data->id = $DB->insert_record('block_quickmail_alternate', $data);
            }

            $action = $data->valid ? self::VERIFY : self::INFORMATION;

            redirect(self::base_url($course->id, [
                'action' => $action, 'id' => $data->id,
            ]));
        }

        if ($id) {
            $form->set_data(self::get_one($id));
        }

        // See: MDL-31677.
        $reflect = new ReflectionClass('quickmail_alternate_form');
        $formfield = $reflect->getProperty('_form');
        $formfield->setAccessible(true);

        return $formfield->getValue($form)->toHtml();
    }

    /**
     * Prints out alternative emails for a given course.
     * @param stdClass $course The course.
     * @return string The markup.
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function view($course) {
        global $OUTPUT;

        $alternates = self::get($course);

        $newurl = self::base_url($course->id, ['action' => self::INTERACT]);

        if (empty($alternates)) {
            $html = $OUTPUT->notification(quickmail::_s('no_alternates', $course));
            $html .= $OUTPUT->continue_button($newurl);
            return $html;
        }

        $table = new html_table();
        $table->head = [
            get_string('email'),
            quickmail::_s('valid'),
            get_string('action'),
        ];

        $approval = [quickmail::_s('waiting'), quickmail::_s('approved')];

        $icons = [
            self::INTERACT => $OUTPUT->pix_icon('i/edit', get_string('edit')),
            self::DELETE => $OUTPUT->pix_icon('i/invalid', get_string('delete')),
        ];

        foreach ($alternates as $email) {
            $editurl = self::base_url($course->id, [
                'action' => self::INTERACT, 'id' => $email->id,
            ]);

            $edit = html_writer::link($editurl, $icons[self::INTERACT]);

            $deleteurl = self::base_url($course->id, [
                'action' => self::DELETE, 'id' => $email->id,
            ]);

            $delete = html_writer::link($deleteurl, $icons[self::DELETE]);

            $row = [
                $email->address,
                $approval[$email->valid],
                implode(' | ', [$edit, $delete]),
            ];

            $table->data[] = new html_table_row($row);
        }

        $newlink = html_writer::link($newurl, quickmail::_s('alternate_new'));

        $html = html_writer::tag('div', $newlink, ['class' => 'new_link']);
        $html .= $OUTPUT->box_start();
        $html .= html_writer::table($table);
        $html .= $OUTPUT->box_end();
        return $html;
    }
}
// phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses

