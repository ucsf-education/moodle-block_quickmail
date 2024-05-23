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

class backup_quickmail_block_structure_step extends backup_block_structure_step {
    protected function define_structure() {
        global $DB;

        $params = ['courseid' => $this->get_courseid()];
        $context = context_course::instance($params['courseid']);
        // LOGS
        $quickmaillogs = $DB->get_records('block_quickmail_log', $params);
        $includehistory = $this->get_setting_value('include_quickmail_log');

        // QM BLOCK CONFIG BACKUP
        // attempt to create block settings step for quickmail, so people can restore their quickmail settings

        // WHY IS CONFIGS TABLE SET TO COURSES WITH AN S ID????????
        $paramstwo = ['coursesid' => $this->get_courseid()];
        $quickmailblocklevelsettings = $DB->get_records('block_quickmail_config', $paramstwo);
        $includeconfig = $this->get_setting_value('include_quickmail_config');

        // LOGS
        $backuplogsandsettings = new backup_nested_element('emaillogs', ['courseid'], null);

        $log = new backup_nested_element('log', ['id'], [
            'userid', 'courseid', 'alternateid', 'mailto', 'subject',
            'message', 'attachment', 'format', 'time', 'failuserids', 'additional_emails',
        ]);

        // courseid name value
        $quickmailsettings = new backup_nested_element('block_level_setting', ['id'], [
            'courseid', 'name', 'value',
        ]);


        $backuplogsandsettings->add_child($log);

        $backuplogsandsettings->add_child($quickmailsettings);

        $backuplogsandsettings->set_source_array([(object)$params]);

        if (!empty($quickmaillogs) and $includehistory) {
            $log->set_source_sql(
                'SELECT * FROM {block_quickmail_log}
                WHERE courseid = ?',
                [['sqlparam' => $this->get_courseid()]]
            );
        }

        if (!empty($quickmailblocklevelsettings) and $includeconfig) {
            $quickmailsettings->set_source_sql(
                'SELECT * FROM {block_quickmail_config}
                WHERE coursesid = ?',
                [['sqlparam' => $this->get_courseid()]]
            );
        }

        $log->annotate_ids('user', 'userid');
        // $quickmail_settings->annotate_ids('setting');

        $log->annotate_files('block_quickmail', 'log', 'id', $context->id);
        $log->annotate_files('block_quickmail', 'attachment_log', 'id', $context->id);
        // $quickmail_settings->annotate_files('block_quickmail', 'settings', 'courseid', $context->id);

        return $this->prepare_block_structure($backuplogsandsettings);
    }
}
