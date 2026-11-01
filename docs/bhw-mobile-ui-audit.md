# BHW mobile UI audit

This pass adds `public/css/bhw/mobile.css` and `public/js/bhw-mobile.js` through
the BHW layout only. All new styles are inside `screen and (max-width: 768px)`;
the stylesheet link also carries that media restriction. Print layouts, routes,
controllers, authentication and demographic calculations are unchanged.

## Findings and fixes

| Area | Cause | Mobile correction |
| --- | --- | --- |
| Navigation | Drawer could cover content without an outside-close target; background remained interactive | Backdrop, background scroll lock, inert content, keyboard focus containment, Escape/outside-click close, close after navigation and before logout confirmation |
| Navigation state | Child pages did not identify their parent navigation link | Mobile active indicators for exact routes and related subpages |
| Dashboard and citizen management | Existing recent fixes already addressed the principal layout issues | Audited alongside the remaining templates; desktop chart dimensions retained |
| Clinical details and health records | Nonwrapping count badges, narrow panel headers and conflicting form flex sizing | Wrapping headers/badges, stacked filter controls, responsive vitals/form grids |
| Families | Registration/edit/member rows can share insufficient width; native dialogs must fit short screens | Single-column mobile form rows, wrapping dialog controls, viewport height constraints |
| Reports and report builder | Several preview tables lack independent scroll regions | Focusable table-only scroll wrappers, stacked configuration fields; wide detail tables retain their minimum width |
| Analytics | Two-column layout persists between 620px and 768px; ranking chart height grows with row count | Single-column panels and filters; bounded chart region with scrolling for long rankings, bounded map height |
| Supplies and batch release | Assignment table escaped its container; narrow multi-field assignment rows | Table-only scrolling, stacked assignment fields, bounded deposit/withdraw/confirmation dialogs |
| Calendar | Shared data-table minimum width can affect generated calendar tables; schedule forms use multiple columns | Calendar table minimum reset, wrapping toolbar, single-column schedule fields |
| Logs | Tab tables can extend beyond the page | Independent table scrolling and wrapping pagination |
| Referrals | Wide records table and two-column form need bounded mobile layouts | Scrollable records, stacked fields and accessible action sizes |
| Announcements | Image comparison grids have minimum column widths; detail and confirmation dialogs need short-screen bounds | Single-column images/comparisons, wrapping actions, scrollable dialogs |
| QR checker | Form/control sizing and nested page padding reduce usable space | Stacked fields, reduced nested padding and mobile control sizing; scanner behavior unchanged |
| Archive | Card hides overflow around its table | Table-only scroll region, visible outer card, wrapping tabs and pagination |
| Settings | Nested page padding and appearance/security controls crowd small screens | Reduced nested padding, stacked appearance controls, wrapping security rows |
| Themes | Some page-specific dialog surfaces are absent from shared appearance selectors | Mobile-only additions using the existing theme variables |

Table wrappers are added only on small screens, include keyboard focus and region
labels, and are removed when resizing above 768px. Calendar-generated tables and
the dashboard's existing stacked referral table are excluded. Dynamic tables
inserted by existing page scripts are also handled.

## Verification

`tests/browser/bhw-mobile-audit.cjs` generates offline browser fixtures from all
23 BHW Blade templates plus Archive and Settings. It uses the actual local CSS,
markup, sidebar handlers and new mobile script, with sample text replacing Blade
expressions. No account, production data or external service is needed.

- 350 layout fixtures: 25 templates × seven widths × both themes.
- Widths: 320, 390, 600, 768, 820, 1024 and 1440px.
- The 768px fixtures use a 390px-high landscape viewport.
- 200 mobile fixtures: no detected element overflow outside intentional scroll
  regions; drawer open/background lock, Escape and backdrop close checks pass.
- Mobile dialog boxes are forced visible to check viewport dimensions, including
  short-screen cases. This checks sizing, not each dialog's submission logic.
- 150 desktop fixtures: computed layout/style hashes match the pre-change
  baseline at 820, 1024 and 1440px in both themes.
- JavaScript syntax, Blade compilation, whitespace checks and the five
  `BhwRouteConnectionsTest` cases pass.

Run with an installed Chromium-based browser (defaults to Edge on Windows):

```powershell
node tests/browser/bhw-mobile-audit.cjs --output "$env:TEMP/bhw-mobile-audit.json"
```

Use `BHW_BROWSER` for another browser executable. To compare desktop fixtures,
save the output before a change and pass `--compare <before.json>` afterward.

## Limits and pre-existing issues

Fixtures do not execute the individual page's scripts, authenticate real users,
or load external chart/map/calendar libraries. Live map interactions, ApexCharts
rendering, FullCalendar interactions, QR camera permission/scanning, uploads,
exports, printing and state-changing submissions still need a live-device smoke
test. The fixture checks do not establish a complete visual or accessibility
certification.

During inspection, the existing Analytics script contained a malformed
`new ApexCharts(diagnosisChart, ...)` options argument, and Calendar contained
unresolved merge markers in its inline CSS. These predate this pass and remain
unchanged because repairing them can change desktop rendering or interactions,
which this request explicitly forbids. They can prevent full live-widget
verification even when the mobile container layout is correct.
