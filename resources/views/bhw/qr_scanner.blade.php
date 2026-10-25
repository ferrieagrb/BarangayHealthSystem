@extends('templates.layout')

@section('CSSown')
<style>
    .qr-page { max-width: 1180px; margin: 0 auto; padding: 24px; color: #172033; }
    .qr-heading { margin-bottom: 22px; }
    .qr-heading h1 { margin: 0 0 6px; font-size: 28px; font-weight: 750; }
    .qr-heading p { margin: 0; color: #64748b; }
    .qr-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 22px; align-items: start; }
    .qr-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; box-shadow: 0 5px 18px rgba(15, 23, 42, .05); min-width: 0; }
    .qr-panel h2 { margin: 0 0 8px; font-size: 18px; font-weight: 700; }
    .qr-muted { color: #64748b; font-size: 14px; line-height: 1.5; }
    #qr-reader { width: 100%; max-width: 460px; margin: 18px auto 12px; overflow: hidden; border-radius: 12px; background:#f8fafc; }
    #qr-reader video { border-radius: 10px; }
    .qr-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
    .qr-btn { border: 0; border-radius: 9px; padding: 11px 16px; font-weight: 650; cursor: pointer; }
    .qr-btn-primary { background: #155eef; color: white; }
    .qr-btn-secondary { background: #eef2f7; color: #263449; }
    .qr-btn:disabled { opacity: .55; cursor: not-allowed; }
    .qr-status { margin-top: 14px; padding: 12px 14px; border-radius: 9px; background: #f1f5f9; color: #334155; font-size: 14px; }
    .qr-status.error { background: #fef2f2; color: #b91c1c; }
    .qr-status.success { background: #ecfdf5; color: #047857; }
    .qr-manual { margin-top: 22px; padding-top: 18px; border-top: 1px solid #e2e8f0; }
    .qr-manual label { display: block; font-weight: 650; font-size: 13px; margin-bottom: 7px; }
    .qr-manual input { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 9px; box-sizing: border-box; }
    .qr-results[hidden] { display: none; }
    .qr-empty { text-align: center; padding: 32px 16px; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 12px; }
    .qr-profile { display: flex; align-items: center; gap: 14px; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0; margin-bottom: 18px; }
    .qr-avatar { width: 62px; height: 62px; border-radius: 50%; object-fit: cover; background: #e2e8f0; display: grid; place-items: center; color: #475569; font-size: 24px; flex: 0 0 auto; }
    .qr-profile h3 { margin: 0 0 4px; font-size: 20px; font-weight: 750; }
    .qr-profile-actions { display:flex; flex-wrap:wrap; gap:9px; margin-top:12px; }
    .qr-detail-group { border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin:16px 0; }
    .qr-detail-group h4 { margin:0 0 14px; color:#173b82; font-size:14px; text-transform:uppercase; letter-spacing:.04em; }
    .qr-action-link { display:inline-flex; align-items:center; text-decoration:none; border-radius:9px; padding:10px 13px; font-size:13px; font-weight:700; background:#155eef; color:#fff; }
    .qr-action-link.secondary { background:#eef2f7; color:#263449; }
    .qr-diagnosis-form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .qr-diagnosis-form label { display:block; font-size:13px; font-weight:650; margin-bottom:6px; }
    .qr-diagnosis-form input,.qr-diagnosis-form textarea { width:100%; box-sizing:border-box; border:1px solid #cbd5e1; border-radius:9px; padding:10px 11px; font:inherit; }
    .qr-diagnosis-form .full { grid-column:1/-1; }
    .qr-diagnosis-form textarea { min-height:80px; resize:vertical; }
    .qr-section { scroll-margin-top:24px; }
    .qr-verified { display: inline-block; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 5px 9px; background: #dcfce7; color: #166534; }
    .qr-info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px; }
    .qr-info-item { min-width: 0; }
    .qr-info-item small { display: block; color: #64748b; margin-bottom: 4px; }
    .qr-info-item strong { overflow-wrap: anywhere; }
    .qr-section { margin-top: 22px; }
    .qr-section h3 { margin: 0 0 10px; font-size: 16px; font-weight: 750; }
    .qr-table-wrap { overflow-x: auto; }
    .qr-table { border-collapse: collapse; width: 100%; font-size: 13px; }
    .qr-table th, .qr-table td { text-align: left; padding: 10px 9px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .qr-table th { color: #475569; background: #f8fafc; font-weight: 700; white-space: nowrap; }
    .qr-no-records { color: #64748b; padding: 12px 0; font-size: 14px; }
    @media (max-width: 850px) { .qr-layout { grid-template-columns: 1fr; } .qr-page { padding: 16px; } }
    @media (max-width: 600px) { .qr-info-grid { grid-template-columns: 1fr; } .qr-diagnosis-form { grid-template-columns:1fr; } .qr-diagnosis-form .full { grid-column:auto; } .qr-panel { padding: 16px; } .qr-heading h1 { font-size: 23px; } }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/qr-checker-alignment.css') }}">
@endsection

@section('content')
<div class="qr-page">
    <header class="qr-heading">
        <h1>Citizen QR Checker</h1>
        <p>Scan a citizen's Health ID to verify the record and review authorized health information.</p>
    </header>

    <div class="qr-layout">
        <section class="qr-panel" aria-labelledby="scanner-title">
            <h2 id="scanner-title">Scan Health ID</h2>
            <p class="qr-muted">Allow camera access when prompted. On phones, use the rear camera and keep the QR code inside the frame.</p>

            <div id="qr-reader"></div>

            <div class="qr-actions">
                <button type="button" id="start-scanner" class="qr-btn qr-btn-primary">Start Camera</button>
                <button type="button" id="stop-scanner" class="qr-btn qr-btn-secondary" disabled>Stop Camera</button>
            </div>

            <div class="qr-manual">
                <label for="qr-image">Scan a saved QR image</label>
                <input id="qr-image" type="file" accept="image/*">
            </div>

            <div id="qr-status" class="qr-status" role="status" aria-live="polite">Camera is stopped. Select Start Camera to begin.</div>

            <div class="qr-manual">
                <label for="manual-token">Manual lookup (if the camera is unavailable)</label>
                <input id="manual-token" type="text" autocomplete="off" placeholder="Paste the Health ID QR URL or token">
                <div class="qr-actions">
                    <button type="button" id="manual-lookup" class="qr-btn qr-btn-secondary">Find Citizen</button>
                </div>
            </div>
        </section>

    </div>

    <div class="qr-layout" style="margin-top:22px;">
        <section class="qr-panel" aria-labelledby="result-title">
            <h2 id="result-title">Citizen Health Information</h2>
            <p class="qr-muted">Personal and medical information is displayed only inside the authenticated BHW portal.</p>

            <div id="qr-empty" class="qr-empty">
                <div style="font-size: 34px; margin-bottom: 8px;">▦</div>
                <strong>No citizen scanned yet</strong>
                <div style="margin-top: 6px; font-size: 13px;">The verified citizen's information will appear here.</div>
            </div>

            <div id="qr-results" class="qr-results" hidden>
                <div id="citizen-profile"></div>
                <div id="vaccination-results"></div>
                <div id="medication-results"></div>
                <div id="health-record-results"></div>
                <div id="diagnosis-entry" class="qr-section"></div>
            </div>
        </section>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const startButton = document.getElementById('start-scanner');
    const stopButton = document.getElementById('stop-scanner');
    const statusBox = document.getElementById('qr-status');
    const manualInput = document.getElementById('manual-token');
    const manualButton = document.getElementById('manual-lookup');
    const imageInput = document.getElementById('qr-image');
    const emptyState = document.getElementById('qr-empty');
    const results = document.getElementById('qr-results');

    let scanner = null;
    let cameraRunning = false;
    let lookupInProgress = false;
    let scanHandled = false;

    const lookupUrlTemplate = @json(route('qr.scanner.lookup', ['token' => 'TOKEN_PLACEHOLDER']));

    function setStatus(message, type = '') {
        statusBox.textContent = message;
        statusBox.className = 'qr-status' + (type ? ' ' + type : '');
    }

    function escapeHtml(value) {
        return String(value ?? 'N/A').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
    }

    function getToken(rawValue) {
        const value = String(rawValue || '').trim();
        if (/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(value)) {
            return value;
        }

        try {
            const url = new URL(value, window.location.origin);
            const match = url.pathname.match(/\/(?:citizen\/qr|health-id)\/([0-9a-f-]{36})(?:\/|$)/i);
            return match ? match[1] : null;
        } catch (error) {
            return null;
        }
    }

    function renderTable(title, columns, rows) {
        let html = `<div class="qr-section"><h3>${escapeHtml(title)}</h3>`;
        if (!rows || rows.length === 0) {
            return html + '<div class="qr-no-records">No records found.</div></div>';
        }

        html += '<div class="qr-table-wrap"><table class="qr-table"><thead><tr>';
        columns.forEach(column => html += `<th>${escapeHtml(column.label)}</th>`);
        html += '</tr></thead><tbody>';

        rows.forEach(row => {
            html += '<tr>';
            columns.forEach(column => html += `<td>${escapeHtml(row[column.key])}</td>`);
            html += '</tr>';
        });

        return html + '</tbody></table></div></div>';
    }

    function renderCitizen(data) {
        const citizen = data.citizen;
        const initials = `${citizen.first_name || ''} ${citizen.last_name || ''}`.trim()
            .split(/\s+/).map(part => part.charAt(0)).join('').slice(0, 2).toUpperCase();
        const avatar = citizen.photo_url
            ? `<img class="qr-avatar" src="${escapeHtml(citizen.photo_url)}" alt="Citizen photo">`
            : `<div class="qr-avatar" aria-label="No citizen photo">${escapeHtml(initials || '?')}</div>`;

        document.getElementById('citizen-profile').innerHTML = `
            <div class="qr-profile">
                ${avatar}
                <div>
                    <h3>${escapeHtml(citizen.full_name)}</h3>
                    <div class="qr-verified">✓ Verified record found</div>
                    <div class="qr-muted" style="margin-top: 5px;">Citizen ID: ${escapeHtml(citizen.id)}</div>
                </div>
            </div>
                <div class="qr-profile-actions">
                    <a class="qr-action-link" href="/citizendetails/${encodeURIComponent(citizen.id)}">Edit / Manage Citizen Details</a>
                    <a class="qr-action-link secondary" href="/citizen/${encodeURIComponent(citizen.id)}/e-card" target="_blank" rel="noopener">Open Health Card</a>
                </div>
            </div>
            <div class="qr-detail-group"><h4>Personal & Contact Details</h4><div class="qr-info-grid">
                <div class="qr-info-item"><small>Date of birth</small><strong>${escapeHtml(citizen.birth_date)}</strong></div>
                <div class="qr-info-item"><small>Age</small><strong>${escapeHtml(citizen.age)}</strong></div>
                <div class="qr-info-item"><small>Sex</small><strong>${escapeHtml(citizen.sex)}</strong></div>
                <div class="qr-info-item"><small>Contact number</small><strong>${escapeHtml(citizen.contact_number)}</strong></div>
                <div class="qr-info-item"><small>Purok</small><strong>${escapeHtml(citizen.purok)}</strong></div>
                <div class="qr-info-item"><small>Family ID</small><strong>${escapeHtml(citizen.family_id)}</strong></div>
            </div></div>
            <div class="qr-detail-group"><h4>Health & Household Profile</h4><div class="qr-info-grid">
                <div class="qr-info-item"><small>Height</small><strong>${escapeHtml(citizen.height)}${citizen.height && citizen.height !== 'N/A' ? ' cm' : ''}</strong></div>
                <div class="qr-info-item"><small>Weight</small><strong>${escapeHtml(citizen.weight)}${citizen.weight && citizen.weight !== 'N/A' ? ' kg' : ''}</strong></div>
                <div class="qr-info-item"><small>Housing status</small><strong>${escapeHtml(citizen.housing_status)}</strong></div>
                <div class="qr-info-item"><small>Pregnant</small><strong>${escapeHtml(citizen.pregnant)}</strong></div>
                <div class="qr-info-item"><small>Taking pills</small><strong>${escapeHtml(citizen.in_pills)}</strong></div>
                <div class="qr-info-item"><small>Citizen status</small><strong>${escapeHtml(citizen.is_deceased)}</strong></div>
            </div></div>`;

        document.getElementById('vaccination-results').innerHTML = renderTable('Vaccination History', [
            {key: 'vaccine_name', label: 'Vaccine'},
            {key: 'dose_number', label: 'Dose'},
            {key: 'date_administered', label: 'Date'},
            {key: 'administered_by', label: 'Administered by'}
        ], data.vaccinations);

        document.getElementById('medication-results').innerHTML = renderTable('Medication History', [
            {key: 'medicine_name', label: 'Medicine'},
            {key: 'dosage', label: 'Dosage'},
            {key: 'quantity_dispensed', label: 'Quantity'},
            {key: 'date_dispensed', label: 'Date'}
        ], data.medications);

        document.getElementById('health-record-results').innerHTML = renderTable('Health Records', [
            {key: 'diagnosis', label: 'Diagnosis'},
            {key: 'record_date', label: 'Record date'},
            {key: 'comments', label: 'Notes'}
        ], data.health_records);

        document.getElementById('diagnosis-entry').innerHTML = `
            <div class="qr-detail-group">
                <h4>Add Diagnosis / Health Record</h4>
                <form id="qr-diagnosis-form" class="qr-diagnosis-form">
                    <input type="hidden" name="citizen_id" value="${escapeHtml(citizen.id)}">
                    <div><label for="qr-diagnosis">Diagnosis *</label><input id="qr-diagnosis" name="diagnosis" required maxlength="255" placeholder="Enter diagnosis"></div>
                    <div><label for="qr-record-date">Record date *</label><input id="qr-record-date" name="record_date" type="date" required value="${new Date().toISOString().slice(0,10)}"></div>
                    <div class="full"><label for="qr-comments">Notes / Comments</label><textarea id="qr-comments" name="comments" placeholder="Symptoms, notes, or follow-up instructions"></textarea></div>
                    <div class="full"><button class="qr-btn qr-btn-primary" type="submit" id="qr-save-diagnosis">Save Diagnosis</button></div>
                </form>
            </div>`;
        document.getElementById('qr-diagnosis-form').addEventListener('submit', saveDiagnosis);
        emptyState.hidden = true;
        results.hidden = false;
    }

    async function saveDiagnosis(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = document.getElementById('qr-save-diagnosis');
        button.disabled = true; button.textContent = 'Saving...';
        try {
            const response = await fetch(@json(route('qr.scanner.diagnosis.store')), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': @json(csrf_token()) },
                credentials: 'same-origin',
                body: new FormData(form)
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'Unable to save diagnosis.');
            setStatus('Diagnosis saved successfully. Refreshing the citizen health record...', 'success');
            // Reload the current citizen using the token retained after a successful scan.
            if (window.currentCitizenQrToken) await lookupCitizen(window.currentCitizenQrToken);
            else setStatus('Diagnosis saved successfully. Scan the citizen QR again to refresh the records.', 'success');
        } catch (error) {
            setStatus(error.message || 'Unable to save diagnosis.', 'error');
        } finally {
            button.disabled = false; button.textContent = 'Save Diagnosis';
        }
    }

    async function lookupCitizen(rawValue) {
        if (lookupInProgress) return;
        const token = getToken(rawValue);

        if (!token) {
            setStatus('Invalid QR content. Scan a Health ID QR code or enter a valid token.', 'error');
            return;
        }

        lookupInProgress = true;
        setStatus('Checking the QR code against the citizen database...');
        try {
            const lookupUrl = lookupUrlTemplate.replace('TOKEN_PLACEHOLDER', encodeURIComponent(token));
            const response = await fetch(lookupUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            let payload = {};
            try { payload = await response.json(); } catch (error) {}

            if (!response.ok) {
                throw new Error(payload.message || (response.status === 404
                    ? 'No citizen record matches this QR code.'
                    : 'Unable to verify this QR code. Please try again.'));
            }

            window.currentCitizenQrToken = token;
            renderCitizen(payload);
            setStatus('Citizen verified. Review the information below according to your authorized duties.', 'success');
            manualInput.value = '';
        } catch (error) {
            setStatus(error.message || 'An unexpected error occurred while checking the QR code.', 'error');
        } finally {
            lookupInProgress = false;
        }
    }

    async function stopCamera(showMessage = true) {
        if (scanner && cameraRunning) {
            try { await scanner.stop(); } catch (error) {}
            try { await scanner.clear(); } catch (error) {}
        }
        cameraRunning = false;
        startButton.disabled = false;
        stopButton.disabled = true;
        if (showMessage) setStatus('Camera stopped.');
    }

    startButton.addEventListener('click', async () => {
        if (typeof Html5Qrcode === 'undefined') {
            setStatus('The scanner library could not load. Check your internet connection, then refresh the page.', 'error');
            return;
        }

        if (!window.isSecureContext) {
            setStatus('Live camera scanning requires HTTPS. Open the HTTPS version of this website or use localhost.', 'error');
            return;
        }

        if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            setStatus('This browser does not provide camera access. Use HTTPS, update the browser, or scan a saved QR image below.', 'error');
            return;
        }

        startButton.disabled = true;
        scanHandled = false;
        setStatus('Requesting camera access...');

        try {
            scanner = new Html5Qrcode('qr-reader');
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 230, height: 230 }, aspectRatio: 1.0 },
                async decodedText => {
                    if (lookupInProgress || scanHandled) return;
                    scanHandled = true;
                    await stopCamera(false);
                    await lookupCitizen(decodedText);
                },
                () => {}
            );
            cameraRunning = true;
            stopButton.disabled = false;
            startButton.disabled = true;
            setStatus('Camera is active. Point it at the citizen Health ID QR code.');
        } catch (error) {
            cameraRunning = false;
            startButton.disabled = false;
            stopButton.disabled = true;
            const errorName = error?.name || '';
            const message = errorName === 'NotAllowedError' || errorName === 'PermissionDeniedError'
                ? 'Camera permission was denied. Allow camera access for this website in the browser address-bar settings, then try again.'
                : errorName === 'NotFoundError' || errorName === 'DevicesNotFoundError'
                    ? 'No camera was found on this device. Use the saved QR image option below.'
                    : errorName === 'NotReadableError' || errorName === 'TrackStartError'
                        ? 'The camera is busy or unavailable. Close other camera apps and try again.'
                        : 'Unable to start the camera. Confirm this page uses HTTPS and that the browser allows camera access.';
            setStatus(message, 'error');
        }
    });

    stopButton.addEventListener('click', () => stopCamera());
    imageInput.addEventListener('change', async event => {
        const file = event.target.files?.[0];
        if (!file || typeof Html5Qrcode === 'undefined') return;

        try {
            if (cameraRunning) await stopCamera(false);
            scanner = new Html5Qrcode('qr-reader');
            setStatus('Reading the selected QR image...');
            const decodedText = await scanner.scanFile(file, true);
            await scanner.clear();
            await lookupCitizen(decodedText);
        } catch (error) {
            setStatus('The selected image does not contain a readable Health ID QR code.', 'error');
        } finally {
            imageInput.value = '';
        }
    });
    manualButton.addEventListener('click', () => lookupCitizen(manualInput.value));
    manualInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') lookupCitizen(manualInput.value);
    });

    window.addEventListener('pagehide', () => {
        if (scanner && cameraRunning) scanner.stop().catch(() => {});
    });
});
</script>
@endsection
