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
 * The email form.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
$PAGE->requires->js('/blocks/quickmail/validation.js');

/**
 * The email form class.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class email_form extends moodleform {
    /**
     * Returns an <option> element for a given user, appended to given markup.
     * @param string $in The markup to prepend to the <option> element.
     * @param stdClass $user A user record.
     * @return string The <option> element, appended to the given markup.
     */
    private function reduce_users($in, $user): string {
        return $in . '<option value="' . $this->option_value($user) . '">' .
               $this->option_display($user) . '</option>';
    }

    /**
     * Returns the text for an <option> element of a given user.
     * @param stdClass $user A user record.
     * @return string The text.
     */
    private function option_display($user): string {
        $userstogroups = $this->_customdata['users_to_groups'];

        if (empty($userstogroups[$user->id])) {
            $groups = quickmail::_s('no_section');
        } else {
            $onlynames = function ($group) {
                return $group->name;
            };
            $groups = implode(',', array_map($onlynames, $userstogroups[$user->id]));
        }

        return sprintf("%s (%s)", fullname($user), $groups);
    }

    /**
     * Returns the value of an <option> element for a given user.
     * @param stdClass $user The user record
     * @return string The option value
     */
    private function option_value($user): string {
        $userstogroups = $this->_customdata['users_to_groups'];
        $userstoroles = $this->_customdata['users_to_roles'];
        $onlysn = function ($role) {
            return $role->shortname;
        };
        if (!is_numeric($user->id)) {
            $roles = null;
        } else {
            $roles = implode(',', array_map($onlysn, $userstoroles[$user->id]));
        }

        // Everyone defaults to none.
        if (is_numeric($user->id)) {
            $roles .= ',none';
        }

        if (empty($userstogroups[$user->id])) {
            $groups = 0;
        } else {
            $onlyid = function ($group) {
                return $group->id;
            };
            $groups = implode(',', array_map($onlyid, $userstogroups[$user->id]));
            $groups .= ',all';
        }
            $groups .= ',allusers';
        return sprintf("%s %s %s", $user->id, $groups, $roles);
    }

    /**
     * The form definition.
     *
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function definition(): void {
        global $CFG, $USER, $COURSE, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'mailto', '');
        $mform->setType('mailto', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'type', '');
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('hidden', 'typeid', 0);
        $mform->setType('typeid', PARAM_INT);

        $roleoptions = ['none' => quickmail::_s('no_filter')];
        foreach ($this->_customdata['roles'] as $role) {
            $roleoptions[$role->shortname] = role_get_name($role);
        }

        $groupoptions = empty($this->_customdata['groups']) ? [] : [
            'allusers' => quickmail::_s('allusers'),
        ];

        $groupoptions['all'] = quickmail::_s('all_sections');
        foreach ($this->_customdata['groups'] as $group) {
            $groupoptions[$group->id] = $group->name;
        }

        $groupoptions[0] = quickmail::_s('no_section');

        $useroptions = [];
        foreach ($this->_customdata['users'] as $user) {
            $useroptions[$this->option_value($user)] = $this->option_display($user);
        }

        $links = [];
        $genurl = function ($type) use ($COURSE) {
            $emailparam = ['courseid' => $COURSE->id, 'type' => $type];
            return new moodle_url('emaillog.php', $emailparam);
        };

        $draftlink = html_writer::link($genurl('drafts'), quickmail::_s('drafts'));
        $links[] =& $mform->createElement('static', 'draft_link', '', $draftlink);

        $context = context_course::instance($COURSE->id);

        $config = quickmail::load_config($COURSE->id);

        $cansend = (
            has_capability('block/quickmail:cansend', $context) ||
            !empty($config['allowstudents'])
        );

        if ($cansend) {
            $historylink = html_writer::link($genurl('log'), quickmail::_s('history'));
            $links[] =& $mform->createElement('static', 'history_link', '', $historylink);
        }

        $mform->addGroup($links, 'links', '&nbsp;', [' | '], false);

        $table = new html_table();
        $table->attributes['class'] = 'emailtable';

        $selectedlabel = new html_table_cell();
        $selectedlabel->text = html_writer::tag(
            'strong',
            quickmail::_s('selected') . " "
        );

        $rolefilterlabel = new html_table_cell();
        $rolefilterlabel->colspan = "2";
        $rolefilterlabel->text = html_writer::tag(
            'div',
            quickmail::_s('role_filter'),
            ['class' => 'object_labels']
        );

        $selectfilter = new html_table_cell();
        $selectfilter->text = html_writer::tag(
            'select',
            array_reduce($this->_customdata['selected'], [$this, 'reduce_users'], ''),
            ['id' => 'mail_users', 'class' => 'select custom-select menu', 'multiple' => 'multiple', 'size' => 30]
        );

        $embed = function ($text, $id) {
            return html_writer::tag(
                'p',
                html_writer::empty_tag('input', [
                    'value' => $text, 'type' => 'button', 'class' => 'btn btn-secondary', 'id' => $id,
                ])
            );
        };

        $embedquick = function ($text) use ($embed) {
            return $embed(quickmail::_s($text), $text);
        };

        $centerbuttons = new html_table_cell();
        $centerbuttons->text = (
            $embed($OUTPUT->larrow() . ' ' . quickmail::_s('add_button'), 'add_button') .
            $embed(quickmail::_s('remove_button') . ' ' . $OUTPUT->rarrow(), 'remove_button') .
            $embedquick('add_all') .
            $embedquick('remove_all')
        );

        $filters = new html_table_cell();
        $filters->text = html_writer::tag(
            'div',
            html_writer::select($roleoptions, '', 'none', null, ['id' => 'roles'])
        ) . html_writer::tag(
            'div',
            quickmail::_s('potential_sections'),
            ['class' => 'object_labels']
        ) . html_writer::tag(
            'div',
            html_writer::select(
                $groupoptions,
                '',
                'all',
                null,
                ['id' => 'groups', 'multiple' => 'multiple', 'size' => 5]
            )
        ) . html_writer::tag(
            'div',
            quickmail::_s('potential_users'),
            ['class' => 'object_labels']
        ) . html_writer::tag(
            'div',
            html_writer::select(
                $useroptions,
                '',
                '',
                null,
                ['id' => 'from_users', 'multiple' => 'multiple', 'size' => 20]
            )
        );

        // DWE -> NON REQUIRED VERSION.
        $table->data[] = new html_table_row([$selectedlabel, $rolefilterlabel]);
        $table->data[] = new html_table_row([$selectfilter, $centerbuttons, $filters]);

        if (has_capability('block/quickmail:allowalternate', $context)) {
            $alternates = $this->_customdata['alternates'];
        } else {
            $alternates = [];
        }

        if (empty($alternates)) {
            $mform->addElement('static', 'from', quickmail::_s('from'), $USER->email);
        } else {
            $options = [0 => $USER->email] + $alternates;
            $mform->addElement('select', 'alternateid', quickmail::_s('from'), $options);
        }

        $mform->addElement('static', 'selectors', '', html_writer::table($table));

        if (!empty($CFG->block_quickmail_addionalemail)) {
            $mform->addElement('text', 'additional_emails', quickmail::_s('additional_emails'), ['style' => 'width: 50%;']);
            $mform->setType('additional_emails', PARAM_TEXT);
            $mform->addRule(
                'additional_emails',
                'One or more email addresses is invalid',
                'callback',
                'block_quickmail_mycallback',
                'client'
            );
            $mform->addHelpButton('additional_emails', 'additional_emails', 'block_quickmail');
        }
        $mform->addElement(
            'filemanager',
            'attachments',
            quickmail::_s('attachment'),
            null,
            ['subdirs' => 1, 'accepted_types' => '*']
        );

        $mform->addElement('text', 'subject', quickmail::_s('subject'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required');
        $mform->addElement(
            'editor',
            'message_editor',
            quickmail::_s('message'),
            $this->_customdata['attributes'],
            $this->_customdata['editor_options']
        );

        $options = $this->_customdata['sigs'] + [-1 => quickmail::_s('no') . ' ' . quickmail::_s('sig')];
        $mform->addElement('select', 'sigid', quickmail::_s('signature'), $options);

        $radio = [
            $mform->createElement('radio', 'receipt', '', get_string('yes'), 1),
            $mform->createElement('radio', 'receipt', '', get_string('no'), 0),
        ];

        $mform->addGroup($radio, 'receipt_action', quickmail::_s('receipt'), [' '], false);
        $mform->addHelpButton('receipt_action', 'receipt', 'block_quickmail');
        $mform->setDefault('receipt', !empty($config['receipt']));

        $buttons = [];
        $buttons[] =& $mform->createElement('submit', 'send', quickmail::_s('send_email'));
        $buttons[] =& $mform->createElement('submit', 'draft', quickmail::_s('save_draft'));
        $buttons[] =& $mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttons', quickmail::_s('actions'), [' '], false);
    }
}
