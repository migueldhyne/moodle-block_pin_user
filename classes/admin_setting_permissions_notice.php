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
 * Read-only "quick action" admin setting reminding the admin how this
 * plugin's two capabilities (adding the block vs. seeing its content) work,
 * with a direct shortcut to "Define roles".
 *
 * Shown permanently (not just right after an upgrade) because the one-time
 * notification fired from db/upgrade.php is only visible when the upgrade is
 * run through the web UI - a CLI upgrade (php admin/cli/upgrade.php) would
 * otherwise leave the admin with no indication that anything needs checking.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_permissions_notice extends \admin_setting {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct(
            'block_pin_user/permissionsnotice',
            get_string('permissionsnotice', 'block_pin_user'),
            get_string('permissionsnotice_desc', 'block_pin_user'),
            ''
        );
    }

    /**
     * Returns the (always empty) stored value for this pseudo-setting.
     *
     * @return string Always empty: this setting does not store any value.
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
     * Renders the reminder text and the shortcut button.
     *
     * @param mixed  $data  Unused.
     * @param string $query Search query used to highlight matches on the admin search page.
     * @return string HTML for this settings row.
     */
    public function output_html($data, $query = '') {
        $url = new \moodle_url('/admin/roles/manage.php');
        $button = \html_writer::link(
            $url,
            get_string('permissionsnotice_button', 'block_pin_user'),
            [
                'class' => 'btn btn-secondary',
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]
        );

        $content = \html_writer::div($button, 'block_pin_user-admin-link');

        return format_admin_setting($this, $this->visiblename, $content, $this->description, false, '', null, $query);
    }
}
