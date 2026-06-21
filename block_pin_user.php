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
 * Block definition class for the block_pin_user plugin.
 *
 * @package   block_pin_user
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block plugin to display pinned user information on the course participants page.
 *
 * This block shows a list of enrolled users in a course along with customized
 * badges based on specific user profile fields. It is only visible to users who
 * have the block/pin_user:viewbadges capability and is only displayed on the
 * participants page.
 */
class block_pin_user extends block_base {
    /**
     * Initializes the block's title using the localized plugin name.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_pin_user');
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Restricts where this block can be added.
     *
     * The block only ever renders content on the course participants page, so
     * there is no point letting admins/teachers add it to the dashboard, the
     * front page, or inside an activity - it would just sit there empty and
     * be confusing. Restricting it here keeps the block picker UI honest.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'all' => false,
            'course-view' => true,
        ];
    }

    /**
     * A single instance of this block only makes sense once per course.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Generates the content of the block to be displayed on the participants page.
     *
     * The content is only shown if the user has the block/pin_user:viewbadges
     * capability and if the current page is the course participants page.
     * It displays a paginated list of actively enrolled participants with
     * badges based on the profile fields configured in the plugin settings
     * (see \block_pin_user\badge_config::MAX_BADGES for the maximum count).
     *
     * @return stdClass|null Object containing the HTML content to display, or null if not applicable.
     */
    public function get_content() {
        global $DB, $COURSE, $OUTPUT;

        // Ensure content is only generated once.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id);

        // Check capability. Uses a capability dedicated to this block rather than
        // a loosely related core capability, so site admins can grant/restrict
        // visibility of these (potentially sensitive) badges independently of
        // who can manage course activities.
        if (!has_capability('block/pin_user:viewbadges', $context)) {
            $this->content = null;
            return $this->content;
        }

        // Check for participants page.
        if (
            $this->page->pagetype !== 'course-participants'
            && !$this->page->url->compare(new moodle_url('/user/index.php'), URL_MATCH_BASE)
        ) {
            $this->content = null;
            return $this->content;
        }

        // Pagination parameters.
        $page    = optional_param('page', 0, PARAM_INT);
        $perpage = 25;

        // Only configured badges are returned here - an unconfigured badge
        // (no profile field selected) never even appears in this list,
        // instead of accidentally matching everyone via "is empty".
        $badges = \block_pin_user\badge_config::get_global_badges();

        [$sql, $countsql, $params, $enrolparams] = \block_pin_user\participant_loader::build_sql($context, $badges);

        $totalcount = $DB->count_records_sql($countsql, $enrolparams);
        $participants = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

        // Prepare renderer and paging controls.
        $renderer = $this->page->get_renderer('block_pin_user');
        $baseurl  = $this->page->url;
        $paging   = $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);

        // The badge colours are admin-configurable, so they are written out
        // as a small inline style block here instead of going through a
        // separate dynamic css.php endpoint: one fewer HTTP request per page
        // load, no extra caching headers to maintain, and the colours are
        // defensively stripped down to safe characters before being echoed.
        $this->content->text .= $this->badge_style($badges);
        $this->content->text .= $this->export_links($COURSE->id, $badges);
        $this->content->text .= $paging;
        foreach ($participants as $participant) {
            $this->content->text .= $renderer->render_participant_with_pin($participant, $COURSE->id, $badges);
        }
        $this->content->text .= $paging;

        return $this->content;
    }

    /**
     * Builds the "Export" links shown above the participant list: one for
     * every active badge (CSV of just that badge's matching participants),
     * plus one combined export with every badge as a column.
     *
     * @param int          $courseid The course id, used to build the export URL.
     * @param \stdClass[]  $badges   The active badge config objects.
     * @return string HTML, or '' if there is nothing to export.
     */
    private function export_links(int $courseid, array $badges): string {
        if (empty($badges)) {
            return '';
        }

        $links = [];

        $allurl = new moodle_url('/blocks/pin_user/export.php', [
            'courseid' => $courseid,
            'sesskey' => sesskey(),
        ]);
        $links[] = html_writer::link($allurl, get_string('exportall', 'block_pin_user'));

        foreach ($badges as $badge) {
            $url = new moodle_url('/blocks/pin_user/export.php', [
                'courseid' => $courseid,
                'badge' => $badge->index,
                'sesskey' => sesskey(),
            ]);
            $links[] = html_writer::link($url, s(\block_pin_user\badge_config::label($badge)));
        }

        return html_writer::tag(
            'div',
            get_string('exportlabel', 'block_pin_user') . ' ' . implode(' · ', $links),
            ['class' => 'block_pin_user-export']
        );
    }

    /**
     * Builds a small inline <style> block applying the admin-configured badge colours.
     *
     * @param \stdClass[] $badges The active badge config objects.
     * @return string HTML <style> element.
     */
    private function badge_style(array $badges): string {
        $css = '';
        foreach ($badges as $badge) {
            $bg = $this->sanitise_css_colour($badge->bgcolor, '#1e7e34');
            $color = $this->sanitise_css_colour($badge->color, '#ffffff');
            $css .= ".block_pin_user .custom-badge{$badge->index} { background-color: {$bg}; color: {$color}; }";
        }
        return html_writer::tag('style', $css);
    }

    /**
     * Defensively restricts a colour setting to characters that are valid in a
     * CSS colour value (hex codes or CSS colour keywords), falling back to a
     * safe default otherwise. admin_setting_configcolourpicker already
     * validates its input on save, this is a defence-in-depth second check
     * since the value is echoed directly into a <style> tag.
     *
     * @param mixed  $value   The raw config value.
     * @param string $default Fallback if the value doesn't look like a valid colour.
     * @return string
     */
    private function sanitise_css_colour($value, string $default): string {
        $value = trim((string) $value);

        // Hex colour, with or without the leading '#' (3 to 8 hex digits).
        if (preg_match('/^#?[0-9a-fA-F]{3,8}$/', $value)) {
            return ($value[0] === '#') ? $value : ('#' . $value);
        }

        // CSS named colour (e.g. "white", "navy").
        if (preg_match('/^[a-zA-Z]{3,20}$/', $value)) {
            return $value;
        }

        return $default;
    }
}
