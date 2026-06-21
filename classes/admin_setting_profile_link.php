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

namespace block_pin_user;

/**
 * Read-only "quick action" admin setting.
 *
 * It does not store any value of its own (nosave = true). Its only purpose
 * is UX: custom profile fields must already exist before they can be picked
 * in the dropdowns below, so this row gives the admin a direct shortcut to
 * Moodle's own "Manage user profile fields" page, plus a reminder of which
 * fields already exist on this site, without having to leave the plugin's
 * settings page to go hunting through Site administration.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_profile_link extends \admin_setting {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct(
            'block_pin_user/profilefieldslink',
            get_string('manageprofilefields', 'block_pin_user'),
            get_string('manageprofilefields_desc', 'block_pin_user'),
            ''
        );
    }

    /**
     * There is nothing to get: this setting does not represent stored data.
     *
     * @return string Always returns an empty string so admin_setting never reports it as "unset".
     */
    public function get_setting() {
        return '';
    }

    /**
     * Returns the default value for this pseudo-setting.
     *
     * @return string Always empty.
     */
    public function get_defaultsetting() {
        return '';
    }

    /**
     * Nothing is ever written for this pseudo-setting.
     *
     * @param mixed $data Unused.
     * @return string Empty string means "no error".
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Renders the quick-action button and the list of existing profile fields.
     *
     * @param mixed  $data  Unused.
     * @param string $query Search query used to highlight matches on the admin search page.
     * @return string HTML for this settings row.
     */
    public function output_html($data, $query = '') {
        global $DB;

        $url = new \moodle_url('/user/profile/index.php');
        $button = \html_writer::link(
            $url,
            get_string('manageprofilefields_button', 'block_pin_user'),
            [
                'class' => 'btn btn-secondary',
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]
        );

        $fieldlines = [];
        if (!during_initial_install() && $DB->get_manager()->table_exists('user_info_field')) {
            $records = $DB->get_records('user_info_field', null, 'name ASC', 'id, shortname, name');
            foreach ($records as $record) {
                $fieldlines[] = $record->name . ' (' . $record->shortname . ')';
            }
        }

        if ($fieldlines) {
            $summary = \html_writer::tag('p', get_string('existingfields', 'block_pin_user'))
                . \html_writer::alist(array_map('s', $fieldlines));
        } else {
            $summary = \html_writer::tag('p', get_string('nofieldsyet', 'block_pin_user'));
        }

        $content = \html_writer::div($summary . $button, 'block_pin_user-admin-link');

        return format_admin_setting($this, $this->visiblename, $content, $this->description, false, '', null, $query);
    }
}
