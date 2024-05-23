<?php
/**
 * Folder plugin version information
 *
 * @package
 * @subpackage
 * @copyright  block_quickmail
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
/**
 * function to migrate quickmail history files attachment to the new file version from 1.9 to 2.x
 */
function migrate_quickmail_20() {
    global $DB;
    // migration of attachments
    $fs = get_file_storage();
    $quickmaillogrecords = $DB->get_records_select('block_quickmail_log', 'attachment<>\'\'');
    foreach ($quickmaillogrecords as $quickmaillogrecord) {
        // searching file into mdl_files
        // analysing attachment content
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
                // rename
                $filename = str_replace('/', '_', $quickmaillogrecord->attachment);
                $filepath = '/';
                $quickmaillogrecord->attachment = $filename;
                $DB->update_record('block_quickmail_log', $quickmaillogrecord);
            }
            $filerecord = ['contextid' => $coursecontext->id, 'component' => 'block_quickmail', 'filearea' => 'attachment_log', 'itemid' => $quickmaillogrecord->id, 'filepath' => $filepath, 'filename' => $filename,
                    'timecreated' => $coursefile->get_timecreated(), 'timemodified' => $coursefile->get_timemodified()];
            if (!$fs->file_exists($coursecontext->id, 'block_quickmail', 'attachment_log', 0, $filepath, $filename)) {
                $fs->create_file_from_storedfile($filerecord, $coursefile->get_id());
            }
        }
    }
}
