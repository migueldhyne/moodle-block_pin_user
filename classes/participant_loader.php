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
 * Builds the SQL used to fetch actively-enrolled course participants
 * together with the raw profile-field values needed to evaluate a given
 * list of badges.
 *
 * Shared by the block (paginated, on-screen) and by export.php (full list,
 * CSV) so both always see exactly the same set of participants and values.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class participant_loader {
    /**
     * Builds the participant-listing SQL and its matching count SQL.
     *
     * @param \context_course $context The course context to list participants for.
     * @param \stdClass[]     $badges  Badge config objects whose profile fields should be joined.
     * @return array{0: string, 1: string, 2: array, 3: array} [$sql, $countsql, $params, $countparams]
     */
    public static function build_sql(\context_course $context, array $badges): array {
        // Only actively enrolled users (excludes suspended enrolments and
        // enrolments outside their start/end window), built on Moodle's own
        // tested enrolment API rather than hand-rolled joins.
        [$enrolsql, $enrolparams] = get_enrolled_sql($context, '', 0, true);
        $params = $enrolparams;

        $fieldselects = '';
        $fieldjoins = '';

        foreach ($badges as $badge) {
            $i = $badge->index;
            $fieldselects .= ", udf{$i}.data AS udf_profilefield{$i}_value";
            $fieldjoins .= " LEFT JOIN {user_info_data} udf{$i}
                                ON u.id = udf{$i}.userid
                               AND udf{$i}.fieldid = (SELECT id FROM {user_info_field} WHERE shortname = :profilefield{$i})";
            $params["profilefield{$i}"] = $badge->profilefield;

            if ($badge->profilefieldb !== '') {
                $fieldselects .= ", udf{$i}b.data AS udf_profilefield{$i}b_value";
                $fieldjoins .= " LEFT JOIN {user_info_data} udf{$i}b
                                    ON u.id = udf{$i}b.userid
                                   AND udf{$i}b.fieldid = (SELECT id FROM {user_info_field} WHERE shortname = :profilefield{$i}b)";
                $params["profilefield{$i}b"] = $badge->profilefieldb;
            }
        }

        $sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename $fieldselects
                  FROM {user} u
                  JOIN ($enrolsql) eu ON eu.id = u.id
                  $fieldjoins
                 WHERE u.deleted = 0
              ORDER BY u.lastname ASC, u.firstname ASC";

        $countsql = "SELECT COUNT(u.id)
                       FROM {user} u
                       JOIN ($enrolsql) eu ON eu.id = u.id
                      WHERE u.deleted = 0";

        return [$sql, $countsql, $params, $enrolparams];
    }
}
