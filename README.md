# block_pin_user

Moodle block that displays, on a course's **Participants** page, the list of
active enrolled users with up to six conditional badges based on custom
profile fields (e.g. allergies, special accommodations, status, etc.).

## Installation

1. Copy this folder into `blocks/pin_user` of your Moodle installation.
2. Go to *Site administration > Notifications* to complete the
   installation.
3. Add the "Pin User" block to a course's Participants page (the block can
   only be added within a course context).

## Configuration

Settings live under *Site administration > Blocks > Pin User*, on a single
page.

For each badge, the main condition (field, condition, value) is always
visible. The **optional second condition (AND/OR)** is collapsed by default
behind a small **"Combine with a second condition (AND/OR)"** link, right
below the comparison value field — one click expands it. If a badge already
has a second condition configured, that section is shown expanded by
default. This collapsing uses the native HTML `<details>`/`<summary>`
element: no JavaScript is required, and the values are still submitted with
the form even while the section is collapsed.

You can configure up to **6 badges** (the `MAX_BADGES` constant in
`classes/badge_config.php`, which a developer can change if needed). Any
unused badge can simply be left on **"None"** — it then appears nowhere,
neither on the settings page once saved nor in the rendered block.

For each badge, you choose:

- **Badge name** (optional): a clearer name than the text shown on the
  badge itself, used in export links and CSV headers (e.g. "Special
  educational needs" while the on-screen badge just shows "SEN"). Leave
  empty to reuse the badge text instead.
- **Profile field**: a dropdown listing the custom profile fields that
  actually exist on the site. The **"None"** value disables the badge
  entirely — this is also the default, so a badge never shows up without
  having been explicitly configured.
- **Condition**: is empty / is not empty / equals / contains / does not
  contain.
- **Comparison value**: used by "equals", "contains" and "does not
  contain".
- **Additional condition (optional, collapsed by default)**: a second
  profile field, with its own condition and value, combined with the first
  via **AND** or **OR**. Leave this second field on "None" (the default) to
  use a single condition only — this is the plugin's original behaviour,
  guaranteed unchanged for any badge already configured.
- **Icon** (optional): chosen from a list of common emoji
  (⚠️ ❤️ ✚ ♿ ⭐ 🚩 ℹ️ ✅ 🔔 🔒). Deliberately plain Unicode characters rather
  than Font Awesome or Moodle icons (`pix_icon`): their rendering doesn't
  depend on the theme or the Moodle version, unlike a class name or icon
  identifier.
- **Text**: can be left empty if the icon is enough on its own (an
  icon-only badge stays accessible: a label is automatically announced to
  screen readers).
- Badge **colours**.

> **Backward compatibility**: badges 1 and 2 use exactly the same setting
> names as in previous versions (`profilefield1`, `text1`, etc.). If you
> are upgrading from an earlier version, your existing configuration is
> kept as-is — nothing to reconfigure.

### Creating a new profile field without leaving the page

At the top of the settings page, a **"Manage custom profile fields"**
button opens Moodle's own admin page for this (`/user/profile/index.php`)
in a new tab, along with a reminder of the fields that already exist. Once
the field is created, come back to this tab and refresh the settings page:
it will appear in the dropdowns.

> This plugin does not reimplement profile field creation in its own
> interface: Moodle already has a complete, maintained form for that.
> Duplicating that functionality would add maintenance overhead and a risk
> of drifting out of sync with core on every new Moodle version, for
> limited benefit over a simple direct link.

## Permissions

Two capabilities control two different things:

| Capability | Controls | Default |
|---|---|---|
| `block/pin_user:addinstance` / `:myaddinstance` | Who can **add** the block to a course page / to My Moodle. | Teacher (editing), Manager |
| `block/pin_user:viewbadges` | Who can **see the block's content** (the list + the badges), once it has been added. | Teacher (editing), Manager |

These are two independent gates: being allowed to add the block does not
automatically grant the right to see its content, and vice versa.

`viewbadges` is a dedicated capability, separate from
`moodle/course:manageactivities` (used by v1.0), so that badge visibility
can be restricted independently of course-management permissions — useful
when the displayed profile fields are sensitive.

### On a fresh install

Nothing to do: Moodle reads `db/access.php` at install time and
automatically grants both capabilities to the Teacher and Manager roles.

### When upgrading from v1.0

⚠️ An important point that's easy to miss:

- **`viewbadges` (new capability)** → Moodle creates it and automatically
  grants it to the Teacher/Manager roles during the upgrade. Nothing to do,
  unless you also want to grant it to another role.
- **`addinstance` / `myaddinstance` (already-existing capabilities)** →
  v2.0.0 corrects the default archetype (`teacher` → `editingteacher`, i.e.
  the standard Teacher role rather than the non-editing Teacher role).
  **Moodle does not automatically re-apply this change** to capabilities
  already present in the database — this is intentional, so as not to
  overwrite permissions you may have customised. If you are upgrading from
  v1.0, manually check/grant these two capabilities to the Teacher role:

  *Site administration → Users → Permissions → Define roles → Teacher →
  search for `block/pin_user:addinstance` and `block/pin_user:myaddinstance`
  → Allow.*

Two reminders are built into the plugin so this doesn't go unnoticed:
- A warning message is shown automatically right after the upgrade **if it
  is performed through the web UI** (`db/upgrade.php`). It will not be
  shown for a command-line upgrade (`admin/cli/upgrade.php`).
- A permanent reminder appears at the top of the plugin's settings page,
  with a direct shortcut to *Define roles*, visible regardless of how the
  upgrade was performed.

## ⚠️ Note on sensitive data

The profile fields used by this block may contain sensitive information
(e.g. health information). The block does not **store** any data of its
own (see `classes/privacy/provider.php`), but it does **display** that
information to anyone holding the `block/pin_user:viewbadges` capability.
Make sure to review who holds this capability on your site before using
this plugin with sensitive fields.

## CSV export

An **"Export"** link appears above the participant list, visible to anyone
holding `block/pin_user:viewbadges` (the same right needed to see the
badges on screen):

- **All participants (CSV)**: name, email, then one column per configured
  badge, containing **the actual profile field value** when the badge
  applies (e.g. "Peanuts, gluten"), "Yes" if the badge applies but the
  field itself is empty (an "is empty" condition), or a blank cell if the
  badge does not apply.
- **One link per badge** (label = the badge's name): only the participants
  for whom that specific badge applies, with a "Value" column (and a
  second column if an additional condition is configured for that badge).

The file is UTF-8 encoded with a BOM and uses a semicolon as the
separator (the convention for Excel in French locales). The export reuses
the exact same matching logic as the on-screen display (the
`badge_matcher` class), so the exported list can never drift out of sync
with what the block shows.

> ⚠️ This file may contain sensitive data (the same data shown in the
> badges). Handle it according to your institution's data protection
> rules.

## Tests

```
vendor/bin/phpunit --filter block_pin_user
```

`tests/condition_evaluator_test.php` covers the badge condition logic in
isolation. `tests/renderer_test.php` checks the HTML output, including
badge text escaping.

## Changelog

### v2.5.1
- Reverted the "separate page" approach from v2.5.0, which turned out to
  be hard to follow: each badge's second condition (AND/OR) is now a small
  collapsible link (`<details>`/`<summary>`, no JavaScript) right below the
  comparison value field, instead of living on a separate page.
- No impact on existing configuration.
- Added tests for this new component's validation/storage logic.

### v2.4.1
- Fixes following a pass with the Moodle Code Checker:
  - Language strings put back into strict alphabetical order (the
    `*_button` / `*_desc` pairs were not correctly ordered).
  - Removed `defined('MOODLE_INTERNAL') || die();` where it is no longer
    needed (autoloaded classes, `lib.php`, `db/upgrade.php`).
  - Completed docblocks (missing description on several methods).
  - Inline comments capitalised at the start of the sentence.

### v2.4.0
- Added an optional "Badge name" field, separate from the text shown on
  the badge, used for export links and CSV headers.
- Each settings section's heading now shows this name once configured, to
  make it easier to find your way around a long settings page.

### v2.3.1
- The CSV export now shows the actual profile field value (e.g. "Peanuts,
  gluten") instead of a plain Yes/No, falling back to "Yes" when the badge
  matches but the field itself is empty.
- The single-badge export gains a "Value" column (and a second column if
  an additional condition is configured).

### v2.3.0
- Added CSV export: all participants (with one column per badge), or just
  those matching one specific badge.
- Protected by the same capability as the badge display, plus a sesskey
  check.
- Refactor: the badge-matching logic (`badge_matcher`) and the SQL query
  builder (`participant_loader`) are now shared between the on-screen
  display and the export, so they can never drift apart.

### v2.2.0
- Each badge can now combine a second condition (field + condition +
  value) with the first, via AND/OR.
- Fully optional: a badge with no second field configured (i.e. every
  badge that existed before this feature) behaves exactly as before.
- Added tests for the AND/OR combinations.

### v2.1.0
- Generalised to a configurable number of badges (up to 6, instead of a
  fixed 2), via native settings fields (no JSON, no JavaScript).
- Added an optional icon per badge (curated Unicode emoji list).
- Badges 1 and 2 keep their original setting key names: no reconfiguration
  needed after upgrading.
- Added tests for `badge_config` and updated the renderer tests.

### v2.0.0
- Fix: the two badges no longer show up for every participant by default
  on a fresh install.
- New dedicated `block/pin_user:viewbadges` capability.
- The query now only shows active enrolments (relies on
  `get_enrolled_sql()` instead of manual joins).
- Badge text escaping (fixed a potential stored XSS vulnerability).
- Reviewed default badge colours for WCAG AA contrast compliance.
- Removed the `css.php` endpoint in favour of a short inline `<style>`
  block (one fewer HTTP request per page, no more cache maintenance).
- Profile field selection via dropdown (instead of a free-text field),
  with a direct link to the Moodle profile fields admin page.
- Added PHPUnit unit tests.
- Added an automatic warning on upgrade (`db/upgrade.php`) and a permanent
  reminder in the settings page about the "Permissions" point above.
