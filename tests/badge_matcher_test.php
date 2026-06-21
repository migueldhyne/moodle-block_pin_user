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
 * Unit tests for \block_pin_user\badge_matcher.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_pin_user\badge_matcher
 */
final class badge_matcher_test extends \advanced_testcase {

    /**
     * Builds a minimal badge config object for testing, with overrides.
     *
     * @param array $overrides
     * @return \stdClass
     */
    private function make_badge(array $overrides = []): \stdClass {
        $badge = (object) [
            'index' => 1,
            'profilefield' => 'sante',
            'condition' => 'isnotempty',
            'value' => '',
            'profilefieldb' => '',
            'conditionb' => 'isnotempty',
            'valueb' => '',
            'combinator' => 'and',
        ];
        foreach ($overrides as $key => $value) {
            $badge->$key = $value;
        }
        return $badge;
    }

    public function test_no_property_never_matches(): void {
        $user = (object) ['id' => 1];
        $this->assertFalse(badge_matcher::matches($user, $this->make_badge()));
    }

    public function test_single_condition_match(): void {
        $user = (object) ['id' => 1, 'udf_profilefield1_value' => 'arachides'];
        $this->assertTrue(badge_matcher::matches($user, $this->make_badge()));
    }

    public function test_single_condition_no_match(): void {
        $user = (object) ['id' => 1, 'udf_profilefield1_value' => ''];
        $this->assertFalse(badge_matcher::matches($user, $this->make_badge()));
    }

    public function test_and_requires_both(): void {
        $badge = $this->make_badge([
            'profilefieldb' => 'niveau', 'conditionb' => 'equals', 'valueb' => 'priorite', 'combinator' => 'and',
        ]);

        $bothtrue = (object) [
            'id' => 1, 'udf_profilefield1_value' => 'present', 'udf_profilefield1b_value' => 'priorite',
        ];
        $this->assertTrue(badge_matcher::matches($bothtrue, $badge));

        $onlyfirst = (object) [
            'id' => 1, 'udf_profilefield1_value' => 'present', 'udf_profilefield1b_value' => 'standard',
        ];
        $this->assertFalse(badge_matcher::matches($onlyfirst, $badge));
    }

    public function test_or_requires_either(): void {
        $badge = $this->make_badge([
            'profilefieldb' => 'niveau', 'conditionb' => 'equals', 'valueb' => 'priorite', 'combinator' => 'or',
        ]);

        $onlysecond = (object) [
            'id' => 1, 'udf_profilefield1_value' => '', 'udf_profilefield1b_value' => 'priorite',
        ];
        $this->assertTrue(badge_matcher::matches($onlysecond, $badge));

        $neither = (object) [
            'id' => 1, 'udf_profilefield1_value' => '', 'udf_profilefield1b_value' => 'standard',
        ];
        $this->assertFalse(badge_matcher::matches($neither, $badge));
    }

    public function test_primary_value_returns_raw_field_content(): void {
        $user = (object) ['id' => 1, 'udf_profilefield1_value' => '  Arachides, gluten  '];
        $this->assertSame('Arachides, gluten', badge_matcher::primary_value($user, $this->make_badge()));
    }

    public function test_primary_value_is_empty_when_property_missing(): void {
        $user = (object) ['id' => 1];
        $this->assertSame('', badge_matcher::primary_value($user, $this->make_badge()));
    }

    public function test_secondary_value_empty_when_no_second_field_configured(): void {
        $user = (object) ['id' => 1, 'udf_profilefield1b_value' => 'priorite'];
        $this->assertSame('', badge_matcher::secondary_value($user, $this->make_badge()));
    }

    public function test_secondary_value_returns_raw_field_content_when_configured(): void {
        $badge = $this->make_badge(['profilefieldb' => 'niveau']);
        $user = (object) ['id' => 1, 'udf_profilefield1b_value' => 'priorite'];
        $this->assertSame('priorite', badge_matcher::secondary_value($user, $badge));
    }
}
