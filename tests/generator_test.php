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
 * Test coverage for Quickmail block data generator.
 *
 * @package    block_quickmail
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail;

use advanced_testcase;

/**
 * Quickmail block data generator testcase.
 *
 * @package    block_quickmail
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \block_quickmail_generator
 */
final class generator_test extends advanced_testcase {
    /**
     * Tests quickmail log record generator.
     */
    public function test_create_quickmail_log(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $generator->get_plugin_generator('block_quickmail');

        $this->assertEquals(0, $DB->count_records('block_quickmail_log'));

        $user = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $course = $generator->create_course();

        // Create quickmail log record with required properties only.
        $log = $plugingenerator->create_quickmail_log($user, $course, []);

        // Confirm that record count in the DB ticked up.
        $this->assertEquals(1, $DB->count_records('block_quickmail_log'));

        // Check log record props.
        $this->assertEquals($course->id, $log->courseid);
        $this->assertEquals($user->id, $log->userid);
        $this->assertNull($log->alternateid);
        $this->assertEquals('', $log->mailto);
        $this->assertEquals('test subject', $log->subject);
        $this->assertEquals('test message', $log->message);
        $this->assertEquals('', $log->attachment);
        $this->assertEquals(0, $log->format);
        $this->assertLessThanOrEqual(time(), $log->time);
        $this->assertGreaterThan(0, $log->time);
        $this->assertEquals('', $log->failuserids);
        $this->assertEquals('', $log->additional_emails);

        // Create another log record with overridden defaults.
        $alternate = $plugingenerator->create_quickmail_alternate($course);
        $timesent = time() + 10000;

        $log = $plugingenerator->create_quickmail_log(
            $user,
            $course,
            [$user2, $user3],
            $alternate,
            'lorem',
            'ipsum',
            'test.png',
            2,
            $timesent,
            '1,2,3',
            'foo@bar.com',
        );
        $this->assertEquals(2, $DB->count_records('block_quickmail_log'));

        $this->assertEquals($course->id, $log->courseid);
        $this->assertEquals($user->id, $log->userid);
        $this->assertEquals($alternate->id, $log->alternateid);
        $this->assertEquals("$user2->id,$user3->id", $log->mailto);
        $this->assertEquals('lorem', $log->subject);
        $this->assertEquals('ipsum', $log->message);
        $this->assertEquals('test.png', $log->attachment);
        $this->assertEquals(2, $log->format);
        $this->assertEquals($timesent, $log->time);
        $this->assertEquals('1,2,3', $log->failuserids);
        $this->assertEquals('foo@bar.com', $log->additional_emails);
    }

    /**
     * Tests quickmail draft record generator.
     */
    public function test_create_quickmail_draft(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $generator->get_plugin_generator('block_quickmail');

        $this->assertEquals(0, $DB->count_records('block_quickmail_drafts'));

        $user = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $course = $generator->create_course();

        // Create quickmail draft record with required properties only.
        $draft = $plugingenerator->create_quickmail_draft($user, $course, []);

        // Confirm that record count in the DB ticked up.
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts'));

        // Check draft record props.
        $this->assertEquals($course->id, $draft->courseid);
        $this->assertEquals($user->id, $draft->userid);
        $this->assertNull($draft->alternateid);
        $this->assertEquals('', $draft->mailto);
        $this->assertEquals('test subject', $draft->subject);
        $this->assertEquals('test message', $draft->message);
        $this->assertEquals('', $draft->attachment);
        $this->assertEquals(0, $draft->format);
        $this->assertLessThanOrEqual(time(), $draft->time);
        $this->assertGreaterThan(0, $draft->time);
        $this->assertEquals('', $draft->additional_emails);

        // Create another draft record with overridden defaults.
        $alternate = $plugingenerator->create_quickmail_alternate($course);
        $timecreated = time() + 10000;

        $draft = $plugingenerator->create_quickmail_draft(
            $user,
            $course,
            [$user2, $user3],
            $alternate,
            'lorem',
            'ipsum',
            'test.png',
            2,
            $timecreated,
            'foo@bar.com',
        );
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts'));

        $this->assertEquals($course->id, $draft->courseid);
        $this->assertEquals($user->id, $draft->userid);
        $this->assertEquals($alternate->id, $draft->alternateid);
        $this->assertEquals("$user2->id,$user3->id", $draft->mailto);
        $this->assertEquals('lorem', $draft->subject);
        $this->assertEquals('ipsum', $draft->message);
        $this->assertEquals('test.png', $draft->attachment);
        $this->assertEquals(2, $draft->format);
        $this->assertEquals($timecreated, $draft->time);
        $this->assertEquals('foo@bar.com', $draft->additional_emails);
    }

    /**
     * Tests quickmail alternate email record generator.
     */
    public function test_create_quickmail_alternate(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $generator->get_plugin_generator('block_quickmail');

        $this->assertEquals(0, $DB->count_records('block_quickmail_alternate'));

        $course = $generator->create_course();

        // Create quickmail alternate email record with required properties only.
        $alternate = $plugingenerator->create_quickmail_alternate($course);

        // Confirm that record count in the DB ticked up.
        $this->assertEquals(1, $DB->count_records('block_quickmail_alternate'));

        // Check log record props.
        $this->assertEquals($course->id, $alternate->courseid);
        $this->assertEquals("course$course->id@example.edu", $alternate->address);
        $this->assertEquals(1, $alternate->valid);

        // Create another alt email record with overridden defaults.
        $alternate = $plugingenerator->create_quickmail_alternate($course, 'someemail@example.edu', 0);
        $this->assertEquals($course->id, $alternate->courseid);
        $this->assertEquals("someemail@example.edu", $alternate->address);
        $this->assertEquals(0, $alternate->valid);
    }

    /**
     * Tests quickmail email signature record generator.
     */
    public function test_create_quickmail_signature(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $generator->get_plugin_generator('block_quickmail');

        $this->assertEquals(0, $DB->count_records('block_quickmail_signatures'));

        $user = $generator->create_user();

        // Create quickmail email signature with required properties only.
        $signature = $plugingenerator->create_quickmail_signature($user);

        // Confirm that record count in the DB ticked up.
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures'));

        // Check signature record props.
        $this->assertEquals($user->id, $signature->userid);
        $this->assertEquals("email signature for user $user->id", $signature->title);
        $this->assertEquals("$user->id signature", $signature->signature);
        $this->assertEquals(0, $signature->default_flag);

        // Create another signature record with overridden defaults.
        $signature = $plugingenerator->create_quickmail_signature($user, 'foo', 'bar', 1);
        $this->assertEquals($user->id, $signature->userid);
        $this->assertEquals('foo', $signature->title);
        $this->assertEquals('bar', $signature->signature);
        $this->assertEquals(1, $signature->default_flag);
    }
}
