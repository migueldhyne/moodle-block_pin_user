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
 * Unit tests for \block_pin_user\condition_evaluator.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_pin_user\condition_evaluator
 */
final class condition_evaluator_test extends \advanced_testcase {

    /**
     * Tests condition_evaluator::evaluate() against a range of inputs.
     *
     * @dataProvider evaluate_provider
     */
    public function test_evaluate(?string $fieldvalue, string $condition, string $expected, bool $result): void {
        $this->assertSame($result, condition_evaluator::evaluate($fieldvalue, $condition, $expected));
    }

    /**
     * Data provider for test_evaluate.
     *
     * @return array
     */
    public static function evaluate_provider(): array {
        return [
            'isempty - null value' => [null, 'isempty', '', true],
            'isempty - empty string' => ['', 'isempty', '', true],
            'isempty - non-empty value' => ['allergie', 'isempty', '', false],

            'isnotempty - null value' => [null, 'isnotempty', '', false],
            'isnotempty - non-empty value' => ['allergie', 'isnotempty', '', true],

            'equals - exact match' => ['oui', 'equals', 'oui', true],
            'equals - mismatch' => ['non', 'equals', 'oui', false],
            'equals - null value never matches' => [null, 'equals', '', false],

            'contains - match' => ['arachides, gluten', 'contains', 'gluten', true],
            'contains - no match' => ['arachides', 'contains', 'gluten', false],
            'contains - null value never matches' => [null, 'contains', 'gluten', false],
            'contains - empty expected never matches' => ['arachides', 'contains', '', false],

            'notcontains - match (absent)' => ['arachides', 'notcontains', 'gluten', true],
            'notcontains - no match (present)' => ['arachides, gluten', 'notcontains', 'gluten', false],
            'notcontains - null value counts as not containing' => [null, 'notcontains', 'gluten', true],

            'unknown condition is always false' => ['anything', 'bogus-condition', 'x', false],
        ];
    }
}
