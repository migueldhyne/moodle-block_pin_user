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
 * Languages configuration for the block_pin_user plugin.
 *
 * @package   block_pin_user
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['badgebg']                    = 'Background colour';
$string['badgebg_desc']               = 'Background colour for this badge.';
$string['badgecolor']                 = 'Text colour';
$string['badgecolor_desc']            = 'Text colour for this badge.';
$string['badgename']                  = 'Badge name';
$string['badgename_desc']             = 'A clearer, more descriptive name used in export links and CSV headers, where space isn\'t as tight as on the badge itself (which often uses a short abbreviation). Leave empty to reuse the badge text below (or "Badge N" if both are empty).';
$string['badgesettings']              = 'Badge {$a}';
$string['badgesettings_desc']         = 'Configure when this badge appears and how it looks. Leave the profile field on "None" to disable it.';
$string['badgesintro']                = 'Badges';
$string['badgesintro_desc']           = 'Configure up to {$a} badges, each shown next to a participant\'s name when its condition is met. Unused badges can simply be left on "None".';
$string['badgetext']                  = 'Text';
$string['badgetext_desc']             = 'Label shown inside the badge. Can be left empty if an icon is enough on its own.';
$string['combinator']                 = 'Combine with';
$string['combinator_desc']            = 'How the main condition combines with the additional condition below. Has no effect if no additional profile field is selected.';
$string['combinatorand']              = 'AND (both conditions must be true)';
$string['combinatoror']               = 'OR (at least one condition must be true)';
$string['condition']                  = 'Condition';
$string['condition_desc']             = 'Select the condition the chosen profile field must meet.';
$string['conditionb']                 = 'Additional condition';
$string['conditionb_desc']            = 'Select the condition the additional profile field must meet.';
$string['conditionvalue']             = 'Comparison value';
$string['conditionvalue_desc']        = 'Used by the "equals", "contains" and "does not contain" conditions.';
$string['conditionvalueb']            = 'Additional comparison value';
$string['conditionvalueb_desc']       = 'Used by the additional condition when it is "equals", "contains" or "does not contain".';
$string['contains']                   = 'Must contain';
$string['equals']                     = 'Must equal';
$string['existingfields']             = 'Custom profile fields that already exist on this site:';
$string['exportall']                  = 'All participants (CSV)';
$string['exportcolemail']             = 'Email';
$string['exportcolname']              = 'Full name';
$string['exportcolvalue']             = 'Value';
$string['exportcolvalueb']            = 'Value (additional condition)';
$string['exportlabel']                = 'Export:';
$string['exportyes']                  = 'Yes';
$string['icon']                       = 'Icon';
$string['icon_desc']                  = 'Optional icon shown before the badge text (or on its own if the text is left empty).';
$string['iconaccessibility']          = 'Accessibility';
$string['iconbell']                   = 'Notification';
$string['iconcheck']                  = 'Validated';
$string['iconflag']                   = 'Flagged';
$string['iconheart']                  = 'Heart / health';
$string['iconinfo']                   = 'Information';
$string['iconlock']                   = 'Confidential';
$string['iconmedical']                = 'Medical';
$string['iconnone']                   = 'None';
$string['iconstar']                   = 'Important';
$string['iconwarning']                = 'Warning';
$string['invalidbadge']               = 'Invalid or unconfigured badge.';
$string['isempty']                    = 'Must be empty';
$string['isnotempty']                 = 'Must not be empty';
$string['manageprofilefields']        = 'Quick actions';
$string['manageprofilefields_button'] = 'Manage custom profile fields (opens in a new tab)';
$string['manageprofilefields_desc']   = 'A profile field must exist before it can be selected below. Use the shortcut below to create or review custom profile fields without leaving this page.';
$string['nofieldsyet']                = 'No custom profile fields exist on this site yet. Use the button below to create one.';
$string['none']                       = 'None (badge disabled)';
$string['notcontains']                = 'Must not contain';
$string['permissionsnotice']          = 'Permissions: what to check';
$string['permissionsnotice_button']   = 'Manage roles and permissions';
$string['permissionsnotice_desc']     = 'Two separate capabilities control this block: block/pin_user:addinstance (and :myaddinstance) decide who can add the block to a page, while block/pin_user:viewbadges decides who can see its content. On a fresh install, both are set up automatically. If you are upgrading from an earlier version of this plugin, Moodle automatically grants the new viewbadges capability to the Teacher and Manager roles, but it does NOT automatically update addinstance/myaddinstance permissions that already existed on your site: please check the Teacher role manually if needed.';
$string['pin_user']                   = 'Pin User';
$string['pin_user:addinstance']       = 'Add a new pin_user block';
$string['pin_user:myaddinstance']     = 'Add a new pin_user block to the My Moodle page';
$string['pin_user:viewbadges']        = 'View the pinned participant badges';
$string['pluginname']                 = 'Pin User';
$string['pluginnamedisplay']          = 'Pin User';
$string['privacy:metadata']           = 'The Pin User block does not store any personal data. It only reads and temporarily displays existing user profile data (which may include sensitive custom profile fields) to users with the relevant capability.';
$string['profilefield']               = 'Profile field';
$string['profilefield_desc']          = 'The custom user profile field to check. Choose "None" to disable this badge.';
$string['profilefieldb']              = 'Profile field (additional condition)';
$string['profilefieldb_desc']         = 'An optional second custom profile field. Leave on "None" to use a single condition only (the default, and the previous behaviour).';
$string['secondcondition']            = 'Second condition';
$string['secondcondition_desc']       = 'Optional. Click to combine a second profile field condition with the one above, using AND or OR.';
$string['secondcondition_toggle']     = 'Combine with a second condition (AND/OR)';
$string['upgradenotice_v2']           = 'Pin User has been updated to v2.0.0. It now uses a dedicated capability to control who can see the badges, and the capability to add the block now correctly defaults to the Teacher role. Moodle does not automatically update permissions that already existed before this upgrade: please review block/pin_user:addinstance and block/pin_user:myaddinstance for the Teacher role under Site administration > Users > Permissions > Define roles.';
$string['upgradenotice_v3']           = 'Pin User has been updated to v2.1.0: you can now configure up to {$a} badges (was 2), each with an optional icon. Your existing badges have been kept as they were - nothing to reconfigure. See the plugin settings page to add more.';
$string['upgradenotice_v4']           = 'Pin User has been updated to v2.2.0: each badge can now combine a second condition (AND/OR) with the first. This is opt-in - your existing badges only use a single condition and keep working exactly as before.';
$string['upgradenotice_v5']           = 'Pin User has been updated to v2.3.0: a "Export" link now appears above the participant list, letting teachers download a CSV of all participants (with one column per badge) or just the participants matching one specific badge.';
$string['upgradenotice_v6']           = 'Pin User has been updated: the "additional condition" fields (AND/OR) for each badge are now tucked behind a small "Combine with a second condition" toggle, right below that badge\'s comparison value field, instead of always being shown. Nothing was lost - your existing settings (if any) are exactly where you left them, and that section starts expanded if it was already in use.';
