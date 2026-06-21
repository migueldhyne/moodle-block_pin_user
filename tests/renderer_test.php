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
 * Unit tests for the block_pin_user renderer.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_pin_user_renderer
 */
final class renderer_test extends \advanced_testcase {
    /**
     * Builds a minimal participant row, including the additional name
     * fields fullname() expects to find on the user object (firstnamephonetic,
     * lastnamephonetic, middlename, alternatename) - without them, fullname()
     * triggers a debugging() call asking the caller to update their SQL.
     *
     * @param array $overrides
     * @return \stdClass
     */
    private function make_user(array $overrides = []): \stdClass {
        $user = (object) [
            'id' => 1,
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'firstnamephonetic' => '',
            'lastnamephonetic' => '',
            'middlename' => '',
            'alternatename' => '',
        ];
        foreach ($overrides as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    /**
     * Builds a single badge config object.
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
            'icon' => '',
            'text' => 'Allergies',
            'bgcolor' => '#1e7e34',
            'color' => '#ffffff',
        ];
        foreach ($overrides as $key => $value) {
            $badge->$key = $value;
        }
        return $badge;
    }

    public function test_badge_shown_when_condition_met(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        $user = $this->make_user(['udf_profilefield1_value' => 'arachides']);

        $html = $renderer->render_participant_with_pin($user, 2, [$this->make_badge()]);

        $this->assertStringContainsString('custom-badge1', $html);
        $this->assertStringContainsString('Allergies', $html);
    }

    public function test_badge_hidden_when_field_not_configured(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        // No 'udf_profilefield1_value' property at all == badge disabled,
        // regardless of what the condition would otherwise evaluate to.
        $user = $this->make_user();

        $html = $renderer->render_participant_with_pin($user, 2, [$this->make_badge()]);

        $this->assertStringNotContainsString('custom-badge1', $html);
    }

    public function test_multiple_badges_render_independently(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        $user = $this->make_user([
            'udf_profilefield1_value' => 'present',
            'udf_profilefield2_value' => '',
        ]);

        $badges = [
            $this->make_badge(['index' => 1, 'text' => 'PAI']),
            $this->make_badge(['index' => 2, 'text' => 'Boursier', 'condition' => 'isnotempty']),
        ];

        $html = $renderer->render_participant_with_pin($user, 2, $badges);

        $this->assertStringContainsString('custom-badge1', $html);
        $this->assertStringContainsString('PAI', $html);
        $this->assertStringNotContainsString('custom-badge2', $html);
        $this->assertStringNotContainsString('Boursier', $html);
    }

    public function test_icon_only_badge_gets_accessible_label(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        $user = $this->make_user(['udf_profilefield1_value' => 'present']);

        $badge = $this->make_badge(['text' => '', 'icon' => '⚠️']);
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);

        $this->assertStringContainsString('⚠️', $html);
        $this->assertStringContainsString('aria-label', $html);
    }

    public function test_badge_text_is_html_escaped(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        $user = $this->make_user(['udf_profilefield1_value' => 'present']);

        $badge = $this->make_badge(['text' => '<script>alert(1)</script>']);
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_second_condition_disabled_behaves_like_single_condition(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        // Second condition left empty: even though udf_profilefield1b_value would
        // fail an 'equals' check, it must be ignored entirely.
        $user = $this->make_user(['udf_profilefield1_value' => 'present']);

        $badge = $this->make_badge(); // Second field ('profilefieldb') is '' by default.
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);

        $this->assertStringContainsString('custom-badge1', $html);
    }

    public function test_and_combinator_requires_both_conditions(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        $badge = $this->make_badge([
            'profilefieldb' => 'niveau',
            'conditionb' => 'equals',
            'valueb' => 'priorite',
            'combinator' => 'and',
        ]);

        // Condition A true, condition B false -> AND must hide the badge.
        $user = $this->make_user([
            'udf_profilefield1_value' => 'present',
            'udf_profilefield1b_value' => 'standard',
        ]);
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);
        $this->assertStringNotContainsString('custom-badge1', $html);

        // Both conditions true -> AND must show the badge.
        $user->udf_profilefield1b_value = 'priorite';
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);
        $this->assertStringContainsString('custom-badge1', $html);
    }

    public function test_or_combinator_needs_only_one_condition(): void {
        $this->resetAfterTest();
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_pin_user');

        $badge = $this->make_badge([
            'condition' => 'isnotempty',
            'profilefieldb' => 'niveau',
            'conditionb' => 'equals',
            'valueb' => 'priorite',
            'combinator' => 'or',
        ]);

        // Condition A false, condition B true -> OR must still show the badge.
        $user = $this->make_user([
            'udf_profilefield1_value' => '',
            'udf_profilefield1b_value' => 'priorite',
        ]);
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);
        $this->assertStringContainsString('custom-badge1', $html);

        // Both conditions false -> OR must hide the badge.
        $user->udf_profilefield1b_value = 'standard';
        $html = $renderer->render_participant_with_pin($user, 2, [$badge]);
        $this->assertStringNotContainsString('custom-badge1', $html);
    }
}
