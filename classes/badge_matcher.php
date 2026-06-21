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
 * Decides whether a given badge applies to a given participant row.
 *
 * Pulled out of the renderer so the exact same logic (including the
 * optional AND/OR second condition) can be reused by the CSV export without
 * risking the on-screen badges and the exported rows ever disagreeing.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class badge_matcher {

    /**
     * Whether the given badge's condition(s) are met for the given user row.
     *
     * @param \stdClass $user  A participant row as returned by
     *                         participant_loader::build_sql(), i.e. including
     *                         the udf_profilefield{index}[b]_value properties.
     * @param \stdClass $badge A badge config object, as returned by
     *                         badge_config::get_global_badges().
     * @return bool
     */
    public static function matches(\stdClass $user, \stdClass $badge): bool {
        $property = "udf_profilefield{$badge->index}_value";

        if (!property_exists($user, $property)) {
            return false;
        }

        $matches = condition_evaluator::evaluate($user->$property, $badge->condition, $badge->value);

        // A badge with no second field configured behaves exactly as a
        // single-condition badge always has.
        if ($badge->profilefieldb !== '') {
            $propertyb = "udf_profilefield{$badge->index}b_value";
            $valueb = property_exists($user, $propertyb) ? $user->$propertyb : null;
            $matchesb = condition_evaluator::evaluate($valueb, $badge->conditionb, $badge->valueb);
            $matches = ($badge->combinator === 'or') ? ($matches || $matchesb) : ($matches && $matchesb);
        }

        return $matches;
    }

    /**
     * The participant's raw value for the badge's primary profile field.
     *
     * Used by the CSV export to show actual data (e.g. "Arachides, gluten")
     * rather than a generic yes/no, wherever a value is available.
     *
     * @param \stdClass $user  A participant row (see matches()).
     * @param \stdClass $badge A badge config object.
     * @return string The raw value, or '' if empty/not present on the row.
     */
    public static function primary_value(\stdClass $user, \stdClass $badge): string {
        $property = "udf_profilefield{$badge->index}_value";
        return property_exists($user, $property) ? trim((string) $user->$property) : '';
    }

    /**
     * The participant's raw value for the badge's optional second profile field.
     *
     * @param \stdClass $user  A participant row (see matches()).
     * @param \stdClass $badge A badge config object.
     * @return string The raw value, or '' if not configured/empty/not present.
     */
    public static function secondary_value(\stdClass $user, \stdClass $badge): string {
        if ($badge->profilefieldb === '') {
            return '';
        }
        $property = "udf_profilefield{$badge->index}b_value";
        return property_exists($user, $property) ? trim((string) $user->$property) : '';
    }
}
