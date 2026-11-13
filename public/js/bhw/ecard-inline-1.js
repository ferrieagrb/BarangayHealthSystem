let citizenPrintPageRule = null;
function printCitizenSection(section) {
    document.body.classList.remove('print-id-only', 'print-record-only');
    document.body.classList.add(section === 'id' ? 'print-id-only' : 'print-record-only');

    if (citizenPrintPageRule) {
        citizenPrintPageRule.remove();
        citizenPrintPageRule = null;
    }

    citizenPrintPageRule = document.createElement('style');
    citizenPrintPageRule.id = 'citizen-print-page-size';
    citizenPrintPageRule.textContent = section === 'id'
        ? '@page { size: auto; margin: 0; }'
        : '@page { size: A4 landscape; margin: 8mm; }';
    document.head.appendChild(citizenPrintPageRule);

    window.print();
}
window.addEventListener('afterprint', function() {
    document.body.classList.remove('print-id-only', 'print-record-only');
    if (citizenPrintPageRule) {
        citizenPrintPageRule.remove();
        citizenPrintPageRule = null;
    }
});
