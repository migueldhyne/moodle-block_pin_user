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
 * Upgrade steps for the block_pin_user plugin.
 *
 * @package   block_pin_user
 * @copyright 2026, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Runs the upgrade steps for block_pin_user.
 *
 * This function is only ever called by Moodle when an *existing* installation
 * of the plugin is being upgraded - it is never called on a fresh install, so
 * there is no need to guard against $oldversion being 0.
 *
 * @param int $oldversion The previously installed version of this plugin.
 * @return bool
 */
function xmldb_block_pin_user_upgrade($oldversion) {

    if ($oldversion < 2026062100) {
        // Version 2.0.0 introduces a dedicated block/pin_user:viewbadges capability
        // and corrects the default archetype used by addinstance/myaddinstance
        // (it was 'teacher', now 'editingteacher'). Moodle automatically
        // creates and assigns brand-new capabilities on upgrade, but it does
        // NOT retroactively re-apply changed archetype defaults to
        // capabilities that already existed before this upgrade. This notice
        // only appears if the upgrade is run through the web UI (it will not
        // be visible for a CLI upgrade) - see also the permanent reminder on
        // this plugin's settings page.
        \core\notification::warning(get_string('upgradenotice_v2', 'block_pin_user'));

        upgrade_block_savepoint(true, 2026062100, 'pin_user');
    }

    if ($oldversion < 2026062101) {
        // Version 2.1.0: badges are no longer hardcoded to exactly two. Existing
        // badge 1 and badge 2 settings are kept under their original config
        // keys, so nothing is lost or needs reconfiguring - this is purely
        // informational.
        \core\notification::info(get_string('upgradenotice_v3', 'block_pin_user', \block_pin_user\badge_config::MAX_BADGES));

        upgrade_block_savepoint(true, 2026062101, 'pin_user');
    }

    if ($oldversion < 2026062102) {
        // Version 2.2.0: each badge can optionally combine a second condition (AND/OR)
        // with its first. Opt-in only - badges with no second field configured
        // (the default, and every badge set up before this feature existed)
        // behave exactly as before.
        \core\notification::info(get_string('upgradenotice_v4', 'block_pin_user'));

        upgrade_block_savepoint(true, 2026062102, 'pin_user');
    }

    if ($oldversion < 2026062103) {
        // Version 2.3.0: adds a CSV export link above the participant list.
        \core\notification::info(get_string('upgradenotice_v5', 'block_pin_user'));

        upgrade_block_savepoint(true, 2026062103, 'pin_user');
    }

    if ($oldversion < 2026062107) {
        // Version 2.5.x: each badge's optional second condition (AND/OR) is now
        // a small collapsible "Combine with a second condition" toggle, right
        // below that badge's comparison value field, instead of always being
        // shown. The underlying config values are untouched (a config value is
        // keyed by plugin + setting name, not by which admin_setting renders it).
        \core\notification::info(get_string('upgradenotice_v6', 'block_pin_user'));

        upgrade_block_savepoint(true, 2026062107, 'pin_user');
    }

    return true;
}
