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
 * Bundles a single badge's optional "second condition" (combinator, profile
 * field, condition, value) into one settings row, rendered as a native
 * HTML5 <details>/<summary> disclosure widget instead of four separate,
 * always-visible rows.
 *
 * Deliberately built with plain HTML <select>/<input> elements and no
 * JavaScript: <details>/<summary> natively collapses and expands in every
 * modern browser, and its contents are always part of the form submission
 * (even while collapsed), so nothing is lost by leaving it closed. This
 * avoids depending on Moodle's internal theme/DOM structure for any
 * show/hide behaviour, which cannot be reliably predicted without a live
 * site to test against.
 *
 * The four underlying values are still stored under their original config
 * keys (combinator{n}, profilefield{n}b, profilefield{n}bcondition,
 * profilefield{n}bvalue), exactly as when they were four separate
 * admin_setting objects - so this is a presentation-only change with zero
 * impact on already-configured badges.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_second_condition extends \admin_setting {
    /** @var int The badge number (1-based) this widget belongs to. */
    private int $badgeindex;

    /** @var array Shared "profile field" dropdown options (shortname => label). */
    private array $fieldoptions;

    /** @var array Shared condition dropdown options (key => label). */
    private array $conditionoptions;

    /** @var array Shared combinator dropdown options (key => label). */
    private array $combinatoroptions;

    /**
     * Constructor.
     *
     * @param int   $badgeindex        The badge number (1-based).
     * @param array $fieldoptions      Profile field dropdown options, shortname => label.
     * @param array $conditionoptions  Condition dropdown options, key => label.
     * @param array $combinatoroptions Combinator dropdown options, key => label.
     */
    public function __construct(int $badgeindex, array $fieldoptions, array $conditionoptions, array $combinatoroptions) {
        $this->badgeindex = $badgeindex;
        $this->fieldoptions = $fieldoptions;
        $this->conditionoptions = $conditionoptions;
        $this->combinatoroptions = $combinatoroptions;

        parent::__construct(
            "block_pin_user/secondcondition{$badgeindex}",
            get_string('secondcondition', 'block_pin_user'),
            get_string('secondcondition_desc', 'block_pin_user'),
            ''
        );
    }

    /**
     * Reads the four underlying values directly from config.
     *
     * @return array{combinator: string, profilefieldb: string, conditionb: string, valueb: string}
     */
    public function get_setting() {
        $i = $this->badgeindex;
        return [
            'combinator' => (string) get_config('block_pin_user', "combinator{$i}"),
            'profilefieldb' => (string) get_config('block_pin_user', "profilefield{$i}b"),
            'conditionb' => (string) get_config('block_pin_user', "profilefield{$i}bcondition"),
            'valueb' => (string) get_config('block_pin_user', "profilefield{$i}bvalue"),
        ];
    }

    /**
     * The default value for all four underlying fields: a single condition only.
     *
     * @return array{combinator: string, profilefieldb: string, conditionb: string, valueb: string}
     */
    public function get_defaultsetting() {
        return [
            'combinator' => 'and',
            'profilefieldb' => '',
            'conditionb' => 'isnotempty',
            'valueb' => '',
        ];
    }

    /**
     * Validates and stores the four underlying values under their original config keys.
     *
     * Defensive by design: an unexpected submitted shape (e.g. not an array)
     * is silently ignored rather than raising an error, since this plugin
     * cannot be tested against every possible Moodle/theme combination.
     *
     * @param mixed $data The submitted value: expected to be an array with
     *                     'combinator', 'profilefieldb', 'conditionb', 'valueb' keys.
     * @return string Empty string (no error is ever reported to the admin here).
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            return '';
        }

        $i = $this->badgeindex;

        $combinator = isset($data['combinator']) ? clean_param($data['combinator'], PARAM_ALPHA) : 'and';
        if (!array_key_exists($combinator, $this->combinatoroptions)) {
            $combinator = 'and';
        }

        $profilefieldb = isset($data['profilefieldb']) ? clean_param($data['profilefieldb'], PARAM_TEXT) : '';
        if (!array_key_exists($profilefieldb, $this->fieldoptions)) {
            $profilefieldb = '';
        }

        $conditionb = isset($data['conditionb']) ? clean_param($data['conditionb'], PARAM_ALPHA) : 'isnotempty';
        if (!array_key_exists($conditionb, $this->conditionoptions)) {
            $conditionb = 'isnotempty';
        }

        $valueb = isset($data['valueb']) ? clean_param($data['valueb'], PARAM_TEXT) : '';

        set_config("combinator{$i}", $combinator, 'block_pin_user');
        set_config("profilefield{$i}b", $profilefieldb, 'block_pin_user');
        set_config("profilefield{$i}bcondition", $conditionb, 'block_pin_user');
        set_config("profilefield{$i}bvalue", $valueb, 'block_pin_user');

        return '';
    }

    /**
     * Renders the collapsed-by-default <details> widget with the four fields inside.
     *
     * @param mixed  $data  The current value (see get_setting()), or submitted data on redisplay.
     * @param string $query Search query used to highlight matches on the admin search page.
     * @return string HTML for this settings row.
     */
    public function output_html($data, $query = '') {
        if (!is_array($data)) {
            $data = $this->get_defaultsetting();
        }

        $fullname = $this->get_full_name();

        $combinatorselect = \html_writer::select(
            $this->combinatoroptions,
            "{$fullname}[combinator]",
            $data['combinator'] ?? 'and',
            false,
            ['class' => 'form-control', 'id' => "{$fullname}_combinator"]
        );
        $fieldbselect = \html_writer::select(
            $this->fieldoptions,
            "{$fullname}[profilefieldb]",
            $data['profilefieldb'] ?? '',
            false,
            ['class' => 'form-control', 'id' => "{$fullname}_profilefieldb"]
        );
        $conditionbselect = \html_writer::select(
            $this->conditionoptions,
            "{$fullname}[conditionb]",
            $data['conditionb'] ?? 'isnotempty',
            false,
            ['class' => 'form-control', 'id' => "{$fullname}_conditionb"]
        );
        $valuebinput = \html_writer::empty_tag('input', [
            'type' => 'text',
            'name' => "{$fullname}[valueb]",
            'value' => $data['valueb'] ?? '',
            'class' => 'form-control',
            'id' => "{$fullname}_valueb",
        ]);

        $rows = $this->field_row(
            "{$fullname}_combinator",
            get_string('combinator', 'block_pin_user'),
            $combinatorselect,
            get_string('combinator_desc', 'block_pin_user')
        );
        $rows .= $this->field_row(
            "{$fullname}_profilefieldb",
            get_string('profilefieldb', 'block_pin_user'),
            $fieldbselect,
            get_string('profilefieldb_desc', 'block_pin_user')
        );
        $rows .= $this->field_row(
            "{$fullname}_conditionb",
            get_string('conditionb', 'block_pin_user'),
            $conditionbselect,
            get_string('conditionb_desc', 'block_pin_user')
        );
        $rows .= $this->field_row(
            "{$fullname}_valueb",
            get_string('conditionvalueb', 'block_pin_user'),
            $valuebinput,
            get_string('conditionvalueb_desc', 'block_pin_user')
        );

        $detailsattrs = ['class' => 'block_pin_user-secondcondition'];
        if (!empty($data['profilefieldb'])) {
            // Already configured on this badge: start expanded so the admin
            // doesn't think their setting disappeared.
            $detailsattrs['open'] = 'open';
        }

        $summary = \html_writer::tag('summary', get_string('secondcondition_toggle', 'block_pin_user'));
        $body = \html_writer::div($rows, 'block_pin_user-secondcondition-fields');
        $content = \html_writer::tag('details', $summary . $body, $detailsattrs);

        return format_admin_setting($this, $this->visiblename, $content, $this->description, false, '', null, $query);
    }

    /**
     * Renders one labelled field row inside the disclosure widget.
     *
     * @param string $forid  The id of the form control this label is for.
     * @param string $label  The field label.
     * @param string $control The rendered HTML form control.
     * @param string $desc   A short help text shown under the control.
     * @return string
     */
    private function field_row(string $forid, string $label, string $control, string $desc): string {
        $labeltag = \html_writer::tag('label', s($label), ['for' => $forid, 'class' => 'block_pin_user-secondcondition-label']);
        $desctag = \html_writer::tag('p', s($desc), ['class' => 'block_pin_user-secondcondition-desc']);
        return \html_writer::div($labeltag . $control . $desctag, 'block_pin_user-secondcondition-row');
    }
}
