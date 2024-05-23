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

require_once($CFG->dirroot . '/blocks/quickmail/lib.php');

class block_quickmail extends block_list {
    function init() {
        $this->title = quickmail::_s('pluginname');
    }

    function applicable_formats() {
        global $USER;
        if (is_siteadmin($USER->id) || has_capability('block/quickmail:myaddinstance', context_system::instance())) {
            return ['site' => true, 'my' => true, 'course-view' => true, 'mod-scorm-view' => true];
        } else {
            return ['site' => false, 'my' => false, 'course-view' => true, 'mod-scorm-view' => true];
        }
    }
    function has_config() {
        return true;
    }
    /**
     * Disable multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $USER, $CFG, $COURSE, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id);

        $config = quickmail::load_config($COURSE->id);
        $permission = has_capability('block/quickmail:cansend', $context);

        $cansend = ($permission or !empty($config['allowstudents']));

        $iconclass = ['class' => 'icon'];

        $cparam = ['courseid' => $COURSE->id];

        if ($cansend && $COURSE->id != SITEID) {
            $sendemailstr = quickmail::_s('composenew');
            $icon = $OUTPUT->pix_icon('t/email', $sendemailstr, 'moodle', $iconclass);
            $sendemail = html_writer::link(
                new moodle_url('/blocks/quickmail/email.php', $cparam),
                $icon . $sendemailstr
            );
            $this->content->items[] = $sendemail;

            $signaturestr = quickmail::_s('signature');
            $icon = $OUTPUT->pix_icon('i/edit', $signaturestr, 'moodle', $iconclass);
            $signature = html_writer::link(
                new moodle_url('/blocks/quickmail/signature.php', $cparam),
                $icon . $signaturestr
            );
            $this->content->items[] = $signature;

            $draftparams = $cparam + ['type' => 'drafts'];
            $draftsemailstr = quickmail::_s('drafts');
            $icon = $OUTPUT->pix_icon('i/settings', $draftsemailstr, 'moodle', $iconclass);
            $drafts = html_writer::link(
                new moodle_url('/blocks/quickmail/emaillog.php', $draftparams),
                $icon . $draftsemailstr
            );
            $this->content->items[] = $drafts;

            $historystr = quickmail::_s('history');
            $icon = $OUTPUT->pix_icon('i/settings', $historystr, 'moodle', $iconclass);
            $history = html_writer::link(
                new moodle_url('/blocks/quickmail/emaillog.php', $cparam),
                $icon . $historystr
            );
            $this->content->items[] = $history;

            if (has_capability('block/quickmail:allowalternate', $context)) {
                $altstr = quickmail::_s('alternate');
                $icon = $OUTPUT->pix_icon('i/edit', $altstr, 'moodle', $iconclass);
                $alt = html_writer::link(
                    new moodle_url('/blocks/quickmail/alternate.php', $cparam),
                    $icon . $altstr
                );

                $this->content->items[] = $alt;
            }

            if (has_capability('block/quickmail:canconfig', $context)) {
                $configstr = quickmail::_s('config');
                $icon = $OUTPUT->pix_icon('i/settings', $configstr, 'moodle', $iconclass);
                $config = html_writer::link(
                    new moodle_url('/blocks/quickmail/config_qm.php', $cparam),
                    $icon . $configstr
                );
                $this->content->items[] = $config;
            }
        }

        if ((has_capability('block/quickmail:myaddinstance', context_system::instance()) || is_siteadmin($USER->id)) && $COURSE->id == SITEID) {
            $sendadminemailstr = quickmail::_s('sendadmin');
            $icon = $OUTPUT->pix_icon('t/email', $sendadminemailstr, 'moodle', $iconclass);
            $sendadminemail = html_writer::link(
                new moodle_url('/blocks/quickmail/admin_email.php'),
                $icon . $sendadminemailstr
            );
            $this->content->items[] = $sendadminemail;
        }
        if (is_siteadmin($USER->id) && $COURSE->id == SITEID) {
            $historystr = quickmail::_s('history');
            $icon = $OUTPUT->pix_icon('i/settings', $historystr, 'moodle', $iconclass);
            $history = html_writer::link(
                new moodle_url('/blocks/quickmail/emaillog.php', $cparam),
                $icon . $historystr
            );
            $this->content->items[] = $history;
        }


        return $this->content;
    }
}
