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
 * Quickmail upgrade functions.
 *
 * @package block_quickmail
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author     Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */

/**
 * Function to migrate quickmail history files attachment to the new file version from 1.9 to 2.x.
 */
function migrate_quickmail_20(): void {
    global $DB;
    // Migration of attachments.
    $fs = get_file_storage();
    $quickmaillogrecords = $DB->get_records_select('block_quickmail_log', 'attachment<>\'\'');
    foreach ($quickmaillogrecords as $quickmaillogrecord) {
        // Searching file into mdl_files, analysing attachment content.
        $filename = $quickmaillogrecord->attachment;
        $filepath = '';
        $notrootfile = strstr($quickmaillogrecord->attachment, '/');
        if ($notrootfile) {
            $filename = substr($quickmaillogrecord->attachment, strrpos($quickmaillogrecord->attachment, '/', -1) + 1);
            $filepath = '/' . substr($quickmaillogrecord->attachment, 0, strrpos($quickmaillogrecord->attachment, '/', -1) + 1);
        } else {
            $filepath = '/';
            $filename = $quickmaillogrecord->attachment;
        }
        $fs = get_file_storage();
                $coursecontext = context_course::instance($quickmaillogrecord->courseid);

        $coursefile = $fs->get_file($coursecontext->id, 'course', 'legacy', 0, $filepath, $filename);
        if ($coursefile) {
            if ($notrootfile) {
                // Rename file.
                $filename = str_replace('/', '_', $quickmaillogrecord->attachment);
                $filepath = '/';
                $quickmaillogrecord->attachment = $filename;
                $DB->update_record('block_quickmail_log', $quickmaillogrecord);
            }
            $filerecord = [
                'contextid' => $coursecontext->id,
                'component' => 'block_quickmail',
                'filearea' => 'attachment_log',
                'itemid' => $quickmaillogrecord->id,
                'filepath' => $filepath,
                'filename' => $filename,
                'timecreated' => $coursefile->get_timecreated(),
                'timemodified' => $coursefile->get_timemodified(),
            ];
            if (!$fs->file_exists($coursecontext->id, 'block_quickmail', 'attachment_log', 0, $filepath, $filename)) {
                $fs->create_file_from_storedfile($filerecord, $coursefile->get_id());
            }
        }
    }
}
