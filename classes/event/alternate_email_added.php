<?php
// This file is part of UCLA Control panel block for Moodle - http://moodle.org/
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
 * Alternate 'added' logging event handler.
 *
 * @package    block_quickmail
 * @copyright  2014 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\event;

use coding_exception;
use core\event\base;
use moodle_exception;
use moodle_url;

/**
 * Email added event handler class.
 *
 * @package    block_quickmail
 * @copyright  2014 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class alternate_email_added extends base {
    /**
     * {@inheritdoc}
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns name of the event.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('eventalternateemailadded', 'block_quickmail');
    }

    /**
     * Returns info on when a user with ID has viewed a control panel module (tab).
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' has added an alternate email  "
            . "{$this->other['address']}.";
    }

    /**
     * Returns URL of the event.
     *
     * @return moodle_url
     * @throws moodle_exception
     */
    public function get_url(): moodle_url {
        return new moodle_url('/blocks/quickmail/alternate.php', [
                    'courseid' => $this->courseid,
                ]);
    }

    /**
     * Returns legacy log data.
     *
     * @return array
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_legacy_logdata(): array {
        return [
            $this->courseid,
            'quickmail',
            'add',
            $this->get_url(),
            get_string('alternate', 'block_quickmail') . ' ' . $this->other['address'],
        ];
    }
}
