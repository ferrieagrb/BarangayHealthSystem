POST-FIX RETEST EVIDENCE
Execution date: 2026-11-19 (Asia/Manila)

Full suite: 202 passed, 0 failures/errors, 1867 assertions.
Focused acceptance checks: 65 passed, 0 failures/errors, 270 assertions.
Total: 267 checks passed, 2137 assertions.

Environment: PHP 8.2.12 / PHPUnit 11.5.57 / SQLite :memory:.
Isolated storage, array mail/cache/session and synchronous queue were used.
Scope: local unit/feature/HTTP acceptance testing. These results do not mean
all 671 planned manual cases or live browser workflows were executed.

Fixed application defects:
- User display name changes now persist.
- Missing user-detail template now renders.
- Missing item-withdrawal action is implemented, with batch stock allocation.
- Excess releases/withdrawals are rejected without altering stock or logs.
- Stock deductions and audit logs commit or roll back together.
- SuperAdmin controller namespaces and duplicate misplaced class corrected.
- Missing Web Push dependencies restored and lockfile updated.

Test fixture and report assertions were updated to the current view/export
contracts; application export behavior was preserved. Eight permanent
regression tests were added for user updates/details and stock movements.

Files include per-check CSV, original PHPUnit JUnit XML, console logs and
TestDox output. Earlier evidence in the parent directory is the historical
pre-fix run and has not been overwritten. Temporary paths in raw JUnit
identify the isolated test environment used for this run.
