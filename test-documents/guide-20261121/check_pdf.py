"""Check rendered text and create representative page previews."""
import json
import os
import sys
from pathlib import Path
sys.path.insert(0, str(Path(os.environ['TEMP'])/'bheams-guide-libs-20261121'))
import pymupdf

HERE = Path(__file__).resolve().parent
PREVIEWS = Path(os.environ['TEMP'])/'bheams-guide-reference-20261121'
PREVIEWS.mkdir(exist_ok=True)
data = json.loads((HERE/'test-cases.json').read_text(encoding='utf-8'))
pdf = pymupdf.open(str(HERE.parent/'BHEAMS-Website-Test-Cases-2026-11-21.pdf'))
texts = [p.get_text() for p in pdf]
whole = ''.join(''.join(t.split()) for t in texts)
missing = [c['id'] for g in data['groups'] for c in g['cases'] if c['id'] not in whole]
outside = []
for n, p in enumerate(pdf, 1):
    for word in p.get_text('words'):
        if word[0] < 30 or word[2] > p.rect.width-29 or word[1] < 9 or word[3] > p.rect.height-9:
            outside.append([n, list(word[:5])])
selected = {0, 4, len(pdf)-1}
locations = {}
for code in ['FAM', 'REF', 'RPT', 'UX']:
    for i, text in enumerate(texts):
        if i < 4:
            continue
        if f'[{code}]' in text:
            locations[code] = i+1
            selected.add(i)
            break
for i in sorted(selected):
    pdf[i].get_pixmap(matrix=pymupdf.Matrix(1.3, 1.3)).save(str(PREVIEWS/f'preview-page-{i+1:02d}.png'))
result = dict(pages=len(pdf), all_case_ids_visible=not missing, missing_case_ids=missing,
    outside_page_bounds=outside, blank_pages=[i+1 for i,t in enumerate(texts) if len(t.strip())<50],
    representative_pages=locations, image_previews=[i+1 for i in sorted(selected)])
(HERE/'pdf-validation.json').write_text(json.dumps(result, indent=2), encoding='utf-8')
print(json.dumps(result))
assert not missing
assert not outside
