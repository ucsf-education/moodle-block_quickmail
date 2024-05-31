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
 * Swedish language pack by Linnaeus University.
 *
 * @package    block_quickmail
 * @subpackage quickmail
 * @copyright  Linnaeus University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['add_all'] = 'Lägg till alla';
$string['add_button'] = 'Lägg till';
$string['all_sections'] = 'Alla sektioner';
$string['allowstudents'] = 'Tillåt studenter att använda Quickmail';
$string['alternate'] = 'Avsändaradresser';
$string['alternate_body'] = '<p> {$a->fullname} lade till {$a->address} som en alternativ avsändaradress för {$a->course}. </p> <p> Syftet med detta epost är att bekräfta att adressen finns och att Du har rätt behörighet i Moodle. </p> <p> Om Du vill slutföra verifieringen, gå till följade URL i Din webbläsare: {$a->url}. </p> <p> Om du inte förstår syftet med detta epost har du förmodligen fått det av misstag. Vi ber i så fall om ursäkt. Då kan du helt enkelt bortse från detta brev. </p> Tack.';
$string['alternate_new'] = 'Lägg till avsändaradress';
$string['alternate_subject'] = 'Bekräfta avsändaradress i Moodle';
$string['approved'] = 'Godkänd';
$string['are_you_sure'] = 'Vill du verkligen radera {$a->title}? Detta går inte att ångra.';
$string['attachment'] = 'Bilagor';
$string['composenew'] = 'Skriv nytt epost';
$string['config'] = 'Inställningar';
$string['default_flag'] = 'Standard';
$string['delete_confirm'] = 'Vill du verkligen radera meddelanden med \'{$a}\'?';
$string['delete_failed'] = 'Kunde inte radera epost';
$string['drafts'] = 'Utkast';
$string['email'] = 'Epost';
$string['entry_activated'] = 'Alternativ adress {$a->address} kan nu användas som avsändare i {$a->course}.';
$string['entry_failure'] = 'Epost kunde inte skickas till {$a->address}. Kontrollera att {$a->address} finns, och försök igen.';
$string['entry_key_not_valid'] = 'Aktiveringslänken för {$a->address} är inte längre giltig. Fortsätt för att skicka länken igen.';
$string['entry_saved'] = 'Avsändaradress {$a->address} har sparats.';
$string['entry_success'] = 'Ett epost har skickats till {$a->address}. Följ instruktionerna i epostet för att verifiera adressen.';
$string['from'] = 'Från';
$string['history'] = 'Historik';
$string['log'] = 'Historik';
$string['message'] = 'Meddelande';
$string['no_alternates'] = 'Inga alternativa avsändaradresser för \'{$a->fullname}\' än. Fortsätt för att skapa en.';
$string['no_course'] = 'Felaktigt kursid {$a}';
$string['no_drafts'] = 'Inga utkast.';
$string['no_email'] = 'Kunde inte skicka epost till {$a->firstname} {$a->lastname}.';
$string['no_filter'] = 'Alla';
$string['no_log'] = 'Ingen historik.';
$string['no_permission'] = 'Du är inte behörig att skicka epost med Quickmail.';
$string['no_selected'] = 'Du måste välja användare.';
$string['no_subject'] = 'Du måste skriva ett ämne.';
$string['potential_sections'] = 'Tillgängliga sektioner';
$string['potential_users'] = 'Tillgängliga användare';
$string['prepend_class'] = 'Inled med kursnamn';
$string['privacy:metadata:block_quickmail_drafts'] = 'Lagrar utkast till osända quickmail-meddelanden.';
$string['privacy:metadata:block_quickmail_drafts:additional_email'] = 'E-postmeddelanden från ytterligare mottagare.';
$string['privacy:metadata:block_quickmail_drafts:alternateid'] = 'ID för den alternativa e-postadressen för detta meddelande.';
$string['privacy:metadata:block_quickmail_drafts:attachment'] = 'Namnen på filerna som bifogas detta meddelande.';
$string['privacy:metadata:block_quickmail_drafts:courseid'] = 'ID för kursen som detta utkast tillhör.';
$string['privacy:metadata:block_quickmail_drafts:format'] = 'ID för textformatet som ska användas för detta utkast.';
$string['privacy:metadata:block_quickmail_drafts:id'] = 'ID-utkastet.';
$string['privacy:metadata:block_quickmail_drafts:mailto'] = 'En lista över användar-ID:n för mottagarna.';
$string['privacy:metadata:block_quickmail_drafts:message'] = 'Innehållet i detta utkast.';
$string['privacy:metadata:block_quickmail_drafts:subject'] = 'Ämnet för detta utkast.';
$string['privacy:metadata:block_quickmail_drafts:time'] = 'Tidpunkten då detta utkast senast uppdaterades.';
$string['privacy:metadata:block_quickmail_drafts:userid'] = 'ID för användaren som skapade detta utkast.';
$string['privacy:metadata:block_quickmail_log'] = 'Butiker skickade snabbmeddelanden.';
$string['privacy:metadata:block_quickmail_log:additional_emails'] = 'E-postmeddelanden från ytterligare mottagare.';
$string['privacy:metadata:block_quickmail_log:alterateid'] = 'ID för den alternativa e-postadressen för detta meddelande.';
$string['privacy:metadata:block_quickmail_log:attachment'] = 'Namnen på filerna som bifogas detta meddelande.';
$string['privacy:metadata:block_quickmail_log:courseid'] = 'ID för kursen detta meddelande tillhör.';
$string['privacy:metadata:block_quickmail_log:failuserids'] = 'En lista med användar-ID:n för de misslyckade mottagarna.';
$string['privacy:metadata:block_quickmail_log:format'] = 'ID för textformatet som ska användas för detta meddelande.';
$string['privacy:metadata:block_quickmail_log:id'] = 'Meddelande-ID.';
$string['privacy:metadata:block_quickmail_log:mailto'] = 'En lista över användar-ID:n för mottagarna.';
$string['privacy:metadata:block_quickmail_log:message'] = 'Innehållet i meddelandet.';
$string['privacy:metadata:block_quickmail_log:subject'] = 'Ämnet för meddelandet.';
$string['privacy:metadata:block_quickmail_log:time'] = 'Tidsstämpeln för när detta meddelande skickades.';
$string['privacy:metadata:block_quickmail_log:userid'] = 'ID för användaren som skickade detta meddelande.';
$string['privacy:metadata:block_quickmail_signatures'] = 'Lagrar anpassade användarmeddelandesignaturer.';
$string['privacy:metadata:block_quickmail_signatures:default_flag'] = 'Flaggar standardsignaturen.';
$string['privacy:metadata:block_quickmail_signatures:id'] = 'Signatur-ID.';
$string['privacy:metadata:block_quickmail_signatures:signature'] = 'Innehållet i signaturen.';
$string['privacy:metadata:block_quickmail_signatures:title'] = 'Signaturtiteln.';
$string['privacy:metadata:block_quickmail_signatures:userid'] = 'ID för användaren som äger denna signatur.';
$string['quickmail:allowalternate'] = 'Tillåt användare att lägga till avsändaradresser för kursen.';
$string['quickmail:cansend'] = 'Får skicka epost med Quickmail';
$string['receipt'] = 'Skicka kopia till Dig själv';
$string['remove_all'] = 'Ta bort alla';
$string['remove_button'] = 'Ta bort';
$string['role_filter'] = 'Filter (roller)';
$string['save_draft'] = 'Spara utkast';
$string['select_roles'] = 'Tillåt roller';
$string['selected'] = 'Mottagare';
$string['send_email'] = 'Skicka';
$string['sig'] = 'Signatur';
$string['signature'] = 'Signatur';
$string['subject'] = 'Ämne';
$string['sure'] = 'Vill du verkligen ta bort {$a->address}? Detta går inte att ångra.';
$string['title'] = 'Titel';
$string['valid'] = 'Aktiveringsstatus';
$string['waiting'] = 'Väntar';
