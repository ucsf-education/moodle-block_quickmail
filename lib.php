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
use core_user\fields;

/**
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class quickmail {
    /**
     * @const string The page type for this block.
     */
    const PAGE_TYPE = 'block-quickmail';

    public static function _s($key, $a = null) {
        return get_string($key, 'block_quickmail', $a);
    }

    static function format_time($time) {
        return userdate($time, '%m-%d-%Y, %I:%M %p');
    }

    static function cleanup($table, $contextid, $itemid) {
        global $DB;

        // Clean up the files associated with this email
        // Fortunately, they are only db references, but
        // they shouldn't be there, nonetheless.
        $tablename = explode('_', $table);
        $filearea = end($tablename);

        $fs = get_file_storage();

        $fs->delete_area_files(
            $contextid,
            'block_quickmail',
            'attachment_' . $filearea,
            $itemid
        );

        $fs->delete_area_files(
            $contextid,
            'block_quickmail',
            $filearea,
            $itemid
        );

        return $DB->delete_records($table, ['id' => $itemid]);
    }

    static function history_cleanup($contextid, $itemid) {
        return self::cleanup('block_quickmail_log', $contextid, $itemid);
    }

    static function draft_cleanup($contextid, $itemid) {
        return self::cleanup('block_quickmail_drafts', $contextid, $itemid);
    }

    private static function flatten_subdirs($tree, $genlink, $level = 0) {
        $attachments = $spaces = '';
        foreach (range(0, $level) as $space) {
            $spaces .= " - ";
        }
        foreach ($tree['files'] as $filename => $file) {
            $attachments .= $spaces . " " . $genlink($filename) . "\n<br/>";
        }
        foreach ($tree['subdirs'] as $dirname => $subdir) {
            $attachments .= $spaces . " " . $dirname . "\n<br/>";
            $attachments .= self::flatten_subdirs($subdir, $genlink, $level + 2);
        }

        return $attachments;
    }

    static function process_attachments($context, $email, $table, $id) {
        $attachments = '';
        $filename = '';

        if (empty($email->attachment)) {
            return $attachments;
        }

        $fs = get_file_storage();

        $tree = $fs->get_area_tree(
            $context->id,
            'block_quickmail',
            'attachment_' . $table,
            $id,
            'id'
        );

        $baseurl = "/$context->id/block_quickmail/attachment_{$table}/$id";

        /**
         * @param string $filename name of the file for which we are generating a download link
         * @param string $text optional param sets the link text; if not given, filename is used
         * @param bool $plain if itrue, we will output a clean url for plain text email users
         *
         */
        $genlink = function ($filename, $text = '', $plain = false) use ($baseurl) {
            if (empty($text)) {
                $text = $filename;
            }

            $url = moodle_url::make_file_url('/pluginfile.php', "$baseurl/$filename", true);

            // to prevent double encoding of ampersands in urls for our plaintext users,
            // we use the out() method of moodle_url
            // @see http://phpdocs.moodle.org/HEAD/moodlecore/moodle_url.html
            if ($plain) {
                return $url->out(false);
            }

            return html_writer::link($url, $text);
        };



        $link = $genlink("{$email->time}_attachments.zip", self::_s('download_all'));

        // get a plain text version of the link
        // by calling gen_link with @param $plain set to true
        $tlink = $genlink("{$email->time}_attachments.zip", '', true);

        $attachments .= "\n<br/>-------\n<br/>";
        $attachments .= self::_s('moodle_attachments', $link);
        $attachments .= "\n<br/>" . $tlink;
        $attachments .= "\n<br/>-------\n<br/>";
        $attachments .= self::_s('qm_contents') . "\n<br />";

        return $attachments . self::flatten_subdirs($tree, $genlink);
    }

    static function zip_attachments($context, $table, $id) {
        global $CFG, $USER;

        $basepath = "block_quickmail/{$USER->id}";
        $moodlebase = "$CFG->tempdir/$basepath";

        if (!file_exists($moodlebase)) {
            mkdir($moodlebase, $CFG->directorypermissions, true);
        }

        $zipname = "attachment.zip";
        $actualzip = "$moodlebase/$zipname";

        $fs = get_file_storage();
        $packer = get_file_packer();

        $files = $fs->get_area_files(
            $context->id,
            'block_quickmail',
            'attachment_' . $table,
            $id,
            'id'
        );

        $storedfiles = [];
        foreach ($files as $file) {
            if ($file->is_directory() and $file->get_filename() == '.') {
                continue;
            }

            $storedfiles[$file->get_filepath() . $file->get_filename()] = $file;
        }

        $packer->archive_to_pathname($storedfiles, $actualzip);

        return $actualzip;
    }

    static function attachment_names($draft) {
        global $USER;

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draft, 'id');

        $onlyfiles = array_filter($files, function ($file) {
            return !$file->is_directory() and $file->get_filename() != '.';
        });

        $onlynames = function ($file) {
            return $file->get_filename();
        };

        $onlynamedfiles = array_map($onlynames, $onlyfiles);

        return implode(',', $onlynamedfiles);
    }

    static function filter_roles($userroles, $masterroles) {
        return array_uintersect($masterroles, $userroles, function ($a, $b) {
            return strcmp($a->shortname, $b->shortname);
        });
    }

    static function load_config($courseid) {
        global $DB;

        $fields = 'name,value';
        $params = ['coursesid' => $courseid];
        $table = 'block_quickmail_config';

        $config = $DB->get_records_menu($table, $params, '', $fields);

        if (empty($config)) {
            $m = 'moodle';
            $allowstudents = get_config($m, 'block_quickmail_allowstudents');
            $roleselection = get_config($m, 'block_quickmail_roleselection');
            $prepender = get_config($m, 'block_quickmail_prepend_class');
            $receipt = get_config($m, 'block_quickmail_receipt');
            $ferpa = get_config($m, 'block_quickmail_ferpa');

            // Convert Never (-1) to No (0) in case site config is changed.
            if ($allowstudents == -1) {
                $allowstudents = 0;
            }

            $config = [
                'allowstudents' => $allowstudents,
                'roleselection' => $roleselection,
                'prepend_class' => $prepender,
                'receipt' => $receipt,
                'ferpa' => $ferpa,
            ];
        } else {
             // See if allow students is disabled at the site level.
             $allowstudents = get_config('moodle', 'block_quickmail_allowstudents');
            if ($allowstudents == -1) {
                $config['allowstudents'] = 0;
            }
                 $config['ferpa'] = get_config('moodle', 'block_quickmail_ferpa');
        }

        return $config;
    }

    static function default_config($courseid) {
        global $DB;

        $params = ['coursesid' => $courseid];
        $DB->delete_records('block_quickmail_config', $params);
    }

    static function save_config($courseid, $data) {
        global $DB;

        self::default_config($courseid);

        foreach ($data as $name => $value) {
            $config = new stdClass();
            $config->coursesid = $courseid;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_quickmail_config', $config);
        }
    }

    static function delete_dialog($courseid, $type, $typeid) {
        global $CFG, $DB, $USER, $OUTPUT;

        $email = $DB->get_record('block_quickmail_' . $type, ['id' => $typeid]);

        if (empty($email)) {
            print_error('not_valid_typeid', 'block_quickmail', '', $typeid);
        }

        $params = ['courseid' => $courseid, 'type' => $type];
        $yesparams = $params + ['typeid' => $typeid, 'action' => 'confirm'];

        $optionyes = new moodle_url('/blocks/quickmail/emaillog.php', $yesparams);
        $optionno = new moodle_url('/blocks/quickmail/emaillog.php', $params);

        $table = new html_table();
        $table->head = [get_string('date'), self::_s('subject')];
        if ($courseid == 1) {
            $table->data = [
            new html_table_row([
            new html_table_cell(self::format_time($email->time)),
            new html_table_cell($email->subject)]),
            ];
        } else {
            $table->data = [
            new html_table_row([
                new html_table_cell(self::format_time($email->time)),
                new html_table_cell($email->subject)]),
            ];
        }
        $msg = self::_s('delete_confirm', html_writer::table($table));

        $html = $OUTPUT->confirm($msg, $optionyes, $optionno);
        return $html;
    }

    static function list_entries($courseid, $type, $page, $perpage, $userid, $count, $candelete) {
        global $CFG, $DB, $OUTPUT;

        $dbtable = 'block_quickmail_' . $type;

        $table = new html_table();

        $params = ['courseid' => $courseid, 'userid' => $userid];
        $logs = $DB->get_records(
            $dbtable,
            $params,
            'time DESC',
            '*',
            $page * $perpage,
            $perpage
        );
        if ($courseid == '1') {
            $table->head = [get_string('date'), self::_s('subject'), get_string('action'), self::_s('status'), self::_s('failed_to_send_to'), self::_s('send_again')];
        } else {
            $table->head = [get_string('date'), self::_s('subject'),
                self::_s('attachment'), get_string('action'), self::_s('status'), self::_s('failed_to_send_to'), self::_s('send_again')];
        }

        $table->data = [];
        foreach ($logs as $log) {
            $arrayoffaileduserids = [];
            $date = self::format_time($log->time);
            $subject = $log->subject;
            $attachments = $log->attachment;
            if (! empty($log->failuserids)) {
            // DWE -> keep track of user ids that failed.
                $arrayoffaileduserids = explode(",", $log->failuserids);
            }
            $params = [
                'courseid' => $log->courseid,
                'type' => $type,
                'typeid' => $log->id,
            ];

            $actions = [];
            if ($courseid == '1') {
                $openlink = html_writer::link(
                    new moodle_url('/blocks/quickmail/admin_email.php', $params),
                    $OUTPUT->pix_icon('i/search', 'Open Email')
                );
            } else {
                $openlink = html_writer::link(
                    new moodle_url('/blocks/quickmail/email.php', $params),
                    $OUTPUT->pix_icon('i/search', 'Open Email')
                );
            }
            $actions[] = $openlink;

            if ($candelete) {
                $deleteparams = $params + [
                    'userid' => $userid,
                    'action' => 'delete',
                ];

                $deletelink = html_writer::link(
                    new moodle_url('/blocks/quickmail/emaillog.php', $deleteparams),
                    $OUTPUT->pix_icon("i/invalid", "Delete Email")
                );

                $actions[] = $deletelink;
            }

            $actionlinks = implode(' ', $actions);

            $statussentornot = self::_s($type . "success");

            if (! empty($arrayoffaileduserids)) {
                $statussentornot = self::_s('message_failure');
                $params += [
                    'fmid' => 1,
                ];
                $text = self::_s('send_again');

                if ($courseid == '1') {
                    $sendagain = html_writer::link(new moodle_url("/blocks/quickmail/admin_email.php", $params), $text);
                } else {
                    $sendagain = html_writer::link(new moodle_url("/blocks/quickmail/email.php", $params), $text);
                }
                $listfailids = count($arrayoffaileduserids);

                $failcount = (($listfailids === 1) ? $listfailids . " " . self::_s("user") : $listfailids . " " . self::_s("users"));
            } else {
                $listfailids = $arrayoffaileduserids;
                $sendagain = "";
                $failcount = "";
            }


            if ($courseid == 1) {
                $table->data[] = [$date, $subject, $actionlinks, $statussentornot, $failcount, $sendagain];
            } else {
                 $table->data[] = [$date, $subject, $attachments, $actionlinks, $statussentornot, $failcount, $sendagain];
            }
        }

        $paging = $OUTPUT->paging_bar(
            $count,
            $page,
            $perpage,
            '/blocks/quickmail/emaillog.php?type=' . $type . '&amp;courseid=' . $courseid . '&userid=' . $userid
        );

        $html = $paging;
        $html .= html_writer::table($table);
        $html .= $paging;
        return $html;
    }

    /**
     * get all users for a given context
     * @param $context a moodle context id
     * @return array of sparse user objects
     */
    public static function get_all_users($context) {
        global $DB, $CFG;
        // List everyone with role in course.
        //
        // Note that users with multiple roles will be squashed into one
        // record.
        $getnamestring = 'u.firstname, u.lastname';

        if ($CFG->version >= 2013111800) {
               $getnamestring = block_quickmail_get_all_user_name_fields('u');
        }
        $sql = "SELECT DISTINCT u.id, " . $getnamestring . ",
        u.email, up.value, u.mailformat, u.suspended, u.maildisplay
        FROM {role_assignments} ra
        JOIN {user} u ON u.id = ra.userid
        LEFT JOIN {user_preferences} up on u.id = up.userid AND up.name = 'message_processor_email_email'
        JOIN {role} r ON ra.roleid = r.id
        WHERE (ra.contextid = ? ) ";

        $everyone = $DB->get_records_sql($sql, [$context->id]);

        return $everyone;
    }


    /**
     * @TODO this function relies on self::get_all_users, it should not have to
     *
     * returns all users enrolled in a gived coure EXCEPT for those whose
     * mdl_user_enrolments.status field is 1 (suspended)
     * @param $context  moodle context id
     * @param $courseid the course id
     */
    public static function get_non_suspended_users($context, $courseid) {
        global $DB, $CFG;
        $everyone = self::get_all_users($context);

        $getnamestring = 'u.firstname, u.lastname';

        if ($CFG->version >= 2013111800) {
               $getnamestring = block_quickmail_get_all_user_name_fields('u');
        }

        $sql = "SELECT u.id, " . $getnamestring . " , u.email, up.value, u.username, u.mailformat, u.suspended, u.maildisplay, ue.status  
            FROM {user} u  
                JOIN {user_enrolments} ue                 
                    ON u.id = ue.userid
                LEFT JOIN {user_preferences} up 
                    ON u.id = up.userid 
                    AND up.name = 'message_processor_email_email' 
                JOIN {enrol} en
                    ON en.id = ue.enrolid                     
                WHERE en.courseid = ?
                    AND ue.status = ?
                ORDER BY u.lastname, u.firstname";

        // let's use a recordset in case the enrollment is huge
        $rsvalids = $DB->get_recordset_sql($sql, [$courseid, 0]);

        // container for user_enrolments records
        $valids = [];

        /**
         * @TODO use a cleaner mechanism from std lib to do this without iterating over the array
         * for each chunk of the recordset,
         * insert the record into the valids container
         * using the id number as the array key;
         * this matches the format used by self::get_all_users
         */
        foreach ($rsvalids as $rsv) {
            $valids[$rsv->id] = $rsv;
        }
        // required to close the recordset
        $rsvalids->close();

        // get the intersection of self::all_users and this potentially shorter list
        $evryonenotsuspended = array_intersect_key($valids, $everyone);

        return $evryonenotsuspended;
    }

    public static function clean($failuserids) {
        $additionalemails = [];
        $failuserids = explode(',', $failuserids);

        foreach ($failuserids as $id => $failedaddressorid) {
            if (! is_numeric($failedaddressorid)) {
                $additionalemails[] = $failedaddressorid;


                unset($failuserids[$id]);
            }
        }

        $additionalemails = implode(',', $additionalemails);
        $mailto            = implode(',', $failuserids);

        return [$mailto, $additionalemails];
    }
}

function block_quickmail_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
    $fs = get_file_storage();
    global $DB, $CFG;

    if (!empty($CFG->block_quickmail_downloads) && $filearea != 'log') {
        require_course_login($course, true, $record);
    }

    [$itemid, $filename] = $args;

    if ($filearea == 'attachment_log') {
        $time = $DB->get_field('block_quickmail_log', 'time', [
            'id' => $itemid,
        ]);

        if ("{$time}_attachments.zip" == $filename) {
            $path = quickmail::zip_attachments($context, 'log', $itemid);
            send_temp_file($path, 'attachments.zip');
        }
    }

    $params = [
        'component' => 'block_quickmail',
        'filearea' => $filearea,
        'itemid' => $itemid,
        'filename' => $filename,
    ];

    $instanceid = $DB->get_field('files', 'id', $params);

    if (empty($instanceid)) {
        send_file_not_found();
    } else {
        $file = $fs->get_file_by_id($instanceid);
        send_stored_file($file);
    }
}

/**
 * Drop-in replacement for the deprecated <code>get_all_user_name_fields()</code> method.
 * @link https://github.com/moodle/moodle/blob/MOODLE_401_STABLE/lib/deprecatedlib.php#L3279
 * @param string|null $prefix
 * @return string
 */
function block_quickmail_get_all_user_name_fields(?string $tableprefix = null): string {
    // This array is provided in this order because when called by fullname() (above) if firstname is before
    // firstnamephonetic str_replace() will change the wrong placeholder.
    $alternatenames = [];
    foreach (fields::get_name_fields() as $field) {
        $alternatenames[$field] = $field;
    }

    if ($tableprefix) {
        foreach ($alternatenames as $key => $altname) {
            $alternatenames[$key] = $tableprefix . '.' . $altname;
        }
    }
    return implode(',', $alternatenames);
}
