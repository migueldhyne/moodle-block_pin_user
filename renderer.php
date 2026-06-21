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
 * Renderer file for the block_pin_user plugin.
 *
 * This file defines the renderer for the Pin User block in Moodle,
 * which generates a link to each user's profile and appends custom
 * badges based on conditions applied to their profile fields.
 *
 * @package   block_pin_user
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_pin_user\badge_matcher;
use block_pin_user\badge_config;

/**
 * Renderer class for the block_pin_user plugin.
 *
 * This class is responsible for rendering the output of the block, including
 * participant names and conditional badges based on user profile fields.
 */
class block_pin_user_renderer extends plugin_renderer_base {

    /**
     * Renders a participant's name along with any badges whose condition is met.
     *
     * @param stdClass   $user     The user record, including custom profile field values
     *                             (only present on the property matching a configured badge).
     * @param int        $courseid The id of the course the participants page belongs to.
     * @param stdClass[] $badges   The active badge config objects, as returned by
     *                             \block_pin_user\badge_config::get_global_badges().
     * @return string HTML output for the participant line with any matching badges.
     */
    public function render_participant_with_pin($user, $courseid, array $badges) {

        $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $courseid]);
        $fullname = html_writer::link($profileurl, fullname($user));

        $html = html_writer::start_tag('div', ['class' => 'block_pin_user participant-name']);
        $html .= ' ' . $fullname;

        foreach ($badges as $badge) {
            if (badge_matcher::matches($user, $badge)) {
                $html .= $this->render_badge($badge);
            }
        }

        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Returns an HTML span element used as a badge.
     *
     * Both the badge text and icon are admin-configured settings, so the
     * text is explicitly escaped here: admin_setting_configtext does not
     * guarantee the value is HTML-safe, and this badge is rendered on every
     * page load of the participants page for every visitor with the right
     * capability. The icon is a fixed Unicode character chosen from a
     * curated list (see badge_config::icon_options()), so it does not need
     * escaping, but it is still wrapped so it can be hidden from screen
     * readers when accompanied by text (to avoid announcing it twice).
     *
     * @param stdClass $badge The badge config object (index, icon, text, ...).
     * @return string HTML span element representing the badge, or '' if there is nothing to show.
     */
    private function render_badge($badge) {
        $text = trim((string) $badge->text);
        $icon = trim((string) $badge->icon);

        if ($text === '' && $icon === '') {
            return '';
        }

        $inner = '';
        $classes = "pin-user-badge custom-badge{$badge->index}";
        $attributes = ['class' => $classes];

        if ($icon !== '') {
            if ($text !== '') {
                // Icon is decorative once the text already conveys the meaning.
                $inner .= html_writer::tag('span', $icon, ['aria-hidden' => 'true', 'class' => 'pin-user-badge-icon']);
            } else {
                // Icon-only badge: give it a meaningful label for screen readers,
                // derived from the same curated list used to build the admin dropdown.
                $iconlabel = badge_config::icon_options()[$icon] ?? null;
                $attributes['aria-label'] = $iconlabel ? get_string($iconlabel, 'block_pin_user') : $icon;
                $inner .= html_writer::tag('span', $icon, ['aria-hidden' => 'true', 'class' => 'pin-user-badge-icon']);
            }
        }

        if ($text !== '') {
            $inner .= s($text);
        }

        return html_writer::tag('span', $inner, $attributes);
    }
}
