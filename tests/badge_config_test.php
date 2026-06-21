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
 * Unit tests for \block_pin_user\badge_config.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_pin_user\badge_config
 */
final class badge_config_test extends \advanced_testcase {
    public function test_unconfigured_site_returns_no_badges(): void {
        $this->resetAfterTest();

        // Explicitly blank every slot, simulating a fresh install where
        // nothing has been configured yet.
        for ($i = 1; $i <= badge_config::MAX_BADGES; $i++) {
            set_config("profilefield{$i}", '', 'block_pin_user');
        }

        $this->assertSame([], badge_config::get_global_badges());
    }

    public function test_only_configured_badges_are_returned(): void {
        $this->resetAfterTest();

        for ($i = 1; $i <= badge_config::MAX_BADGES; $i++) {
            set_config("profilefield{$i}", '', 'block_pin_user');
        }

        set_config('profilefield2', 'sante', 'block_pin_user');
        set_config('profilefield2condition', 'isnotempty', 'block_pin_user');
        set_config('text2', 'PAI', 'block_pin_user');
        set_config('icon2', '⚠️', 'block_pin_user');

        $badges = badge_config::get_global_badges();

        $this->assertCount(1, $badges);
        $this->assertSame(2, $badges[0]->index);
        $this->assertSame('sante', $badges[0]->profilefield);
        $this->assertSame('PAI', $badges[0]->text);
        $this->assertSame('⚠️', $badges[0]->icon);

        // The second condition was never configured: it must default to
        // "disabled" (empty field) and a safe 'and' combinator.
        $this->assertSame('', $badges[0]->profilefieldb);
        $this->assertSame('and', $badges[0]->combinator);
    }

    public function test_second_condition_is_read_when_configured(): void {
        $this->resetAfterTest();

        for ($i = 1; $i <= badge_config::MAX_BADGES; $i++) {
            set_config("profilefield{$i}", '', 'block_pin_user');
        }

        set_config('profilefield1', 'sante', 'block_pin_user');
        set_config('profilefield1b', 'niveau', 'block_pin_user');
        set_config('profilefield1bcondition', 'equals', 'block_pin_user');
        set_config('profilefield1bvalue', 'priorite', 'block_pin_user');
        set_config('combinator1', 'or', 'block_pin_user');

        $badges = badge_config::get_global_badges();

        $this->assertCount(1, $badges);
        $this->assertSame('niveau', $badges[0]->profilefieldb);
        $this->assertSame('equals', $badges[0]->conditionb);
        $this->assertSame('priorite', $badges[0]->valueb);
        $this->assertSame('or', $badges[0]->combinator);
    }

    public function test_invalid_stored_combinator_falls_back_to_and(): void {
        $this->resetAfterTest();

        for ($i = 1; $i <= badge_config::MAX_BADGES; $i++) {
            set_config("profilefield{$i}", '', 'block_pin_user');
        }

        set_config('profilefield1', 'sante', 'block_pin_user');
        set_config('combinator1', 'not-a-real-value', 'block_pin_user');

        $badges = badge_config::get_global_badges();

        $this->assertSame('and', $badges[0]->combinator);
    }

    public function test_icon_and_condition_options_have_language_strings(): void {
        foreach (badge_config::icon_options() as $stringid) {
            $this->assertTrue(
                get_string_manager()->string_exists($stringid, 'block_pin_user'),
                "Missing language string '{$stringid}' used by badge_config::icon_options()"
            );
        }
        foreach (badge_config::condition_options() as $stringid) {
            $this->assertTrue(
                get_string_manager()->string_exists($stringid, 'block_pin_user'),
                "Missing language string '{$stringid}' used by badge_config::condition_options()"
            );
        }
        foreach (badge_config::combinator_options() as $stringid) {
            $this->assertTrue(
                get_string_manager()->string_exists($stringid, 'block_pin_user'),
                "Missing language string '{$stringid}' used by badge_config::combinator_options()"
            );
        }
    }

    public function test_name_is_read_from_config(): void {
        $this->resetAfterTest();

        for ($i = 1; $i <= badge_config::MAX_BADGES; $i++) {
            set_config("profilefield{$i}", '', 'block_pin_user');
        }
        set_config('profilefield1', 'sante', 'block_pin_user');
        set_config('badgename1', 'Élève à besoins spécifiques', 'block_pin_user');

        $badges = badge_config::get_global_badges();
        $this->assertSame('Élève à besoins spécifiques', $badges[0]->name);
    }

    public function test_label_prefers_name_over_text_over_fallback(): void {
        $badge = (object) ['index' => 1, 'name' => 'Élève à besoins spécifiques', 'text' => 'EBS'];
        $this->assertSame('Élève à besoins spécifiques', badge_config::label($badge));

        $badgenoname = (object) ['index' => 1, 'name' => '', 'text' => 'EBS'];
        $this->assertSame('EBS', badge_config::label($badgenoname));

        $badgeneither = (object) ['index' => 3, 'name' => '', 'text' => ''];
        $this->assertSame(get_string('badgesettings', 'block_pin_user', 3), badge_config::label($badgeneither));
    }
}
