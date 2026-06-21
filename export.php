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
 * CSV export for the block_pin_user plugin.
 *
 * Streams a CSV of the active participant list, either with one column per
 * configured badge ("export all"), or filtered down to only the
 * participants matching one specific badge.
 *
 * Reuses participant_loader and badge_matcher so the exported rows always
 * match exactly what is shown on screen by the block.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $DB;

$courseid = required_param('courseid', PARAM_INT);
$badgeindex = optional_param('badge', 0, PARAM_INT);

$course = get_course($courseid);
require_login($course);

$context = context_course::instance($courseid);
require_capability('block/pin_user:viewbadges', $context);

// This is a read-only export, but it can contain sensitive personal data
// (e.g. health-related custom profile fields), so it is protected with a
// sesskey check like any other sensitive action, not just state changes.
require_sesskey();

$allbadges = \block_pin_user\badge_config::get_global_badges();

if ($badgeindex > 0) {
    $selected = null;
    foreach ($allbadges as $candidate) {
        if ($candidate->index === $badgeindex) {
            $selected = $candidate;
            break;
        }
    }
    if ($selected === null) {
        throw new \moodle_exception('invalidbadge', 'block_pin_user');
    }
    $badges = [$selected];
} else {
    $badges = $allbadges;
}

if (empty($badges)) {
    throw new \moodle_exception('invalidbadge', 'block_pin_user');
}

[$sql, , $params, ] = \block_pin_user\participant_loader::build_sql($context, $badges);
$participants = $DB->get_records_sql($sql, $params);

$shortname = clean_filename($course->shortname);
if ($badgeindex > 0) {
    $filename = 'pin_user_' . $shortname . '_' . clean_filename(\block_pin_user\badge_config::label($badges[0])) . '.csv';
} else {
    $filename = 'pin_user_' . $shortname . '.csv';
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0, must-revalidate');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

// UTF-8 BOM so Excel (Windows) detects the encoding correctly instead of
// mangling accented characters.
fwrite($output, "\xEF\xBB\xBF");

// Semicolon delimiter: comma is the decimal separator in many locales
// (including fr_FR), and Excel in those locales expects ';'-separated CSV.
$delimiter = ';';

if ($badgeindex > 0) {
    $headers = [
        get_string('exportcolname', 'block_pin_user'),
        get_string('exportcolemail', 'block_pin_user'),
        get_string('exportcolvalue', 'block_pin_user'),
    ];
    if ($badges[0]->profilefieldb !== '') {
        $headers[] = get_string('exportcolvalueb', 'block_pin_user');
    }
    fputcsv($output, $headers, $delimiter);

    foreach ($participants as $participant) {
        if (\block_pin_user\badge_matcher::matches($participant, $badges[0])) {
            $row = [
                fullname($participant),
                $participant->email,
                \block_pin_user\badge_matcher::primary_value($participant, $badges[0]),
            ];
            if ($badges[0]->profilefieldb !== '') {
                $row[] = \block_pin_user\badge_matcher::secondary_value($participant, $badges[0]);
            }
            fputcsv($output, $row, $delimiter);
        }
    }
} else {
    $headers = [get_string('exportcolname', 'block_pin_user'), get_string('exportcolemail', 'block_pin_user')];
    foreach ($badges as $badge) {
        $headers[] = \block_pin_user\badge_config::label($badge);
    }
    fputcsv($output, $headers, $delimiter);

    foreach ($participants as $participant) {
        $row = [fullname($participant), $participant->email];
        foreach ($badges as $badge) {
            if (!\block_pin_user\badge_matcher::matches($participant, $badge)) {
                // No match: leave the cell blank rather than a "No" label,
                // since matching cells now show real data, not a yes/no.
                $row[] = '';
                continue;
            }

            $value = \block_pin_user\badge_matcher::primary_value($participant, $badge);
            // The badge matched but its field is empty (e.g. an "is empty"
            // condition, or a match driven entirely by the second
            // condition): fall back to a plain "Yes" so the match is still
            // visible instead of leaving an ambiguous blank cell.
            $row[] = $value !== '' ? $value : get_string('exportyes', 'block_pin_user');
        }
        fputcsv($output, $row, $delimiter);
    }
}

fclose($output);
