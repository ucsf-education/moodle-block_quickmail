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
 * US-English language pack.
 *
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['actions'] = 'Actions';
$string['add_all'] = 'Add All';
$string['add_button'] = 'Add';
$string['addionalemail'] = 'Allow emails to external email addresses';
$string['addionalemail_desc'] = 'If this option is enabled quickmail emails are also sent so external email adresses the user entered within the form.';
$string['additional_emails'] = 'Additional Emails';
$string['additional_emails_help'] = 'Other email addresses you would like the message sent to, in a comma or semicolon separated list. Example:

 email1@example.com, email2@example.com
 ';
$string['admin_email_send_receipt'] = 'Admin Email Send Receipt';
$string['all_sections'] = 'All Sections';
$string['allowstudents'] = 'Allow students to use Quickmail';
$string['allowstudentsdesc'] = 'Allow students to use Quickmail. If you choose "Never", the block cannot be configured to allow students access at the course level.';
$string['allusers'] = ' All Users';
$string['alternate'] = 'Alternate Emails';
$string['alternate_body'] = '
<p>
{$a->fullname} added {$a->address} as an alternate sending address for {$a->course}.
</p>

<p>
The purpose of this email was to verify that this address exists, and the owner
of this address has the appropriate permissions in Moodle.
</p>

<p>
If you wish to complete the verification process, please continue by directing
your browser to the following url: {$a->url}.
</p>

<p>
If the description of this email does not make any sense to you, then you may have
received it by mistake. Simply discard this message.
</p>

Thank you.
';
$string['alternate_from'] = 'Moodle: Quickmail';
$string['alternate_new'] = 'Add Alternate Address';
$string['alternate_subject'] = 'Alternate email address verification';
$string['approved'] = 'Approved';
$string['are_you_sure'] = 'Are you sure you want to delete {$a->title}? This action cannot be reversed.';
$string['attachment'] = 'Attachment(s)';
$string['backup_block_configuration'] = 'Backup Quickmail Block Level Configuration Settings (Such as [Allow Students to use Quickmail])';
$string['backup_history'] = 'Include Quickmail History';
$string['body'] = 'Body';
$string['composenew'] = 'Compose New Email';
$string['config'] = 'Configuration';
$string['courseferpa'] = 'Respect Course Mode';
$string['courselayout'] = 'Course Layout';
$string['courselayout_desc'] = 'Use _Course_ page layout  when rendering the Quickmail block pages. Enable this setting, if you are getting Moodle form fixed width issues.';
$string['default_flag'] = 'Default';
$string['delete_confirm'] = 'Are you sure you want to delete message with the following details: {$a}';
$string['delete_failed'] = 'Failed to delete email';
$string['download_all'] = 'Download All';
$string['download_auth_only'] = 'Authorized Users Only';
$string['download_open'] = 'Open Downloads';
$string['downloads'] = 'Require login for attachments';
$string['downloads_desc'] = 'This setting determines if attachments are available only to logged in Moodle users';
$string['drafts'] = 'View Drafts';
$string['draftssuccess'] = "Draft";
$string['email'] = 'Email';
$string['email_error'] = 'Could not email: {$a->firstname} {$a->lastname} ({$a->email})';
$string['email_error_field'] = 'Can not have an empty: {$a}';
$string['entry_activated'] = 'Alternate email {$a->address} can now be used in {$a->course}.';
$string['entry_failure'] = 'An email could not be sent to {$a->address}. Please verify that {$a->address} exists, and try again.';
$string['entry_key_not_valid'] = 'Activation link is no longer valid for {$a->address}. Continue to resend activation link.';
$string['entry_saved'] = 'Alternate address {$a->address} has been saved.';
$string['entry_success'] = 'An email to verify that the address is valid has been sent to {$a->address}. Instructions on how to activate the address is contained in its contents.';
$string['eventalternateemailadded'] = 'Alternate email added';
$string['failed_to_send_to'] = 'failed to send to';
$string['ferpa'] = 'FERPA Mode';
$string['ferpa_desc'] = 'Allows the system to behave either according to the course groupmode setting, ignoring the groupmode setting but separating groups, or ignoring groups altogether.';
$string['from'] = 'From';
$string['history'] = 'View History';
$string['log'] = 'View History';
$string['logsuccess'] = 'all messages sent successfully';
$string['message'] = 'Message';
$string['message_body_as_follows'] = 'message body as follows ';
$string['message_failure'] = 'some users did not get message';
$string['message_sent_to'] = 'Message sent to ';
$string['messageprovider:broadcast'] = 'Send broadcast messages using Admin Email.';
$string['moodle_attachments'] = 'Moodle Attachments ({$a})';
$string['new'] = 'New';
$string['no'] = 'No';
$string['no_alternates'] = 'No alternate emails found for {$a->fullname}. Continue to make one.';
$string['no_course'] = 'Invalid Course with id of {$a}';
$string['no_drafts'] = 'You have no email drafts.';
$string['no_email'] = 'Could not email {$a->firstname} {$a->lastname}.';
$string['no_email_address'] = 'Could not email {$a}';
$string['no_filter'] = 'No filter';
$string['no_log'] = 'You have no email history yet.';
$string['no_permission'] = 'You do not have permission to send emails with Quickmail.';
$string['no_section'] = 'Not in a section';
$string['no_selected'] = 'You must select some users for emailing.';
$string['no_subject'] = 'You must have a subject';
$string['no_type'] = '{$a} is not in the acceptable type viewer. Please use the applciation correctly.';
$string['no_usergroups'] = 'There are no users in your group capable of being emailed.';
$string['no_users'] = 'There are no users you are capable of emailing.';
$string['noferpa'] = 'No Group Respect';
$string['noreply'] = 'No-Reply';
$string['not_valid'] = 'This is not a valid email log viewer type: {$a}';
$string['not_valid_action'] = 'You must provide a valid action: {$a}';
$string['not_valid_typeid'] = 'You must provide a valid email for {$a}';
$string['not_valid_user'] = 'You can not view other email history.';
$string['overwrite_history'] = 'Overwrite Quickmail History';
$string['pluginname'] = 'Quickmail';
$string['potential_sections'] = 'Potential Sections';
$string['potential_users'] = 'Potential Recipients';
$string['prepend_class'] = 'Prepend Course name';
$string['prepend_class_desc'] = 'Prepend the course shortname to the subject of the email.';
$string['privacy:metadata:block_quickmail_drafts'] = 'Stores drafts of unsent quickmail messages.';
$string['privacy:metadata:block_quickmail_drafts:additional_email'] = 'Emails of additional recipients.';
$string['privacy:metadata:block_quickmail_drafts:alternateid'] = 'The ID of the alternate email for this message.';
$string['privacy:metadata:block_quickmail_drafts:attachment'] = 'The names of files attached to this message.';
$string['privacy:metadata:block_quickmail_drafts:courseid'] = 'The ID of the course that this draft belongs to.';
$string['privacy:metadata:block_quickmail_drafts:format'] = 'The ID of the text format to be used for this draft.';
$string['privacy:metadata:block_quickmail_drafts:id'] = 'The draft ID.';
$string['privacy:metadata:block_quickmail_drafts:mailto'] = 'A list of user IDs for the recipients.';
$string['privacy:metadata:block_quickmail_drafts:message'] = 'The contents of this draft.';
$string['privacy:metadata:block_quickmail_drafts:subject'] = 'The subject of this draft.';
$string['privacy:metadata:block_quickmail_drafts:time'] = 'The time when this draft was last updated.';
$string['privacy:metadata:block_quickmail_drafts:userid'] = 'The ID of the user who created this draft.';
$string['privacy:metadata:block_quickmail_log'] = 'Stores sent quickmail messages.';
$string['privacy:metadata:block_quickmail_log:additional_emails'] = 'Emails of additional recipients.';
$string['privacy:metadata:block_quickmail_log:alterateid'] = 'The ID of the alternate email for this message.';
$string['privacy:metadata:block_quickmail_log:attachment'] = 'The names of files attached to this message.';
$string['privacy:metadata:block_quickmail_log:courseid'] = 'The ID of the course this message belongs to.';
$string['privacy:metadata:block_quickmail_log:failuserids'] = 'A list of user IDs for the failed recipients.';
$string['privacy:metadata:block_quickmail_log:format'] = 'The ID of the text format to be used for this message.';
$string['privacy:metadata:block_quickmail_log:id'] = 'The message ID.';
$string['privacy:metadata:block_quickmail_log:mailto'] = 'A list of user IDs for the recipients.';
$string['privacy:metadata:block_quickmail_log:message'] = 'The contents of the message.';
$string['privacy:metadata:block_quickmail_log:subject'] = 'The subject of the message.';
$string['privacy:metadata:block_quickmail_log:time'] = 'The timestamp of when this message was sent.';
$string['privacy:metadata:block_quickmail_log:userid'] = 'The ID of the user who sent this message.';
$string['privacy:metadata:block_quickmail_signatures'] = 'Stores custom user message signatures.';
$string['privacy:metadata:block_quickmail_signatures:default_flag'] = 'Flags the default signature.';
$string['privacy:metadata:block_quickmail_signatures:id'] = 'The signature ID.';
$string['privacy:metadata:block_quickmail_signatures:signature'] = 'The contents of the signature.';
$string['privacy:metadata:block_quickmail_signatures:title'] = 'The signature title.';
$string['privacy:metadata:block_quickmail_signatures:userid'] = 'The ID of the user who owns this signature.';
$string['qm_contents'] = 'Download File Contents';
$string['quickmail:addinstance'] = "Add a new Quickmail block to a course page";
$string['quickmail:allowalternate'] = "Allows users to add an alternate email for courses.";
$string['quickmail:canconfig'] = "Allows users to configure Quickmail instance.";
$string['quickmail:candelete'] = "Allows users to delete email from history.";
$string['quickmail:canimpersonate'] = "Allows users to log in as other users and view history.";
$string['quickmail:cansend'] = "Allows users to send email through Quickmail";
$string['quickmail:myaddinstance'] = "Add a new Quickmail block to the /my page";
$string['receipt'] = 'Receive a copy';
$string['receipt_help'] = 'Receive a copy of the email being sent';
$string['remove_all'] = 'Remove All';
$string['remove_button'] = 'Remove';
$string['required'] = 'Please fill in the required fields.';
$string['reset'] = 'Restore System Defaults';
$string['restore_history'] = 'Restore Quickmail History';
$string['role_filter'] = 'Role Filter';
$string['save_draft'] = 'Save Draft';
$string['seconds'] = 'seconds';
$string['select_groups'] = 'Select Sections ...';
$string['select_roles'] = 'Roles to filter by';
$string['select_users'] = 'Select Users ...';
$string['selected'] = 'Selected Recipients';
$string['send_again'] = 'send again';
$string['send_email'] = 'Send Email';
$string['sendadmin'] = 'Send Admin Email';
$string['sent_success'] = 'all messages sent successfully';
$string['sent_successfully_to_the_following_users'] = 'sent successfully to the following users: ';
$string['sig'] = 'Signature';
$string['signature'] = 'Signatures';
$string['something_broke'] = 'It looks like you either have email sending disabled or things are very broken';
$string['status'] = 'status';
$string['strictferpa'] = 'Always Separate Groups';
$string['subject'] = 'Subject';
$string['sure'] = 'Are you sure you want to delete {$a->address}? This action cannot be undone.';
$string['time_elapsed'] = 'Time Elapsed: ';
$string['title'] = 'Title';
$string['user'] = 'user';
$string['users'] = 'users';
$string['valid'] = 'Activation Status';
$string['waiting'] = 'Waiting';
$string['warnings'] = 'Warnings';
