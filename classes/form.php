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

class block_quickmail_form {

    /**
     * Instantiates and returns a compose message form
     * 
     * @param  object    $context
     * @param  object    $user           auth user
     * @param  object    $course         moodle course
     * @return compose_message_form
     */
    public static function make_compose_message_form($context, $user, $course)
    {
        // build target URL
        $target = '?' . http_build_query([
            'courseid' => $course->id,
        ], '', '&');

        // get the auth user's current signatures as array (id => title)
        $user_signature_array = block_quickmail\persistents\signature::get_flat_array_for_user($user->id);

        return new compose_message_form($target, [
            'context' => $context,
            'user' => $user,
            'course' => $course,
            'user_signature_array' => $user_signature_array,
            // 'draft' => '',
        ], 'post', '', ['id' => 'mform-compose']);
    }

    /**
     * Instantiates and returns a signature management form
     * 
     * @param  object        $context
     * @param  object        $user                   auth user
     * @param  persistent    $signature              optional, defaults to null
     * @param  int           $course_id              optional, course id
     * @return manage_signature_form
     */
    public static function make_manage_signatures_form($context, $user, $signature = null, $course_id = 0)
    {
        // build target URL
        $target = '?' . http_build_query([
            'id' => ! empty($signature) ? $signature->get('id') : 0,
            'courseid' => $course_id,
        ], '', '&');

        // attempt to fetch the course, return null if not valid
        try {
            $course = get_course($course_id);
        } catch (dml_exception $e) {
            $course = null;
        }

        // get the auth user's current signatures as array (id => title)
        $user_signature_array = block_quickmail\persistents\signature::get_flat_array_for_user($user->id);

        return new manage_signatures_form($target, [
            'context' => $context,
            'user' => $user,
            'signature' => $signature,
            'user_signature_array' => $user_signature_array,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-manage-signatures']);
    }

    public static function make_course_config_form($context, $user, $course)
    {
        // build target URL
        $target = '?' . http_build_query([
            'courseid' => $course->id,
        ], '', '&');

        return new course_config_form($target, [
            'context' => $context,
            'user' => $user,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-course-config']);
    }

}