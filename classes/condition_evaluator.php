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
 * Pure, framework-independent helper that evaluates a single badge condition
 * against a custom profile field value.
 *
 * Extracted out of the renderer so it can be unit tested in isolation,
 * without needing a full Moodle page/output stack.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class condition_evaluator {

    /**
     * List of condition identifiers this class knows how to evaluate.
     */
    public const CONDITIONS = ['isempty', 'isnotempty', 'equals', 'contains', 'notcontains'];

    /**
     * Evaluates whether a profile field value satisfies a configured condition.
     *
     * @param string|null $fieldvalue The user's custom profile field value, or null if unknown/unset.
     * @param string      $condition  One of the CONDITIONS constants.
     * @param string      $expected   The comparison value configured by the admin (used by
     *                                'equals', 'contains' and 'notcontains').
     * @return bool True if the condition is met.
     */
    public static function evaluate(?string $fieldvalue, string $condition, string $expected): bool {
        switch ($condition) {
            case 'isempty':
                return empty($fieldvalue);
            case 'isnotempty':
                return !empty($fieldvalue);
            case 'equals':
                return $fieldvalue !== null && $fieldvalue === $expected;
            case 'contains':
                return $fieldvalue !== null && $expected !== '' && strpos($fieldvalue, $expected) !== false;
            case 'notcontains':
                return $fieldvalue === null || $expected === '' || strpos($fieldvalue, $expected) === false;
            default:
                return false;
        }
    }
}
