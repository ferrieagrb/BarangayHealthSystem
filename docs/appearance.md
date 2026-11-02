# Appearance preferences and dark mode

The four authenticated layouts load the same appearance assets. Theme and text-size
preferences remain on the authenticated `User` model and are saved through the
existing `user.preferences.update` endpoint. No routes, controllers, database
structures, or global setting scopes were changed.

## Implementation and files

- `public/css/theme.css`: shared palette, role-token bridges, dark component styles,
  preference form styling, calendar and SVG chart colors, and screen/print boundaries.
  Existing light styles remain in place. Official health IDs retain their document
  palette and QR image backgrounds; the surrounding record interface uses the theme.
- `public/js/appearance.js`: previews theme and text size in memory, updates root and
  body classes/attributes, restores saved values on Cancel and page-cache navigation,
  scales dynamically inserted text, and recalculates responsive text on resize.
  It sends no requests and writes no localStorage, sessionStorage, or cookies.
- `resources/views/partials/appearance-assets.blade.php`: one asset include used at
  the end of the head in `templates/layout`, `templates/admin`,
  `templates/superadmin`, and `templates/citizen`. Server-rendered root attributes
  select the saved palette before body rendering.
- `resources/views/partials/appearance-settings.blade.php`: personal appearance form
  reused by `settings`, `admin/admin_settings`, and `superadmin/settings`.
  Superadmin's global settings form remains separate.
- `resources/views/citizen/settings.blade.php`: existing form uses the shared
  preview handler and adds Cancel Preview. Its previous theme script and the
  Citizen layout's font script were consolidated into the shared script. Citizen's
  Small/Medium/Large scales remain 0.9/1/1.1; staff scales are 1/1.15/1.3.
- `tests/Feature/AppearancePreferencesTest.php`: preference persistence, account
  isolation, validation, authentication, and representative Blade rendering tests.
- `tests/browser/appearance.cjs`: isolated Edge checks of rendered test pages.

The existing settings do not offer a saved accent preference, so no competing
accent setting was introduced. The Superadmin `allow_dark_mode` flag remains
untouched by personal previews and saves.

## Validation

The focused PHP run passes **14 tests / 391 assertions**:

```powershell
php artisan test --compact --filter='AppearancePreferencesTest|CitizenAnnouncementReturnNavigationTest|AdminCitizenPurgeTest'
```

The appearance tests exercise all four roles, both existing personal and dedicated
settings URLs, saved dark and light states, refresh, logout/login, invalid values,
anonymous requests, and attempts to target another user. The database is SQLite
in memory; no real account or health records are used.

Browser validation passes **309 checks across 47 rendered pages**, at widths
1440, 768, and 390. It checks previews, text sizing, Cancel, no preview writes,
form submissions, refresh, Back navigation, Demographics tabs, and text contrast
in page content and exposed dialog surfaces. Text contrast thresholds are 4.5:1
for normal text and 3:1 for large text. Calendar and chart libraries are supplied
locally during the checks, and screenshot samples are generated in the temporary
fixture directory.

Rendered coverage:

| Role | Pages/components |
| --- | --- |
| All four | Personal Settings; Admin dedicated Settings; Superadmin System Settings |
| BHW | Dashboard, citizen list, registration, citizen details, legacy details, health records, diagnosis/immunization/maintenance sections, analytics, calendar, announcements, announcement creation, QR scanner, referral list/form/legacy page, reports, families, supplies, supply creation, electronic health card, logs, archive |
| Admin | Overview, analytics/reports, users, user creation, supplies, logs, permissions, archive, Demographics Overview/Purok/Subgroups/Map tabs |
| Superadmin | Dashboard, system analytics, connections (including failure state), users, audit logs |
| Citizen | Dashboard with section navigation, health card, health information, referral form, calendar, announcements/list/detail, electronic health card, supplies |

These are actual Blade templates rendered with synthetic data. They are not a
complete end-to-end run against a populated deployment. Production API responses,
every possible record/status combination, camera scanning, external map tiles,
CDN fonts/icons, physical printing, and live service/backup actions were not fully
verified. No form that changes application records is submitted by the browser
harness; preference submissions are intercepted, while the PHP tests cover the
real save endpoint.

The broader `BhwDiseaseMonitoringTest` run has seven failures because its baseline
test schema does not create `system_settings`, which the unchanged favicon partial
queries for `site_logo`. This is outside the appearance implementation; that test
and the favicon partial were left unchanged.

## Reproduce browser checks

Install `playwright-core`, `apexcharts`, `fullcalendar@6.1.15`, and `leaflet` in a
temporary directory (not the application dependency tree), then run:

```powershell
$env:BHEAMS_APPEARANCE_FIXTURES = "$env:TEMP\bheams-theme-check\fixtures"
php artisan test --compact --filter=AppearancePreferencesTest
$env:PLAYWRIGHT_MODULE = "$env:TEMP\bheams-theme-check\node_modules\playwright-core"
node tests/browser/appearance.cjs
```

The harness uses the installed Windows Edge executable by default. Set
`BHEAMS_BROWSER` to use another Chromium executable. Fixture HTML, screenshots,
and `browser-results.json` stay in the temporary fixture directory.

New components should use `--theme-*` or the bridged role variables for their
surfaces, borders, and text. Add new shared component selectors to `theme.css`
instead of creating a separate palette or a second preference storage mechanism.
