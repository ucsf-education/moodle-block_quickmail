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
 * Quickmail configuration form.
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The quickmail configuration form class.
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_form extends moodleform {
    /**
     * The form definition.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected function definition(): void {
        $mform =& $this->_form;

        $resetlink = html_writer::link(
            new moodle_url('/blocks/quickmail/config_qm.php', [
                'courseid' => $this->_customdata['courseid'],
                'reset' => 1,
            ]),
            quickmail::_s('reset')
        );
        $mform->addElement('static', 'reset', '', $resetlink);

        $studentselect = [0 => get_string('no'), 1 => get_string('yes')];

        $allowstudents = get_config('moodle', 'block_quickmail_allowstudents');
        if ($allowstudents != -1) {
            // If we disallow "Allow students to use Quickmail" at the site
            // level, then disallow the config to be set at the course level.
            $mform->addElement(
                'select',
                'allowstudents',
                quickmail::_s('allowstudents'),
                $studentselect
            );
        }

        $roles =& $mform->addElement(
            'select',
            'roleselection',
            quickmail::_s('select_roles'),
            $this->_customdata['roles']
        );

        $roles->setMultiple(true);

        $options = [
            0 => get_string('none'),
            'idnumber' => get_string('idnumber'),
            'shortname' => get_string('shortname'),
        ];

        $mform->addElement(
            'select',
            'prepend_class',
            quickmail::_s('prepend_class'),
            $options
        );

        $mform->addElement(
            'select',
            'receipt',
            quickmail::_s('receipt'),
            $studentselect
        );

        $mform->addElement('submit', 'save', get_string('savechanges'));

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addRule('roleselection', null, 'required');
    }
}
