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
 * Privacy provider tests for block_quickmail.
 *
 * @package    block_quickmail
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_quickmail\privacy;

use context_system;
use context_user;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use dml_exception;

/**
 * Privacy provider test case.
 *
 * @package    block_quickmail
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_quickmail\privacy\provider
 */
final class provider_test extends provider_testcase {
    /**
     * @var string This component's name.
     */
    public const COMPONENT_NAME = 'block_quickmail';

    /**
     * Test getting the context for the user ID related to this plugin.
     * @throws dml_exception
     */
    public function test_get_contexts_for_userid(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator(self::COMPONENT_NAME);

        // Create four users and a course.
        $user1 = $generator->create_user();
        $usercontext1 = context_user::instance($user1->id);
        $user2 = $generator->create_user();
        $usercontext2 = context_user::instance($user2->id);
        $user3 = $generator->create_user();
        $usercontext3 = context_user::instance($user3->id);
        $user4 = $generator->create_user();
        $course = $generator->create_course();

        // No contexts returned before user-specific block configurations are applied.
        $contextlist1 = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(0, $contextlist1);
        $contextlist2 = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(0, $contextlist2);

        // Create some quickmail data points for all users but user4.
        $plugingenerator->create_quickmail_log($user1, $course, [$user2]);
        $plugingenerator->create_quickmail_log($user2, $course, [$user3]);
        $plugingenerator->create_quickmail_draft($user1, $course, [$user2]);
        $plugingenerator->create_quickmail_draft($user3, $course, [$user4]);
        $plugingenerator->create_quickmail_signature($user1);

        // Ensure provider only fetches the user's own context.
        $contextlist1 = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(1, $contextlist1);
        $this->assertEquals($usercontext1, $contextlist1->current());
        $contextlist2 = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(1, $contextlist2);
        $this->assertEquals($usercontext2, $contextlist2->current());
        $contextlist3 = provider::get_contexts_for_userid($user3->id);
        $this->assertCount(1, $contextlist3);
        $this->assertEquals($usercontext3, $contextlist3->current());
        $contextlist4 = provider::get_contexts_for_userid($user4->id);
        $this->assertCount(0, $contextlist4);
    }

    /**
     * Test getting users in the context ID related to this plugin.
     */
    public function test_get_users_in_context(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator(self::COMPONENT_NAME);

        // Create four users and a course.
        $user1 = $generator->create_user();
        $usercontext1 = context_user::instance($user1->id);
        $user2 = $generator->create_user();
        $usercontext2 = context_user::instance($user2->id);
        $user3 = $generator->create_user();
        $usercontext3 = context_user::instance($user3->id);
        $user4 = $generator->create_user();
        $usercontext4 = context_user::instance($user4->id);
        $course = $generator->create_course();

        // No users in given user-contexts before user-related data has been added.
        $userlist1 = new userlist($usercontext1, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);
        $userlist2 = new userlist($usercontext2, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist2);
        $this->assertCount(0, $userlist2);
        $userlist3 = new userlist($usercontext3, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist3);
        $this->assertCount(0, $userlist3);
        $userlist4 = new userlist($usercontext4, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist4);
        $this->assertCount(0, $userlist4);

        // Create some quickmail data points for all users but user4.
        $plugingenerator->create_quickmail_log($user1, $course, [$user2]);
        $plugingenerator->create_quickmail_log($user2, $course, [$user3]);
        $plugingenerator->create_quickmail_draft($user1, $course, [$user2]);
        $plugingenerator->create_quickmail_draft($user3, $course, [$user4]);
        $plugingenerator->create_quickmail_signature($user1);

        // Ensure provider only fetches the user whose user context is checked.
        $userlist1 = new userlist($usercontext1, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist1);
        $this->assertCount(1, $userlist1);
        $this->assertEquals($user1, $userlist1->current());

        $userlist2 = new userlist($usercontext2, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
        $this->assertEquals($user2, $userlist2->current());

        $userlist3 = new userlist($usercontext3, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist3);
        $this->assertCount(1, $userlist3);
        $this->assertEquals($user3, $userlist3->current());

        // User4 has no quickmail data associated with it, so the users list will come back empty.
        $userlist4 = new userlist($usercontext4, self::COMPONENT_NAME);
        provider::get_users_in_context($userlist4);
        $this->assertCount(0, $userlist4);
    }

    /**
     * Test fetching information about user data stored.
     */
    public function test_get_metadata(): void {
        $collection = new collection(self::COMPONENT_NAME);
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(3, $itemcollection);

        $table = reset($itemcollection);
        $this->assertEquals('block_quickmail_log', $table->get_name());
        $privacyfields = $table->get_privacy_fields();
        $this->assertCount(12, $privacyfields);
        $this->assertArrayHasKey('id', $privacyfields);
        $this->assertArrayHasKey('courseid', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('alternateid', $privacyfields);
        $this->assertArrayHasKey('mailto', $privacyfields);
        $this->assertArrayHasKey('subject', $privacyfields);
        $this->assertArrayHasKey('message', $privacyfields);
        $this->assertArrayHasKey('attachment', $privacyfields);
        $this->assertArrayHasKey('format', $privacyfields);
        $this->assertArrayHasKey('time', $privacyfields);
        $this->assertArrayHasKey('failuserids', $privacyfields);
        $this->assertArrayHasKey('additional_emails', $privacyfields);
        $this->assertEquals('privacy:metadata:block_quickmail_log', $table->get_summary());

        $table = next($itemcollection);
        $this->assertEquals('block_quickmail_drafts', $table->get_name());
        $privacyfields = $table->get_privacy_fields();
        $this->assertCount(11, $privacyfields);
        $this->assertArrayHasKey('id', $privacyfields);
        $this->assertArrayHasKey('courseid', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('alternateid', $privacyfields);
        $this->assertArrayHasKey('mailto', $privacyfields);
        $this->assertArrayHasKey('subject', $privacyfields);
        $this->assertArrayHasKey('message', $privacyfields);
        $this->assertArrayHasKey('attachment', $privacyfields);
        $this->assertArrayHasKey('format', $privacyfields);
        $this->assertArrayHasKey('time', $privacyfields);
        $this->assertArrayHasKey('additional_emails', $privacyfields);
        $this->assertEquals('privacy:metadata:block_quickmail_drafts', $table->get_summary());

        $table = next($itemcollection);
        $this->assertEquals('block_quickmail_signatures', $table->get_name());
        $privacyfields = $table->get_privacy_fields();
        $this->assertCount(5, $privacyfields);
        $this->assertArrayHasKey('id', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('title', $privacyfields);
        $this->assertArrayHasKey('signature', $privacyfields);
        $this->assertArrayHasKey('default_flag', $privacyfields);
        $this->assertEquals('privacy:metadata:block_quickmail_signatures', $table->get_summary());
    }

    /**
     * Test exporting data for an approved contextlist.
     */
    public function test_export_user_data(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator(self::COMPONENT_NAME);

        $user = $generator->create_user();
        $usercontext = context_user::instance($user->id);
        $course = $generator->create_user();

        // Create data for user.
        $plugingenerator->create_quickmail_log($user, $course);
        $plugingenerator->create_quickmail_log($user, $course);
        $plugingenerator->create_quickmail_draft($user, $course);
        $plugingenerator->create_quickmail_draft($user, $course);
        $plugingenerator->create_quickmail_signature($user);
        $plugingenerator->create_quickmail_signature($user);

        // Confirm data is present.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', ['userid' => $user->id, 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', ['userid' => $user->id, 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures', ['userid' => $user->id]));

        // Export data for user.
        $approvedlist = new approved_contextlist($user, self::COMPONENT_NAME, [$usercontext->id]);
        provider::export_user_data($approvedlist);

        // Confirm user's data is exported.
        $subcontext = get_string('pluginname', self::COMPONENT_NAME);
        $writer = writer::with_context($usercontext);
        $this->assertTrue($writer->has_any_data([$subcontext]));
        $data = (array) $writer->get_data([$subcontext]);
        $this->assertCount(2, $data['signatures']);
        foreach (array_values($data['signatures']) as $signature) {
            $this->assertEquals($user->id, $signature->userid);
        }
        $this->assertCount(2, $data['log']);
        foreach (array_values($data['log']) as $log) {
            $this->assertEquals($user->id, $log->userid);
            $this->assertEquals($course->id, $log->courseid);
        }
        $this->assertCount(2, $data['drafts']);
        foreach (array_values($data['drafts']) as $draft) {
            $this->assertEquals($user->id, $draft->userid);
            $this->assertEquals($course->id, $draft->courseid);
        }
    }

    /**
     * Test deleting data for all users within an approved contextlist.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator(self::COMPONENT_NAME);

        // Create two users and a course.
        $user1 = $generator->create_user();
        $usercontext1 = context_user::instance($user1->id);
        $user2 = $generator->create_user();
        $usercontext2 = context_user::instance($user2->id);
        $course = $generator->create_course();

        // Add some data for both users.
        $plugingenerator->create_quickmail_log($user1, $course);
        $plugingenerator->create_quickmail_log($user2, $course);
        $plugingenerator->create_quickmail_draft($user1, $course);
        $plugingenerator->create_quickmail_draft($user2, $course);
        $plugingenerator->create_quickmail_signature($user1);
        $plugingenerator->create_quickmail_signature($user2);

        // Check that the DB got populated correctly.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user2->id ]));

        // Attempt system context deletion (should have no effect).
        $systemcontext = context_system::instance();
        provider::delete_data_for_all_users_in_context($systemcontext);

        // Confirm that user data is still there.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));

        // Delete all data in user1 user context.
        provider::delete_data_for_all_users_in_context($usercontext1);

        // Confirm that only user1's block data got deleted.
        $this->assertEquals(0, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(0, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(0, $DB->count_records('block_quickmail_signatures', ['userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user2->id ]));
    }

    /**
     * Test deleting data within an approved contextlist for a user.
     */
    public function test_delete_data_for_user(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator(self::COMPONENT_NAME);

        // Create two users and a course.
        $user1 = $generator->create_user();
        $usercontext1 = context_user::instance($user1->id);
        $user2 = $generator->create_user();
        $usercontext2 = context_user::instance($user2->id);
        $course = $generator->create_course();

        // Add some data for both users.
        $plugingenerator->create_quickmail_log($user1, $course);
        $plugingenerator->create_quickmail_log($user2, $course);
        $plugingenerator->create_quickmail_draft($user1, $course);
        $plugingenerator->create_quickmail_draft($user2, $course);
        $plugingenerator->create_quickmail_signature($user1);
        $plugingenerator->create_quickmail_signature($user2);

        // Check that the DB got populated correctly.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user2->id ]));

        // Attempt system context deletion (should have no effect).
        $systemcontext = context_system::instance();
        $approvedlist = new approved_contextlist($user1, self::COMPONENT_NAME, [$systemcontext->id]);
        provider::delete_data_for_user($approvedlist);

        // Confirm that user data is still there.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));

        // Attempt to delete user1 data in user2 user context (should have no effect).
        $approvedlist = new approved_contextlist($user2, self::COMPONENT_NAME, [$usercontext1->id]);
        provider::delete_data_for_user($approvedlist);

        // Confirm that user data is still there.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));

        // Delete all data in user1 user context.
        $approvedlist = new approved_contextlist($user1, self::COMPONENT_NAME, [$usercontext1->id]);
        provider::delete_data_for_user($approvedlist);

        // Confirm that only user1's block data got deleted.
        $this->assertEquals(0, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(0, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(0, $DB->count_records('block_quickmail_signatures', ['userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user2->id ]));
    }

    /**
     * Test deleting data within a context for an approved userlist.
     */
    public function test_delete_data_for_users(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator(self::COMPONENT_NAME);

        // Create two users and a course.
        $user1 = $generator->create_user();
        $usercontext1 = context_user::instance($user1->id);
        $user2 = $generator->create_user();
        $course = $generator->create_course();

        // Add some data for both users.
        $plugingenerator->create_quickmail_log($user1, $course);
        $plugingenerator->create_quickmail_log($user2, $course);
        $plugingenerator->create_quickmail_draft($user1, $course);
        $plugingenerator->create_quickmail_draft($user2, $course);
        $plugingenerator->create_quickmail_signature($user1);
        $plugingenerator->create_quickmail_signature($user2);

        // Check that the DB got populated correctly.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user2->id ]));

        // Attempt system context deletion (should have no effect).
        $systemcontext = context_system::instance();
        $approvedlist = new approved_userlist($systemcontext, self::COMPONENT_NAME, [$user1->id, $user2->id]);
        provider::delete_data_for_users($approvedlist);

        // Confirm that user data is still there.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));

        // Attempt to delete data in another user's context (should have no effect).
        $approvedlist = new approved_userlist($usercontext1, self::COMPONENT_NAME, [$user2->id]);
        provider::delete_data_for_users($approvedlist);

        // Confirm that user data is still there.
        $this->assertEquals(2, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(2, $DB->count_records('block_quickmail_signatures'));

        // Delete data for user1 and user2 in the user context for user1.
        $approvedlist = new approved_userlist($usercontext1, self::COMPONENT_NAME, [$user1->id, $user2->id]);
        provider::delete_data_for_users($approvedlist);

        // Confirm only user1's data is deleted.
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures'));
        $this->assertEquals(0, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(0, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user1->id ]));
        $this->assertEquals(0, $DB->count_records('block_quickmail_signatures', ['userid' => $user1->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_log', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_drafts', [ 'courseid' => $course->id, 'userid' => $user2->id ]));
        $this->assertEquals(1, $DB->count_records('block_quickmail_signatures', ['userid' => $user2->id ]));

    }
}
