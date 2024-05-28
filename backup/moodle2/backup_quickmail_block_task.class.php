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
 * Defines the backup task for quickmail.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/quickmail/backup/moodle2/backup_quickmail_stepslib.php');

/**
 * Quickmail backup task class.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_quickmail_block_task extends backup_block_task {
    /**
     * Define the settings that the quickmail block can have.
     *
     * @return void
     * @throws base_plan_exception
     * @throws base_setting_exception
     * @throws base_setting_ui_exception
     * @throws coding_exception
     */
    protected function define_my_settings(): void {
        $includehistory = new backup_generic_setting('include_quickmail_log', base_setting::IS_BOOLEAN, false);
        $includehistory->get_ui()->set_label(get_string('backup_history', 'block_quickmail'));
        $this->add_setting($includehistory);

        $this->plan->get_setting('users')->add_dependency($includehistory);
        $this->plan->get_setting('blocks')->add_dependency($includehistory);

        $includeconfigsettings = new backup_generic_setting('include_quickmail_config', base_setting::IS_BOOLEAN, true);
        $includeconfigsettings->get_ui()->set_label(get_string('backup_block_configuration', 'block_quickmail'));
        $this->add_setting($includeconfigsettings);

        $this->plan->get_setting('blocks')->add_dependency($includeconfigsettings);
    }

    /**
     * Defines backup steps for the quickmail block.
     *
     * @return void
     * @throws base_task_exception
     */
    protected function define_my_steps() {
        // Todo: additional steps for drafts and alternate emails.
        $this->add_step(new backup_quickmail_block_structure_step('quickmail_structure', 'emaillogs_and_block_configuration.xml'));
    }

    /**
     * Defines the file areas that the quickmail block controls.
     *
     * @return array The file areas.
     */
    public function get_fileareas(): array {
        return [];
    }

    /**
     * Define the configdata attributes that need to be processed by the contenttransformer.
     * @return array The configdata attributes.
     *
     */
    public function get_configdata_encoded_attributes(): array {
        return [];
    }

    /**
     * Encodes links for backup in the given content, then returns the transformed content.
     * @param string $content The contents to back up.
     * @return string
     */
    public static function encode_content_links($content) {
        // Todo: perhaps needing this when moving away from email zip attaches.
        return $content;
    }
}
