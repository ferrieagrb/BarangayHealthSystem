"""Create brief, explicitly illustrative all-Passed sample entries."""
import copy
import json
from pathlib import Path

HERE = Path(__file__).resolve().parent
data = copy.deepcopy(json.loads((HERE/'test-cases.json').read_text(encoding='utf-8')))
entries = {}

def rows(code, text):
    entries[code] = [tuple(part.strip() for part in line.split('|')) for line in text.strip().splitlines()]

rows('PUB', '''Successfully opens the homepage.|Homepage displayed.
Successfully shows recent announcements.|Announcements visible.
Successfully opens each selected page.|Navigation remains functional.
Successfully displays the contact directory.|Contact details visible.
Successfully displays the logo and favicon.|Branding displayed.
Successfully displays empty-state messages.|No records added.''')
rows('PCA', '''Successfully shows events on correct dates.|Events displayed.
Successfully changes the displayed month.|Selected month displayed.
Successfully displays selected event details.|Event details visible.''')
rows('PAN', '''Successfully lists public announcements.|Announcements visible.
Successfully shows the selected announcement.|Announcement details displayed.
Successfully lists citizen announcements.|Citizen announcements visible.
Successfully returns to the dashboard.|Dashboard position restored.''')
entries['LOG'] = [(f'Successfully opens the {role} dashboard.', 'User signed in.') for role in ['Citizen', 'BHW', 'Admin', 'Super Admin']]
entries['LOG'] += [('Successfully rejects invalid credentials.', 'User remains signed out.'),
    ('Successfully signs out each user.', 'Sessions terminated.'),
    ('Successfully blocks unauthenticated access.', 'Protected pages remain restricted.'),
    ('Successfully prompts for both credentials.', 'No session created.'),
    ('Successfully rejects incomplete credentials.', 'No session created.'),
    ('Successfully blocks the archived account.', 'Account remains archived.')]
rows('LCK', '''Successfully limits repeated login attempts.|Temporary limit applied.
Successfully allows login after lock expiry.|Temporary lock cleared.
Successfully unlocks the selected account.|Account unlocked.
Successfully locks the account at the threshold.|Account temporarily locked.''')
rows('PWD', '''Successfully sends the password-reset link.|Reset link delivered.
Successfully resets the password.|New password active.
Successfully opens the required password-change page.|Normal access remains restricted.
Successfully saves the required new password.|Dashboard access enabled.
Successfully rejects the expired token.|Password unchanged.
Successfully rejects the reused token.|Password unchanged.
Successfully rejects invalid password values.|Password unchanged.''')
entries['PRF'] = [('Successfully saves appearance preferences.', 'Preferences retained.') for _ in range(4)] + [
    ('Successfully changes the password.', 'New password active.'),
    ('Successfully rejects the incorrect current password.', 'Password unchanged.')]
rows('EML', '''Successfully sends the verification email.|Verification link delivered.
Successfully verifies the email address.|Email marked verified.''')
rows('PUS', '''Successfully subscribes the current device.|Device subscription saved.
Successfully lists the user's devices.|Own devices displayed.
Successfully unsubscribes the current device.|Device subscription removed.
Successfully blocks another user's device removal.|Other subscription unchanged.''')
rows('CDS', '''Successfully displays the linked citizen profile.|Own profile visible.
Successfully displays diagnosis history.|Own history visible.
Successfully displays vaccination records and schedules.|Vaccination details visible.
Successfully displays maintenance medicines.|Medicine details visible.
Successfully handles the missing linked profile.|No unrelated profile shown.
Successfully blocks another citizen's data.|Personal records remain private.''')
rows('CRD', '''Successfully displays the citizen's health card.|Correct health card visible.
Successfully updates the citizen photo.|New photo saved.
Successfully prints or downloads the card.|Readable card available.
Successfully rejects the oversized photo.|Existing photo retained.
Successfully blocks the out-of-area photo update.|Protected photo unchanged.''')
rows('SHR', '''Successfully enables public health sharing.|Public sharing enabled.
Successfully displays the shared health summary.|Correct summary visible.
Successfully disables public health sharing.|Public details hidden.
Successfully blocks unauthorized sharing changes.|Other profile unchanged.
Successfully rejects the invalid QR token.|No profile disclosed.
Successfully blocks the archived citizen's token.|Archived details remain hidden.''')
rows('PQR', '''Successfully scans the health QR code.|Matching citizen displayed.
Successfully reads the uploaded QR image.|Matching citizen displayed.
Successfully accepts a valid URL or token.|Matching summary displayed.
Successfully handles denied camera access.|Alternative input remains available.
Successfully handles an unreadable QR image.|No incorrect citizen selected.''')
rows('CSU', '''Successfully lists available supplies.|Available stock visible.
Successfully shows matching supplies.|Search results filtered.
Successfully displays existing release history.|Own release history visible.
Successfully hides another citizen's releases.|Other release records remain private.
Successfully blocks unauthorized stock allocation.|Stock unchanged.''')
rows('BDS', '''Successfully opens the BHW dashboard.|Dashboard displayed.
Successfully displays accurate summary totals.|Current totals visible.''')
rows('BCL', '''Successfully lists permitted citizens.|Assigned citizens visible.
Successfully shows matching citizens.|Search results filtered.
Successfully filters by Purok and subgroup.|Location filters applied.
Successfully enforces patient access permissions.|Unauthorized changes blocked.
Successfully filters the selected age bracket.|Age filter applied.
Successfully switches living and deceased views.|Selected status displayed.
Successfully changes pages while retaining filters.|Filters retained.
Successfully displays the no-results message.|No unrelated citizens shown.''')
rows('BCR', '''Successfully registers the citizen with a family.|Citizen and linked account created.
Successfully registers the citizen without a family.|Manual area assignment saved.
Successfully rejects missing required details.|No citizen created.
Successfully requires a temporary-password change.|Normal access restricted until change.
Successfully accepts blank optional fields.|Citizen saved without invented values.
Successfully rejects invalid or future birth dates.|No citizen created.
Successfully saves capitalized names.|Names formatted correctly.
Successfully rejects a missing manual birth date.|No partial records created.''')
rows('BCU', '''Successfully updates the selected citizen.|Profile changes saved.
Successfully permits only authorized area assignment.|Valid assignment saved.
Successfully applies family-planning eligibility rules.|Eligible values retained.
Successfully archives the citizen.|Citizen removed from active list.
Successfully clears optional values.|Blank values saved.
Successfully rejects a future birth date.|Birth date unchanged.
Successfully cancels citizen archival.|Citizen remains active.
Successfully blocks out-of-area access.|Protected citizen unchanged.''')
rows('IMP', '''Successfully imports supported file formats.|Expected citizen records created.
Successfully identifies the correct headings.|Data rows imported correctly.
Successfully maps imported subgroup values.|Correct area assignments saved.
Successfully rejects invalid uploads.|No rows imported.
Successfully imports large and wide files.|Expected rows retained.
Successfully preserves unknown birth dates.|No invented age or username.
Successfully skips rows with invalid required names.|Invalid rows excluded.
Successfully leaves invalid optional values blank.|Valid row data retained.
Successfully reports import counts and issues.|Import totals reconciled.
Successfully rejects the corrupt workbook.|No invalid rows imported.
Successfully requires the imported account's password change.|Account access follows password change.
Successfully updates the previously missing birth date.|DOB-based identity saved.''')
rows('EXP', '''Successfully exports the Excel workbook.|Excel file available.
Successfully exports the PDF document.|PDF file available.
Successfully requires password confirmation.|Export remains protected.
Successfully exports the filtered citizen selection.|Export matches displayed results.
Successfully excludes unauthorized citizen records.|Protected records remain private.''')
rows('FAM', '''Successfully finds the family using a member.|Matching family displayed.
Successfully creates the selected household.|Members and head assigned.
Successfully generates area-specific family codes.|Unique codes saved.
Successfully rejects missing or duplicate members.|No invalid family created.
Successfully rejects an unavailable member.|Existing membership retained.
Successfully creates a family through quick-create.|New family available.
Successfully adds the available member.|Family membership updated.
Successfully removes the selected member.|Citizen record retained.
Successfully changes the family head.|Selected head saved.
Successfully rejects an unrelated family head.|Existing head retained.
Successfully moves the family locally.|Member area links updated.
Successfully enforces cross-Purok movement permission.|Only authorized move saved.
Successfully archives the family and compacts codes.|Active area codes renumbered.
Successfully restores the family with the next code.|Family active with unique code.
Successfully reassigns an archived family's former member.|New membership saved.
Successfully restores without reclaiming reassigned members.|New member links retained.
Successfully rejects the stale member selection.|Existing membership retained.
Successfully blocks unauthorized family changes.|Family records unchanged.''')
rows('HLT', '''Successfully displays the citizen's medical history.|Correct history visible.
Successfully saves the diagnosis.|Diagnosis added to citizen history.
Successfully saves the supplied vital signs.|Vital signs retained.
Successfully archives the selected diagnosis.|Diagnosis available for restoration.
Successfully blocks read-only record changes.|Health records unchanged.
Successfully validates vital-sign boundaries.|Only valid values saved.
Successfully rejects invalid required or lengthy values.|No invalid diagnosis saved.
Successfully accepts omitted optional vital signs.|Diagnosis saved without invented vitals.
Successfully rejects a missing or mismatched citizen.|No incorrect health link created.''')
rows('VAC', '''Successfully records the eligible vaccine dose.|Vaccination history updated.
Successfully rejects an out-of-sequence dose.|Dose sequence preserved.
Successfully saves the historical vaccination.|Historical details retained.
Successfully archives the selected vaccination.|Record available for restoration.
Successfully applies vaccine eligibility boundaries.|Only eligible doses accepted.
Successfully rejects the duplicate dose.|No duplicate record created.
Successfully rejects vaccination with unknown birth date.|Vaccination history unchanged.
Successfully validates the administration date.|Only valid dates accepted.
Successfully rejects tampered historical vaccine data.|No forged record saved.
Successfully blocks deceased or out-of-area targets.|Protected vaccination records unchanged.''')
rows('VSC', '''Successfully schedules the eligible citizen.|Vaccination schedule saved.
Successfully records the administered vaccination.|Schedule marked completed.
Successfully blocks scheduling when disabled.|No schedule created.
Successfully displays the citizen's own schedule.|Other schedules remain hidden.
Successfully rejects duplicate or ineligible scheduling.|No invalid schedule created.
Successfully prevents repeated schedule completion.|No duplicate vaccination created.''')
rows('MED', '''Successfully records the medication entry.|Medication history updated.
Successfully saves the maintenance medicine.|Maintenance details retained.
Successfully archives the maintenance record.|Record available for restoration.
Successfully rejects invalid maintenance dates.|No invalid record saved.
Successfully blocks restricted medication changes.|Protected medicine records unchanged.
Successfully saves medication without deducting inventory.|Stock unchanged.''')
rows('TER', '''Successfully displays medicine suggestions.|Relevant medicines listed.
Successfully displays diagnosis suggestions.|Relevant diagnoses listed.
Successfully handles the terminology service outage.|Manual entry remains available.
Successfully displays vaccine suggestions.|Relevant vaccines listed.
Successfully keeps citizen details out of searches.|Personal data remains private.''')
rows('INV', '''Successfully opens the inventory pages.|Inventory displayed.
Successfully filters inventory results.|Selected stock results displayed.
Successfully creates the supply catalog item.|Item saved with zero stock.
Successfully deposits the supply batch.|Stock and deposit log updated.
Successfully deposits multiple packs.|Correct total stock added.
Successfully rejects invalid catalog values.|No invalid item created.
Successfully rejects an invalid deposit pack.|Stock unchanged.
Successfully displays stock-threshold indicators.|Correct stock status shown.''')
rows('STK', '''Successfully withdraws the requested batch quantity.|Stock reduced from 20 to 16.
Successfully rejects excess withdrawal.|Stock and logs unchanged.
Successfully withdraws from earliest-expiring packs.|Total stock reduced to 14.
Successfully releases stock to the citizen.|Stock reduced by three units.
Successfully rejects excess stock release.|Stock and logs unchanged.
Successfully archives the exact supply item.|Unrelated items retained.
Successfully rejects invalid withdrawal values.|Stock unchanged.
Successfully prevents concurrent stock over-allocation.|Stock and logs remain consistent.
Successfully rolls back the failed stock transaction.|Stock and logs unchanged.''')
rows('ANN', '''Successfully publishes the text announcement.|Announcement visible.
Successfully publishes all attached images.|Images linked to announcement.
Successfully replaces the selected image.|New attachment displayed.
Successfully archives the announcement.|Announcement hidden from active views.
Successfully rejects missing required values.|No announcement created.
Successfully rejects invalid image uploads.|Existing attachments retained.
Successfully blocks unauthorized announcement changes.|Announcements unchanged.''')
rows('EVT', '''Successfully creates the event.|Event visible on correct date.
Successfully updates the event.|Updated details displayed.
Successfully archives the event.|Event removed from active feeds.
Successfully rejects invalid event details.|No invalid event saved.
Successfully rejects unauthorized or missing event requests.|Events unchanged.''')
rows('REF', '''Successfully displays the referral records.|Current referral list visible.
Successfully shows matching referral records.|Search results filtered.
Successfully finds the matching permitted citizen.|Citizen selection filtered.
Successfully updates the selected patient preview.|Correct patient details displayed.
Successfully blocks generation without a citizen.|No referral created.
Successfully generates and downloads the referral PDF.|Referral and private PDF saved.
Successfully ignores forged patient details.|Verified patient details saved.
Successfully leaves clinical writing areas blank.|Clinical fields remain unfilled.
Successfully retains the saved patient snapshot.|Original referral details preserved.
Successfully applies the current logo.|Current branding displayed.
Successfully generates a readable one-page form.|Single-page A4 PDF available.
Successfully preserves unknown patient values.|Missing details remain blank.
Successfully blocks out-of-area citizen selection.|No unauthorized referral created.
Successfully blocks non-BHW referral access.|Private referrals remain restricted.
Successfully cleans up failed referral generation.|No partial referral or PDF remains.
Successfully downloads the legacy referral.|Original clinical details retained.
Successfully rejects unavailable referral downloads.|No unrelated PDF disclosed.''')
rows('BQR', '''Successfully displays the scanned citizen.|Correct internal profile shown.
Successfully saves diagnosis after scanning.|Correct citizen history updated.
Successfully blocks out-of-area QR lookup.|Protected details remain hidden.
Successfully rejects the unknown QR token.|No citizen selected.
Successfully rejects a tampered diagnosis target.|Protected health records unchanged.''')
entries['RPT'] = [('Successfully displays and exports the report.', 'Preview, Excel, and PDF match.') for _ in range(12)]
rows('RPF', '''Successfully validates combined location filters.|Valid location filters applied.
Successfully finds the selected citizen's history.|Matching history displayed.
Successfully displays count-only results.|Detailed lists and graphics hidden.
Successfully applies graphic and summary options.|Selected report view displayed.
Successfully rejects invalid age ranges.|No invalid report generated.
Successfully rejects invalid date ranges.|No invalid report generated.
Successfully blocks disabled report access.|Reports remain restricted.
Successfully enforces report-group permissions.|Only permitted reports displayed.
Successfully displays an empty-report message.|No invented records shown.''')
rows('ANA', '''Successfully opens the analytics page.|Analytics displayed.
Successfully applies disease and area filters.|Matching analytics displayed.
Successfully masks groups below the privacy threshold.|Small groups remain protected.
Successfully calculates supply-usage estimates.|Expected estimates displayed.
Successfully uses the diagnosis record date.|Correct reporting period applied.
Successfully counts distinct citizens.|Duplicate patients not overcounted.
Successfully displays the no-data state.|No invalid totals shown.''')
rows('BLG', '''Successfully displays each operational log category.|Selected logs visible.
Successfully saves the vehicle log.|Vehicle entry recorded.
Successfully rejects invalid vehicle details.|No invalid entry saved.
Successfully enforces audit-log permissions.|Unauthorized log access blocked.''')
entries['BAR'] = [(f'Successfully restores the selected {name}.', 'Record active again.') for name in
    ['citizen', 'family', 'supply', 'event', 'diagnosis', 'maintenance record', 'vaccination', 'announcement']]
entries['BAR'] += [('Successfully rejects invalid restore targets.', 'Archive unchanged.'),
    ('Successfully blocks out-of-area restoration.', 'Protected record remains archived.'),
    ('Successfully blocks read-only restoration.', 'Record remains archived.')]
entries['BPM'] = []
for feature in ['inventory', 'patient', 'family']:
    entries['BPM'] += [(f'Successfully blocks denied {feature} access.', 'Protected records unchanged.'),
        ('Successfully allows viewing and blocks changes.', 'Read-only access enforced.'),
        (f'Successfully allows permitted {feature} changes.', 'Authorized changes saved.')]
entries['BPM'] += [('Successfully blocks denied password-reset access.', 'Citizen password unchanged.'),
    ('Successfully blocks reset with read access.', 'Citizen password unchanged.'),
    ('Successfully allows the authorized password reset.', 'Temporary password issued.')]
rows('ADS', '''Successfully opens the Admin dashboard.|Dashboard displayed.
Successfully displays accurate operational totals.|Report totals reconciled.
Successfully applies the forecast history requirement.|Projection shown only with sufficient history.''')
rows('AUS', '''Successfully filters the user list.|Matching users displayed.
Successfully creates accounts with permitted roles.|Accounts require initial password change.
Successfully rejects Super Admin role creation.|No elevated account created.
Successfully updates the user's display name.|New name saved.
Successfully archives the selected account.|Archived account cannot sign in.
Successfully restores the selected account.|Account restored with required password change.
Successfully unlocks the selected account.|Lock counters cleared.
Successfully resets the selected user's password.|Temporary password issued.
Successfully rejects invalid user details.|No invalid account saved.
Successfully protects the Super Admin account.|Protected account unchanged.
Successfully blocks self-archival.|Current account remains active.''')
rows('APM', '''Successfully lists BHW permission targets.|BHW accounts displayed.
Successfully saves the selected permission levels.|Permissions updated.
Successfully blocks actions after permission revocation.|Revoked access enforced.''')
rows('GEO', '''Successfully creates the Purok.|New Purok available.
Successfully creates the subgroup.|Subgroup linked to correct Purok.
Successfully saves shared BHW coverage.|Both assigned workers have access.
Successfully enforces subgroup archive requirements.|Only eligible archival completed.
Successfully rejects invalid Purok details.|No invalid Purok created.
Successfully rejects duplicate subgroup values.|No duplicate subgroup created.
Successfully removes the worker assignment.|Removed worker's access updated.''')
rows('MAP', '''Successfully saves the Purok pin.|Purok location retained.
Successfully saves the subgroup pin.|Subgroup location retained.
Successfully saves the valid area boundary.|Boundary displayed.
Successfully removes the selected boundary.|Other boundaries retained.
Successfully rejects out-of-range coordinates.|Existing location retained.
Successfully rejects invalid boundary geometry.|Existing boundary retained.''')
rows('BUL', '''Successfully archives the target Purok's citizens.|Matched citizens and accounts archived.
Successfully archives the target subgroup's citizens.|Matched citizens and accounts archived.
Successfully rejects incorrect typed confirmation.|Citizens remain active.
Successfully rejects missing or incorrect password.|Citizens remain active.
Successfully blocks unauthorized bulk archival.|Citizen records unchanged.''')
rows('CAT', '''Successfully creates the inventory category.|Category available.
Successfully renames the selected category.|Updated category displayed.
Successfully archives the selected category.|Category available for restoration.
Successfully deposits stock as Admin.|Stock and deposit history updated.''')
rows('AAR', '''Successfully displays Admin log categories.|Selected logs visible.
Successfully restores the selected Purok.|Purok active again.
Successfully restores the selected supply.|Supply active again.''')
rows('SDS', '''Successfully opens the Super Admin dashboard.|Dashboard displayed.
Successfully displays current active sessions.|Session details visible.
Successfully displays security and audit events.|Recent events visible.''')
rows('SUS', '''Successfully lists users across all roles.|Filtered user list displayed.
Successfully creates the Super Admin account.|Account requires initial password change.
Successfully opens the selected account details.|Correct user details displayed.
Successfully updates the user's identity and role.|Accepted changes saved.
Successfully archives and restores the account.|Correct account state retained.
Successfully unlocks and resets the selected account.|Lock cleared; temporary password issued.
Successfully blocks restricted self-management actions.|Current account remains protected.
Successfully rejects duplicate or invalid identities.|No invalid account saved.''')
rows('SAY', '''Successfully loads system analytics data.|Analytics widgets populated.
Successfully displays user and traffic totals.|Accurate totals visible.
Successfully displays disk and database metrics.|Correct units and values shown.''')
rows('CON', '''Successfully refreshes connection status.|Latest check time displayed.
Successfully reports database connection status.|Accurate database status visible.
Successfully isolates the failing service.|Other connection cards remain available.''')
rows('SAL', '''Successfully displays combined chronological logs.|Latest events shown first.
Successfully filters logs by source.|Selected-source logs displayed.
Successfully hides sensitive credentials and tokens.|Secrets remain private.''')
rows('SET', '''Successfully saves valid system settings.|Updated settings retained.
Successfully validates login-attempt limits.|Only permitted limits saved.
Successfully applies password re-entry settings.|Confirmation behavior updated.
Successfully applies vaccination scheduling settings.|Scheduling availability updated.
Successfully applies maintenance mode exclusions.|Restricted pages show maintenance notice.
Successfully updates the system logo.|New branding displayed.
Successfully rejects the invalid logo upload.|Existing logo retained.
Successfully blocks lower-role settings access.|Global settings unchanged.''')
rows('BKP', '''Successfully creates the encrypted database backup.|Encrypted backup file available.
Successfully requires password confirmation.|Backup download remains protected.
Successfully restores the backup to a scratch database.|Recovered records verified.
Successfully blocks unauthorized backup access.|No backup disclosed.''')
rows('SEC', '''Successfully blocks unauthorized Citizen access.|Administrative pages remain restricted.
Successfully blocks unauthorized BHW access.|Administrative pages remain restricted.
Successfully blocks unauthorized Admin access.|Super Admin functions remain restricted.
Successfully blocks unauthenticated protected requests.|No unauthorized changes saved.
Successfully rejects requests without valid CSRF protection.|No unauthorized changes saved.
Successfully blocks tampered record identifiers.|Other users' records protected.
Successfully prevents untrusted script execution.|Displayed content remains safe.
Successfully blocks direct private-file access.|Private files remain protected.
Successfully enforces the required password change.|Normal access remains restricted.
Successfully displays safe error responses.|Secrets remain undisclosed.''')
rows('UX', '''Successfully supports the tested desktop browsers.|Core workflows remain usable.
Successfully adapts to mobile and tablet widths.|Controls remain accessible.
Successfully supports keyboard navigation.|Focus and controls remain usable.
Successfully displays field labels and errors.|Form feedback remains readable.
Successfully supports larger text and zoom.|Content remains accessible.
Successfully handles empty and final pagination pages.|Valid navigation retained.
Successfully displays mobile family-selection controls.|Pickers remain usable.
Successfully displays working Clear and Save buttons.|Actions remain visible side by side.''')
rows('E2E', '''Successfully completes citizen onboarding.|Identity and area links consistent.
Successfully displays newly recorded clinical history.|Correct Citizen account updated.
Successfully completes the inventory workflow.|Stock and history remain consistent.
Successfully enables and revokes QR sharing.|Public access follows sharing state.
Successfully enforces revoked permissions throughout.|Unauthorized actions blocked.
Successfully archives and reactivates linked records.|Linked states restored consistently.
Successfully reconciles reports with source records.|Preview and exports match.''')

for g in data['groups']:
    values = entries[g['code']]
    assert len(values) == len(g['cases']), (g['code'], len(values), len(g['cases']))
    for c, pair in zip(g['cases'], values):
        assert len(pair) == 2
        c['actual'], c['postcondition'] = pair
        c['remark'] = 'Passed'
        c['result_type'] = 'Illustrative sample; not execution evidence'
        assert len(c['actual'].split()) <= 12, c['id']
        assert len(c['postcondition'].split()) <= 12, c['id']
data['status'] = 'Illustrative all-Passed sample results; no test execution claimed.'
(HERE/'sample-test-cases.json').write_text(json.dumps(data, indent=2, ensure_ascii=False), encoding='utf-8')
print('Prepared', sum(len(g['cases']) for g in data['groups']), 'brief sample results.')
