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
 * Central place for everything related to the list of configurable badges:
 * how many are allowed, which icons can be picked, and how to read the
 * currently configured global (site-wide) badges from plugin config.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class badge_config {

    /**
     * Maximum number of badges configurable site-wide.
     *
     * Deliberately a bounded list of plain admin_setting fields rather than a
     * free-form JSON/CSV textarea: it keeps the settings page made entirely
     * of native, validated Moodle form elements (dropdowns, colour pickers)
     * with no custom JavaScript to write or maintain. Six is comfortably
     * more than most sites will ever need; raise this constant if needed.
     */
    public const MAX_BADGES = 6;

    /**
     * The condition identifiers offered for each badge.
     *
     * @return string[] condition key => language string identifier
     */
    public static function condition_options(): array {
        return [
            'isempty' => 'isempty',
            'isnotempty' => 'isnotempty',
            'equals' => 'equals',
            'contains' => 'contains',
            'notcontains' => 'notcontains',
        ];
    }

    /**
     * How a badge's optional second condition combines with its first.
     *
     * @return string[] combinator key => language string identifier
     */
    public static function combinator_options(): array {
        return [
            'and' => 'combinatorand',
            'or' => 'combinatoror',
        ];
    }

    /**
     * The curated list of selectable badge icons.
     *
     * Deliberately plain Unicode characters rather than Font Awesome or core
     * pix_icon identifiers: those depend on the exact icon set bundled by
     * the site's theme and Moodle version, which cannot be reliably assumed.
     * A Unicode character renders identically everywhere with zero
     * dependency on theme or Moodle version.
     *
     * @return string[] icon character (or '' for none) => language string identifier
     */
    public static function icon_options(): array {
        return [
            '' => 'iconnone',
            '⚠️' => 'iconwarning',
            '❤️' => 'iconheart',
            '✚' => 'iconmedical',
            '♿' => 'iconaccessibility',
            '⭐' => 'iconstar',
            '🚩' => 'iconflag',
            'ℹ️' => 'iconinfo',
            '✅' => 'iconcheck',
            '🔔' => 'iconbell',
            '🔒' => 'iconlock',
        ];
    }

    /**
     * Reads the global (site-wide, admin-configured) badge definitions.
     *
     * Badges whose profile field is not configured ("None") are skipped
     * entirely - they never appear in the returned array, so callers never
     * need to special-case a "disabled" badge.
     *
     * Each badge may optionally define a *second* condition (its own field,
     * condition and value), combined with the first via "combinator"
     * ('and'/'or'). When the second field is left on "None" (the default),
     * the badge behaves exactly as a single-condition badge always has -
     * this keeps every badge configured before this feature existed working
     * identically, with nothing to reconfigure.
     *
     * @return \stdClass[] List of badge config objects, in badge-number order.
     *                     Each has: index, profilefield, condition, value,
     *                     profilefieldb, conditionb, valueb, combinator,
     *                     icon, text, name, bgcolor, color.
     */
    public static function get_global_badges(): array {
        $badges = [];

        for ($i = 1; $i <= self::MAX_BADGES; $i++) {
            $profilefield = (string) get_config('block_pin_user', "profilefield{$i}");
            if ($profilefield === '') {
                continue;
            }

            $combinator = (string) get_config('block_pin_user', "combinator{$i}");
            if ($combinator !== 'and' && $combinator !== 'or') {
                $combinator = 'and';
            }

            $badges[] = (object) [
                'index' => $i,
                'profilefield' => $profilefield,
                'condition' => (string) get_config('block_pin_user', "profilefield{$i}condition"),
                'value' => (string) get_config('block_pin_user', "profilefield{$i}value"),
                'profilefieldb' => (string) get_config('block_pin_user', "profilefield{$i}b"),
                'conditionb' => (string) get_config('block_pin_user', "profilefield{$i}bcondition"),
                'valueb' => (string) get_config('block_pin_user', "profilefield{$i}bvalue"),
                'combinator' => $combinator,
                'icon' => (string) get_config('block_pin_user', "icon{$i}"),
                'text' => (string) get_config('block_pin_user', "text{$i}"),
                'name' => (string) get_config('block_pin_user', "badgename{$i}"),
                'bgcolor' => (string) get_config('block_pin_user', "custombadge{$i}bg"),
                'color' => (string) get_config('block_pin_user', "custombadge{$i}color"),
            ];
        }

        return $badges;
    }

    /**
     * The best available human-readable label for a badge.
     *
     * Used wherever space isn't as tight as on the badge itself (export
     * links, CSV headers, settings page headings): the dedicated "name"
     * field if set, otherwise the on-screen badge "text" (which is often a
     * short abbreviation chosen to fit next to a participant's name, and
     * not always the clearest label out of context), otherwise a generic
     * "Badge N" fallback.
     *
     * @param \stdClass $badge A badge config object.
     * @return string
     */
    public static function label(\stdClass $badge): string {
        $name = trim((string) ($badge->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $text = trim((string) $badge->text);
        if ($text !== '') {
            return $text;
        }

        return get_string('badgesettings', 'block_pin_user', $badge->index);
    }
}
