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
 * Settings configuration for the block_pin_user plugin.
 *
 * Each badge's optional second condition (combinator, profile field,
 * condition, value) is bundled into a single collapsed-by-default
 * <details>/<summary> widget (admin_setting_second_condition) instead of
 * four always-visible rows, so the common case - one condition per badge -
 * stays a short form, without needing a separate settings page or any
 * JavaScript.
 *
 * @package   block_pin_user
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $DB;

    $settings = new admin_settingpage(
        'block_pin_user_settings',
        get_string('pluginname', 'block_pin_user')
    );

    if ($ADMIN->fulltree) {
        // Build the list of custom profile fields that actually exist on this
        // site, so the admin picks from a real list instead of typing a
        // shortname blindly. An empty/"none" value means the badge is
        // disabled - this is also now the default, fixing the previous
        // behaviour where badges showed for every participant out of the
        // box on a fresh install.
        $fieldoptions = ['' => get_string('none', 'block_pin_user')];
        if (!during_initial_install() && $DB->get_manager()->table_exists('user_info_field')) {
            $records = $DB->get_records('user_info_field', null, 'name ASC', 'id, shortname, name');
            foreach ($records as $record) {
                $fieldoptions[$record->shortname] = $record->name . ' (' . $record->shortname . ')';
            }
        }

        // Build the condition dropdown options once (shared by every badge).
        $conditionoptions = [];
        foreach (\block_pin_user\badge_config::condition_options() as $key => $stringid) {
            $conditionoptions[$key] = get_string($stringid, 'block_pin_user');
        }

        // Build the combinator dropdown options once (shared by every badge).
        $combinatoroptions = [];
        foreach (\block_pin_user\badge_config::combinator_options() as $key => $stringid) {
            $combinatoroptions[$key] = get_string($stringid, 'block_pin_user');
        }

        // Build the icon dropdown options once (shared by every badge).
        $iconoptions = [];
        foreach (\block_pin_user\badge_config::icon_options() as $key => $stringid) {
            $label = get_string($stringid, 'block_pin_user');
            $iconoptions[$key] = ($key === '') ? $label : ($key . ' ' . $label);
        }

        // Permanent reminder: this plugin uses two separate capabilities (who can
        // add the block vs. who can see its content), and upgrading from an
        // older version may require a manual permission check (see README.md).
        $settings->add(new \block_pin_user\admin_setting_permissions_notice());

        // Quick action: shortcut to Moodle's own "manage user profile fields"
        // admin page, plus a reminder of which fields already exist, so the
        // admin never has to leave this page to go create the field they need.
        $settings->add(new \block_pin_user\admin_setting_profile_link());

        $settings->add(new admin_setting_heading(
            'block_pin_user_intro_heading',
            get_string('badgesintro', 'block_pin_user'),
            get_string('badgesintro_desc', 'block_pin_user', \block_pin_user\badge_config::MAX_BADGES)
        ));

        // Sensible, WCAG AA-friendly default colour pairs for each badge slot.
        $defaultcolours = [
            1 => ['#1e7e34', '#ffffff'],
            2 => ['#0a58ca', '#ffffff'],
            3 => ['#6f42c1', '#ffffff'],
            4 => ['#8a4b08', '#ffffff'],
            5 => ['#00695c', '#ffffff'],
            6 => ['#a01818', '#ffffff'],
        ];

        for ($i = 1; $i <= \block_pin_user\badge_config::MAX_BADGES; $i++) {
            [$defaultbg, $defaultcolor] = $defaultcolours[$i];

            // Enrich the heading with whatever name is already saved for this
            // badge, so it's easier to find the right section on a long
            // settings page once several badges are configured.
            $existingname = (string) get_config('block_pin_user', "badgename{$i}");
            $headingtitle = get_string('badgesettings', 'block_pin_user', $i);
            if ($existingname !== '') {
                $headingtitle .= ' — ' . $existingname;
            }

            $settings->add(new admin_setting_heading(
                "block_pin_user_badge{$i}_heading",
                $headingtitle,
                get_string('badgesettings_desc', 'block_pin_user')
            ));

            $settings->add(new admin_setting_configtext(
                "block_pin_user/badgename{$i}",
                get_string('badgename', 'block_pin_user'),
                get_string('badgename_desc', 'block_pin_user'),
                ''
            ));

            $settings->add(new admin_setting_configselect(
                "block_pin_user/profilefield{$i}",
                get_string('profilefield', 'block_pin_user'),
                get_string('profilefield_desc', 'block_pin_user'),
                '',
                $fieldoptions
            ));

            $settings->add(new admin_setting_configselect(
                "block_pin_user/profilefield{$i}condition",
                get_string('condition', 'block_pin_user'),
                get_string('condition_desc', 'block_pin_user'),
                'isnotempty',
                $conditionoptions
            ));

            $settings->add(new admin_setting_configtext(
                "block_pin_user/profilefield{$i}value",
                get_string('conditionvalue', 'block_pin_user'),
                get_string('conditionvalue_desc', 'block_pin_user'),
                ''
            ));

            // Optional second condition (AND/OR), collapsed by default behind
            // a small toggle: most badges only need a single condition, so
            // these four underlying values are tucked away here instead of
            // always taking up four extra rows.
            $settings->add(new \block_pin_user\admin_setting_second_condition(
                $i,
                $fieldoptions,
                $conditionoptions,
                $combinatoroptions
            ));

            $settings->add(new admin_setting_configselect(
                "block_pin_user/icon{$i}",
                get_string('icon', 'block_pin_user'),
                get_string('icon_desc', 'block_pin_user'),
                '',
                $iconoptions
            ));

            $settings->add(new admin_setting_configtext(
                "block_pin_user/text{$i}",
                get_string('badgetext', 'block_pin_user'),
                get_string('badgetext_desc', 'block_pin_user'),
                $i <= 2 ? "text{$i}" : ''
            ));

            $settings->add(new admin_setting_configcolourpicker(
                "block_pin_user/custombadge{$i}bg",
                get_string('badgebg', 'block_pin_user'),
                get_string('badgebg_desc', 'block_pin_user'),
                $defaultbg
            ));

            $settings->add(new admin_setting_configcolourpicker(
                "block_pin_user/custombadge{$i}color",
                get_string('badgecolor', 'block_pin_user'),
                get_string('badgecolor_desc', 'block_pin_user'),
                $defaultcolor
            ));
        }
    }
}
