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
 * Unit tests for \block_pin_user\admin_setting_second_condition.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_pin_user\admin_setting_second_condition
 */
final class admin_setting_second_condition_test extends \advanced_testcase {
    /**
     * Loads lib/adminlib.php before each test, since the parent class
     * (\admin_setting) is not autoloaded.
     */
    protected function setUp(): void {
        parent::setUp();
        // The admin_setting class (which admin_setting_second_condition
        // extends) lives in lib/adminlib.php, which is only loaded on admin
        // pages and is not autoloaded - it must be required explicitly here.
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');
    }

    /**
     * Builds an instance for badge 1 with the standard option sets.
     *
     * @return admin_setting_second_condition
     */
    private function make_setting(): admin_setting_second_condition {
        return new admin_setting_second_condition(
            1,
            ['' => 'None', 'sante' => 'Santé', 'niveau' => 'Niveau'],
            ['isempty' => 'Is empty', 'isnotempty' => 'Is not empty', 'equals' => 'Equals'],
            ['and' => 'AND', 'or' => 'OR']
        );
    }

    public function test_valid_data_is_stored_under_original_config_keys(): void {
        $this->resetAfterTest();

        $setting = $this->make_setting();
        $setting->write_setting([
            'combinator' => 'or',
            'profilefieldb' => 'niveau',
            'conditionb' => 'equals',
            'valueb' => 'priorite',
        ]);

        $this->assertSame('or', get_config('block_pin_user', 'combinator1'));
        $this->assertSame('niveau', get_config('block_pin_user', 'profilefield1b'));
        $this->assertSame('equals', get_config('block_pin_user', 'profilefield1bcondition'));
        $this->assertSame('priorite', get_config('block_pin_user', 'profilefield1bvalue'));
    }

    public function test_get_setting_round_trips_what_was_written(): void {
        $this->resetAfterTest();

        $setting = $this->make_setting();
        $setting->write_setting([
            'combinator' => 'or',
            'profilefieldb' => 'sante',
            'conditionb' => 'isnotempty',
            'valueb' => '',
        ]);

        $this->assertSame([
            'combinator' => 'or',
            'profilefieldb' => 'sante',
            'conditionb' => 'isnotempty',
            'valueb' => '',
        ], $setting->get_setting());
    }

    public function test_unknown_field_falls_back_to_disabled(): void {
        $this->resetAfterTest();

        $setting = $this->make_setting();
        $setting->write_setting([
            'combinator' => 'and',
            'profilefieldb' => 'not-a-real-field',
            'conditionb' => 'isnotempty',
            'valueb' => '',
        ]);

        // An unrecognised profile field shortname must never be stored as-is:
        // it falls back to '' (disabled), the same as if "None" was chosen.
        $this->assertSame('', get_config('block_pin_user', 'profilefield1b'));
    }

    public function test_unknown_combinator_falls_back_to_and(): void {
        $this->resetAfterTest();

        $setting = $this->make_setting();
        $setting->write_setting([
            'combinator' => 'xor',
            'profilefieldb' => 'sante',
            'conditionb' => 'isnotempty',
            'valueb' => '',
        ]);

        $this->assertSame('and', get_config('block_pin_user', 'combinator1'));
    }

    public function test_unknown_condition_falls_back_to_isnotempty(): void {
        $this->resetAfterTest();

        $setting = $this->make_setting();
        $setting->write_setting([
            'combinator' => 'and',
            'profilefieldb' => 'sante',
            'conditionb' => 'bogus',
            'valueb' => '',
        ]);

        $this->assertSame('isnotempty', get_config('block_pin_user', 'profilefield1bcondition'));
    }

    public function test_non_array_data_is_ignored_without_error(): void {
        $this->resetAfterTest();

        $setting = $this->make_setting();
        $result = $setting->write_setting('not-an-array');

        $this->assertSame('', $result);
        // Nothing should have been written.
        $this->assertFalse(get_config('block_pin_user', 'profilefield1b'));
    }
}
