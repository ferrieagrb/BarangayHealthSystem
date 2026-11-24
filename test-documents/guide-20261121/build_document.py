"""Build the Word test plan and spreadsheet-friendly CSV from test-cases.json."""
import csv
import json
import argparse
from pathlib import Path
from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Inches, Pt

HERE = Path(__file__).resolve().parent
DEST = HERE.parent
NAME = 'BHEAMS-Website-Test-Cases-2026-11-21'
parser = argparse.ArgumentParser()
parser.add_argument('--sample-results', action='store_true')
args = parser.parse_args()
sample = args.sample_results
if sample:
    NAME += '-Sample-Results'
data = json.loads((HERE/('sample-test-cases.json' if sample else 'test-cases.json')).read_text(encoding='utf-8'))
groups = data['groups']
count = sum(len(g['cases']) for g in groups)
doc = Document()
sec = doc.sections[0]
sec.page_width, sec.page_height = Inches(8.5), Inches(11)
sec.top_margin = sec.bottom_margin = Inches(.6)
sec.left_margin = sec.right_margin = Inches(.5)
sec.header_distance = sec.footer_distance = Inches(.25)
normal = doc.styles['Normal']
normal.font.name, normal.font.size = 'Times New Roman', Pt(12)
normal.paragraph_format.space_after = Pt(0)
normal.paragraph_format.line_spacing = 1
for name in ['Title', 'Heading 1', 'Heading 2', 'Caption']:
    doc.styles[name].font.name = 'Times New Roman'
    doc.styles[name].font.color.rgb = None
doc.styles['Heading 1'].font.size = Pt(14)
doc.styles['Heading 2'].font.size = Pt(12)
doc.styles['Heading 1'].paragraph_format.space_before = Pt(10)
doc.styles['Heading 1'].paragraph_format.space_after = Pt(5)
doc.styles['Heading 2'].paragraph_format.space_before = Pt(7)
doc.styles['Heading 2'].paragraph_format.space_after = Pt(3)

header = sec.header.paragraphs[0]
header.alignment = WD_ALIGN_PARAGRAPH.CENTER
run = header.add_run('BHEAMS  |  SAMPLE RESULTS' if sample else 'BHEAMS  |  WEBSITE TEST CASES')
run.font.name, run.font.size = 'Times New Roman', Pt(9)
footer = sec.footer.paragraphs[0]
footer.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = footer.add_run()
field = OxmlElement('w:fldSimple'); field.set(qn('w:instr'), 'PAGE'); r._r.addnext(field)

def para(text, bold=False, size=12, align=None, keep=False):
    p = doc.add_paragraph()
    p.paragraph_format.keep_with_next = keep
    if align is not None: p.alignment = align
    r = p.add_run(text); r.bold = bold; r.font.size = Pt(size)
    return p

para('SAMPLE RESULTS: WEBSITE TEST CASES' if sample else 'APPENDIX: WEBSITE TEST CASES', True, 16, WD_ALIGN_PARAGRAPH.CENTER)
para('BHEAMS — Barangay Amuyong Health Center', True, 14, WD_ALIGN_PARAGRAPH.CENTER)
para('Prepared 21 November 2026  |  Asia/Manila', False, 11, WD_ALIGN_PARAGRAPH.CENTER)
para(f'{count} test cases across {len(groups)} feature groups', True, 12, WD_ALIGN_PARAGRAPH.CENTER)
doc.add_paragraph()
para('Purpose and format', True)
para('This new manual test plan covers the current local website: public pages and Citizen, BHW, Admin, and Super Admin functions. It follows the test tables in Appendix E of the supplied L.I.B.R.A manuscript, beginning at PDF page 193 (printed page 180).')
para('Each feature has a Test heading, Test Case Description, and the guide’s eight columns: #, Test Scenario, Test Cases, Test Step, Expected Result, Actual Result, Post Condition, and Remark. Library features and sample results are replaced with BHEAMS workflows.')
doc.add_paragraph()
para('Execution record', True)
para('Tester: ____________________   Reviewer: ____________________')
para('Test date: _________________   Build: _______________________')
para('Website URL: _____________________________________________')
para('Browser / device: _________________________________________')
para('Evidence folder / defect reference: __________________________')
doc.add_paragraph()
para('How to use the tables', True)
if sample:
    para('This sample copy uses short illustrative Actual Result and Post Condition entries, with Passed in every Remark cell. These are example successful outcomes, not observed test results or execution evidence.')
    para('For actual testing, run each case and replace the sample entries with the observed outcome. Negative cases pass when invalid or unauthorized actions are correctly blocked.')
else:
    para('These cases are prepared for execution. Actual Result is “Not yet executed.” and Remark is “Not Run” throughout. Previous test results are not carried forward. Post Condition describes the expected final state, not an observed result.')
    para('Run the stated steps and every listed input, role, format, and boundary variant. Replace Actual Result with observed behavior and evidence. Record Passed, Failed, or Blocked; use Not Applicable only with a documented reason. Record report preview, Excel, and PDF outcomes separately. A case passes only when all its applicable variants pass.')
para('Use synthetic records in a local or staging copy. Reset each case to its stated starting data. Capture the relevant identity, count, quantity, screenshot, or error, then restore changed settings and fixture data.')
doc.add_page_break()

doc.add_heading('Test data and setup', level=1)
setup = [
('CIT-A / CIT-B', 'Two Citizen accounts linked to RES-A and RES-B respectively. Keep a separate no-linked-profile account.'),
('BHW-A / BHW-B', 'Two health workers with known assignments. BHW-A covers SG-A in P-A; BHW-B covers SG-B in P-A. A separate worker covers SG-C in P-B.'),
('ADM-A / SA-A', 'An Admin and a Super Admin. Use a second disposable Super Admin for account-management cases.'),
('RES-A / RES-B / RES-C', 'Fictional citizens in SG-A/P-A, SG-B/P-A, and SG-C/P-B. RES-C is outside BHW-A scope. Record actual fixture IDs.'),
('F-A / F-B', 'Two families with known heads, members, and area-specific codes. Prepare archived-family and unassigned-citizen variants.'),
('Dates and age', 'T is the recorded test date in Asia/Manila. Use age-boundary fixtures and unknown-DOB imported records. Derive vaccine windows from the current application configuration.'),
('Stock', 'Use an item with starting quantity 20 and minimum stock 5. Prepare separate packs with known expiry dates, plus zero-stock and low-stock items. Reset stock before each case.'),
('Uploads and imports', 'Prepare valid and invalid CSV/XLSX/XLS files and image files. Include missing required values, unknown DOB, duplicate rows, late headers, and large/wide sheets.'),
('Clinical and public data', 'Prepare distinct diagnosis dates, completed vaccines, future schedules, maintenance medicines, announcements, events, and existing legacy supply-release history.'),
('Security and integrations', 'Use disposable accounts, controlled reset tokens, and test mail/push destinations. Simulate failures only in the isolated environment.'),
]
for key, value in setup:
    p = doc.add_paragraph(); p.paragraph_format.space_after = Pt(4)
    p.add_run(key + ': ').bold = True; p.add_run(value)
doc.add_paragraph()
para('Current behavior reflected in this revision', True)
for text in [
    'Referrals are generated directly from a selected citizen. Patient details are saved as a snapshot; clinical writing areas stay blank. There is no approval-status workflow in the current referral interface.',
    'Family cases cover code compaction on archive, the next code on restore, reuse of members from archived families, and availability checks when a selection becomes stale.',
    'Citizen import may preserve an unknown DOB without inventing age or username. Manual registration still requires a valid DOB.',
    'Citizen supply history cases use existing legacy release fixtures. They do not assume that new scheduled-release creation is currently available.',
]:
    p = doc.add_paragraph(text); p.paragraph_format.space_after = Pt(3)
doc.add_page_break()

doc.add_heading('Feature coverage index', level=1)
for n, g in enumerate(groups, 1):
    p = doc.add_paragraph(f"Test {n:02d} [{g['code']}] — {g['title']} ({len(g['cases'])} cases)")
    p.paragraph_format.space_after = Pt(2)
    for r in p.runs: r.font.size = Pt(11)
doc.add_page_break()

widths = [.28, .85, .99, 1.33, 1.28, 1.05, 1.04, .68] if sample else [.28, .91, 1.03, 1.37, 1.35, .73, 1.15, .68]
assert abs(sum(widths) - 7.5) < .001
labels = ['#', 'Test Scenario', 'Test Cases', 'Test Step', 'Expected Result', 'Actual Result', 'Post Condition', 'Remark']

def format_row(row, repeat=False, keep=False):
    trpr = row._tr.get_or_add_trPr()
    cant = OxmlElement('w:cantSplit'); trpr.append(cant)
    if repeat:
        flag = OxmlElement('w:tblHeader'); flag.set(qn('w:val'), 'true'); trpr.append(flag)
    for cell in row.cells:
        cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.TOP
        for p in cell.paragraphs:
            p.paragraph_format.keep_with_next = keep
            p.paragraph_format.space_after = Pt(0)
            p.paragraph_format.space_before = Pt(0)
            for r in p.runs:
                r.font.name = 'Times New Roman'; r.font.size = Pt(12)

for n, g in enumerate(groups, 1):
    caption = doc.add_paragraph(f"Table {n}. Test Cases for {g['title']}")
    caption.alignment = WD_ALIGN_PARAGRAPH.CENTER
    caption.paragraph_format.space_before = Pt(9)
    caption.paragraph_format.space_after = Pt(4)
    caption.paragraph_format.keep_with_next = True
    for r in caption.runs: r.bold = True; r.font.size = Pt(12)
    para('Precondition: ' + g['preconditions'], False, 10, keep=True)
    table = doc.add_table(rows=3, cols=8)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    table.style = 'Table Grid'
    for col, width in zip(table.columns, widths): col.width = Inches(width)
    margins = OxmlElement('w:tblCellMar')
    for edge, twips in [('top', 45), ('bottom', 45), ('left', 45), ('right', 45)]:
        item = OxmlElement('w:'+edge); item.set(qn('w:w'), str(twips)); item.set(qn('w:type'), 'dxa'); margins.append(item)
    table._tbl.tblPr.append(margins)
    for row in table.rows:
        for cell, width in zip(row.cells, widths): cell.width = Inches(width)
    title = table.rows[0].cells[0].merge(table.rows[0].cells[-1])
    title.text = f"Test {n}: {g['title']} [{g['code']}]"
    desc = table.rows[1].cells[0].merge(table.rows[1].cells[-1])
    desc.text = 'Test Case Description: ' + g['description'] + ' Role: ' + g['roles'] + '.'
    for cell, label in zip(table.rows[2].cells, labels): cell.text = label
    for row in table.rows:
        format_row(row, repeat=True, keep=True)
        for cell in row.cells:
            for r in cell.paragraphs[0].runs: r.bold = True
    for i, c in enumerate(g['cases'], 1):
        row = table.add_row()
        vals = [str(i), c['id']+'\n'+c['scenario'], c['testCase'],
            '\n'.join(f'{j}. {s.rstrip(".")}.' for j, s in enumerate(c['steps'], 1)),
            c['expected'], c['actual'], c['postcondition'], c['remark']]
        for cell, width, text in zip(row.cells, widths, vals):
            cell.width = Inches(width); cell.text = text
        format_row(row)
    p = doc.add_paragraph(); p.paragraph_format.space_after = Pt(2)
    p.paragraph_format.line_spacing = Pt(2)

doc.add_page_break()
doc.add_heading('Source and review record', level=1)
para('Reference guide: '+data['reference'])
para('Guide location: Appendix E, PDF page 193 onward; PDF page 193 is printed page 180. The manuscript supplies the table format, not BHEAMS requirements or results.')
para('Website basis: current local routes, controllers, services, views, configuration, and existing automated-test definitions. Existing test definitions inform the plan; this document does not claim they were executed again.')
para('Branch: '+data['branch'])
para('Source commit: '+data['commit'], size=10)
para('The companion JSON records module sources and SHA-256 hashes. The CSV contains the same cases with role, precondition, and source fields.')
para('Scope: functional, validation, access-control, data-consistency, export, mobile, and end-to-end acceptance cases. Performance measurements and deployment verification require separate execution evidence.')
doc.add_paragraph()
para('Sample totals (illustrative only)' if sample else 'Execution totals (complete after testing)', True)
para(f'Sample cases: {count}    Sample Passed entries: {count}' if sample else f'Prepared cases: {count}    Passed: _____    Failed: _____')
if not sample:
    para('Blocked: _____    Not Run: _____    Not Applicable: _____')
para('Open defect references: ____________________________________')
para('Reviewer / date: __________________________________________')

doc.core_properties.title = 'BHEAMS Website Test Cases — 21 November 2026' + (' — Sample Results' if sample else '')
doc.core_properties.subject = 'Manual test cases following the supplied Appendix E guide'
doc.core_properties.author = 'BHEAMS Project'
doc.core_properties.comments = 'Illustrative sample results only; not execution evidence.' if sample else 'Prepared plan only. No execution outcomes are asserted.'
doc.save(DEST/(NAME+'.docx'))

fields = ['Test No.', 'Feature', 'Role', 'Precondition', 'Case ID', '#', 'Test Scenario', 'Test Cases', 'Test Step',
    'Expected Result', 'Actual Result', 'Post Condition', 'Remark', 'Source Files']
with (DEST/(NAME+'.csv')).open('w', encoding='utf-8-sig', newline='') as f:
    writer = csv.writer(f); writer.writerow(fields)
    for n, g in enumerate(groups, 1):
        for i, c in enumerate(g['cases'], 1):
            writer.writerow([n, g['title'], g['roles'], g['preconditions'], c['id'], i,
                c['scenario'], c['testCase'], '\n'.join(f'{j}. {s}' for j, s in enumerate(c['steps'], 1)),
                c['expected'], c['actual'], c['postcondition'], c['remark'], '; '.join(g['sources'])])
print(json.dumps(dict(document=str(DEST/(NAME+'.docx')), groups=len(groups), cases=count)))
