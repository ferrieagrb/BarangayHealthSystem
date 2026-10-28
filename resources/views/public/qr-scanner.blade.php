<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner | Barangay Amuyong</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
    <style>
        .public-qr { color: #172033; }
        .public-qr-layout { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: 20px; align-items: start; }
        .public-qr-panel { min-width: 0; padding: 22px; background: #fff; border: 1px solid #dbe3ee; border-radius: 14px; box-shadow: 0 5px 18px rgba(15, 23, 42, .06); }
        .public-qr-panel h2 { margin: 0 0 8px; color: #173b82; font-size: 19px; }
        .public-qr-muted { margin: 0 0 14px; color: #526174; font-size: 14px; line-height: 1.55; }
        #public-qr-reader { width: 100%; max-width: 440px; min-height: 50px; margin: 16px auto; overflow: hidden; border-radius: 10px; background: #f1f5f9; }
        #public-qr-reader video { width: 100%; border-radius: 10px; }
        .public-qr-actions { display: flex; flex-wrap: wrap; gap: 9px; margin: 12px 0; }
        .public-qr-button { min-height: 44px; padding: 10px 15px; border: 0; border-radius: 8px; background: #155eef; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .public-qr-button.secondary { background: #e8eef7; color: #22334b; }
        .public-qr-button:disabled { opacity: .55; cursor: not-allowed; }
        .public-qr-input-group { margin-top: 16px; }
        .public-qr-input-group label { display: block; margin-bottom: 6px; color: #28374d; font-size: 14px; font-weight: 700; }
        .public-qr-input-group input { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #b9c5d5; border-radius: 8px; font: inherit; }
        .public-qr-status { margin-top: 12px; padding: 11px 13px; border-radius: 8px; background: #f1f5f9; color: #334155; font-size: 14px; }
        .public-qr-status.error { background: #fef2f2; color: #b91c1c; }
        .public-qr-status.success { background: #ecfdf5; color: #047857; }
        .public-qr-results[hidden] { display: none; }
        .public-qr-identity { padding-bottom: 14px; border-bottom: 1px solid #e2e8f0; }
        .public-qr-identity h2 { margin-bottom: 12px; color: #142b52; font-size: 23px; overflow-wrap: anywhere; }
        .public-qr-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .public-qr-fact small { display: block; margin-bottom: 3px; color: #64748b; }
        .public-qr-fact strong { overflow-wrap: anywhere; }
        .public-qr-record { margin-top: 18px; }
        .public-qr-record h3 { margin: 0 0 8px; color: #173b82; font-size: 15px; }
        .public-qr-table-wrap { overflow-x: auto; }
        .public-qr-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .public-qr-table th, .public-qr-table td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        .public-qr-table th { background: #f8fafc; color: #475569; white-space: nowrap; }
        .public-qr-empty { padding: 20px 10px; border: 1px dashed #cbd5e1; border-radius: 9px; color: #64748b; text-align: center; }
        @media (max-width: 760px) { .public-qr-layout { grid-template-columns: 1fr; } .public-qr-panel { padding: 17px; } }
        @media (max-width: 440px) { .public-qr-facts { grid-template-columns: 1fr; } .public-qr-actions > * { flex: 1 1 100%; } }
    </style>
@include('partials.favicon')
</head>
<body>
    <div class="topbar">
        <div class="container topbar-content">
            <div class="topbar-item">
                <i class="fa-solid fa-phone"></i>
                <span>BARANGAY HOTLINE: <strong>(+63) 958 789 1234</strong></span>
            </div>
            <div class="topbar-item highlight">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>EMERGENCY: <strong>(+63) 958 789 1234</strong></span>
            </div>
        </div>
    </div>

    <header class="main-header">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="brand">
                <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong Logo" class="brand-logo">
                <div class="brand-text">
                    <span class="brand-title">BARANGAY AMUYONG</span>
                    <span class="brand-subtitle">Health Services Portal</span>
                </div>
            </a>
            <nav class="nav-menu" aria-label="Public navigation">
                <ul>
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
                    <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
                    <li><a href="{{ route('public.qr-scanner') }}" class="active" aria-current="page">QR Scanner</a></li>
                    <li><a href="{{ route('login') }}" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero page-hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('{{ asset('images/mission.jpg') }}');">
        <div class="container hero-content">
            <span class="badge">Citizen Health ID</span>
            <h1 class="hero-title">Public <br><span class="text-accent">QR Scanner</span></h1>
            <p class="hero-description">Scan a Citizen Health ID QR code to view the health information the citizen has chosen to share. No login is required.</p>
        </div>
    </section>

    <main class="container main-content">
        <section class="content-section public-qr" aria-labelledby="public-qr-title">
            <div class="section-header">
                <h2 id="public-qr-title">Scan a Citizen ID</h2>
            </div>
            <p class="public-qr-muted">Use the camera on your phone or computer, select a saved QR image, or enter the QR link/token manually. Detailed records appear only while the citizen's sharing permission is active.</p>
            <div class="public-qr-layout">
                <div class="public-qr-panel">
                    <h2>QR Scanner</h2>
                    <div id="public-qr-reader" aria-label="QR camera preview"></div>
                    <div class="public-qr-actions">
                        <button type="button" class="public-qr-button" id="public-qr-start">Start camera</button>
                        <button type="button" class="public-qr-button secondary" id="public-qr-stop" disabled>Stop camera</button>
                    </div>
                    <div class="public-qr-input-group">
                        <label for="public-qr-image">Scan a saved QR image</label>
                        <input type="file" id="public-qr-image" accept="image/*">
                    </div>
                    <div class="public-qr-input-group">
                        <label for="public-qr-manual">Enter Health ID link or token</label>
                        <input type="text" id="public-qr-manual" autocomplete="off" placeholder="Paste the Citizen QR link or UUID">
                        <div class="public-qr-actions">
                            <button type="button" class="public-qr-button secondary" id="public-qr-submit">View health summary</button>
                        </div>
                    </div>
                    <div id="public-qr-status" class="public-qr-status" role="status" aria-live="polite">Ready to scan a Citizen ID QR code.</div>
                </div>
                <div class="public-qr-panel" id="public-qr-summary" aria-live="polite" aria-busy="false">
                    <h2>Citizen Health Summary</h2>
                    <div id="public-qr-placeholder" class="public-qr-empty">Scan a Citizen ID to display its recorded health summary here.</div>
                    <div id="public-qr-results" class="public-qr-results" hidden>
                        <div id="public-qr-identity" class="public-qr-identity"></div>
                        <div id="public-qr-health-records" class="public-qr-record"></div>
                        <div id="public-qr-vaccinations" class="public-qr-record"></div>
                        <div id="public-qr-maintenance" class="public-qr-record"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-col brand-col">
                <div class="footer-brand">
                    <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong Logo" class="footer-logo">
                    <span>BARANGAY AMUYONG</span>
                </div>
                <p class="footer-desc">Dedicated to delivering transparent and efficient public health services to our community.</p>
            </div>
            <div class="footer-col">
                <p class="footer-heading">Quick Links</p>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
                    <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
                    <li><a href="{{ route('public.qr-scanner') }}">QR Scanner</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <p class="footer-heading">Contacts</p>
                <ul class="footer-links">
                    <li><a href="{{ url('/contactdirectory') }}">Contact Directory</a></li>
                    <li><a href="{{ route('login') }}">Staff Login</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container"><p>&copy; {{ date('Y') }} Barangay Amuyong. All rights reserved.</p></div>
        </div>
    </footer>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startButton = document.getElementById('public-qr-start');
            const stopButton = document.getElementById('public-qr-stop');
            const statusBox = document.getElementById('public-qr-status');
            const imageInput = document.getElementById('public-qr-image');
            const manualInput = document.getElementById('public-qr-manual');
            const summary = document.getElementById('public-qr-summary');
            const placeholder = document.getElementById('public-qr-placeholder');
            const results = document.getElementById('public-qr-results');
            const lookupUrlTemplate = @json(route('health-id.summary', ['token' => 'TOKEN_PLACEHOLDER']));
            let scanner = null;
            let cameraRunning = false;
            let lookupInProgress = false;
            let scanHandled = false;

            function setStatus(message, type = '') {
                statusBox.textContent = message;
                statusBox.className = 'public-qr-status' + (type ? ' ' + type : '');
            }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, character => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                }[character]));
            }

            function getToken(rawValue) {
                const value = String(rawValue || '').trim();
                const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                if (uuidPattern.test(value)) return value;

                try {
                    const url = new URL(value, window.location.origin);
                    const match = url.pathname.match(/\/health-id\/([0-9a-f-]{36})(?:\/|$)/i);
                    return match && uuidPattern.test(match[1]) ? match[1] : null;
                } catch (error) {
                    return null;
                }
            }

            function showNoResults(message) {
                results.hidden = true;
                placeholder.hidden = false;
                placeholder.textContent = message;
                document.getElementById('public-qr-identity').textContent = '';
                document.getElementById('public-qr-health-records').textContent = '';
                document.getElementById('public-qr-vaccinations').textContent = '';
                document.getElementById('public-qr-maintenance').textContent = '';
            }

            function renderSummary(data) {
                const citizen = data.citizen;
                const displayValue = (value, suffix = '') => value === null || value === undefined || value === ''
                    ? 'Not recorded'
                    : `${value}${suffix}`;
                const formattedMeasure = (value, suffix) => value === null || value === undefined || value === ''
                    ? 'Not recorded'
                    : `${Number.isFinite(Number(value)) ? Number(value).toFixed(1) : value}${suffix}`;
                const statusValue = value => value === null || value === undefined
                    ? 'Not recorded'
                    : (value ? 'Yes' : 'No');
                document.getElementById('public-qr-identity').innerHTML = `
                    <h2>${escapeHtml(citizen.name)}</h2>
                    <div class="public-qr-facts">
                        <div class="public-qr-fact"><small>Date of birth</small><strong>${escapeHtml(displayValue(citizen.birth_date))}</strong></div>
                        <div class="public-qr-fact"><small>Age</small><strong>${escapeHtml(displayValue(citizen.age))}</strong></div>
                        <div class="public-qr-fact"><small>Sex</small><strong>${escapeHtml(displayValue(citizen.sex))}</strong></div>
                        <div class="public-qr-fact"><small>Purok</small><strong>${escapeHtml(displayValue(citizen.purok))}</strong></div>
                        <div class="public-qr-fact"><small>Family</small><strong>${escapeHtml(displayValue(citizen.family_name))}</strong></div>
                        <div class="public-qr-fact"><small>Subgroup</small><strong>${escapeHtml(displayValue(citizen.subgroup))}</strong></div>
                        <div class="public-qr-fact"><small>Height</small><strong>${escapeHtml(displayValue(citizen.height, ' cm'))}</strong></div>
                        <div class="public-qr-fact"><small>Weight</small><strong>${escapeHtml(displayValue(citizen.weight, ' kg'))}</strong></div>
                        <div class="public-qr-fact"><small>Housing status</small><strong>${escapeHtml(displayValue(citizen.housing_status))}</strong></div>
                        <div class="public-qr-fact"><small>Pregnancy status</small><strong>${escapeHtml(statusValue(citizen.pregnant))}</strong></div>
                        <div class="public-qr-fact"><small>Taking pills</small><strong>${escapeHtml(statusValue(citizen.taking_pills))}</strong></div>
                        <div class="public-qr-fact"><small>Deceased status</small><strong>${escapeHtml(citizen.deceased === null || citizen.deceased === undefined ? 'Not recorded' : (citizen.deceased ? 'Deceased' : 'Living'))}</strong></div>
                    </div>`;

                const healthRecords = Array.isArray(data.health_records) ? data.health_records : [];
                document.getElementById('public-qr-health-records').innerHTML = `
                    <h3>Diagnosis &amp; Health Records</h3>
                    ${healthRecords.length
                        ? `<div class="public-qr-table-wrap"><table class="public-qr-table"><thead><tr><th>Date recorded</th><th>Diagnosis</th><th>BMI</th><th>Weight / Height</th><th>Temperature</th><th>Blood pressure</th><th>Comments / Notes</th></tr></thead><tbody>
                            ${healthRecords.map(record => `<tr>
                                <td>${escapeHtml(displayValue(record.record_date))}</td>
                                <td>${escapeHtml(displayValue(record.diagnosis))}</td>
                                <td>${escapeHtml(formattedMeasure(record.bmi, ' kg/m²'))}</td>
                                <td>${escapeHtml(displayValue(record.weight, ' kg'))} / ${escapeHtml(displayValue(record.height, ' cm'))}</td>
                                <td>${escapeHtml(formattedMeasure(record.temperature, ' °C'))}</td>
                                <td>${escapeHtml(displayValue(record.blood_pressure))}</td>
                                <td>${escapeHtml(displayValue(record.comments))}</td>
                            </tr>`).join('')}
                        </tbody></table></div>`
                        : '<p class="public-qr-muted">No diagnosis or health records found.</p>'}`;

                const vaccinations = Array.isArray(data.vaccinations) ? data.vaccinations : [];
                document.getElementById('public-qr-vaccinations').innerHTML = `
                    <h3>Immunization History</h3>
                    ${vaccinations.length
                        ? `<div class="public-qr-table-wrap"><table class="public-qr-table"><thead><tr><th>Vaccine</th><th>Product name / Dose number</th><th>Date administered</th><th>Healthcare professional / Clinic site</th></tr></thead><tbody>
                            ${vaccinations.map(record => `<tr><td>${escapeHtml(displayValue(record.vaccine_name))}</td><td>${escapeHtml(displayValue(record.vaccine_name))} — ${escapeHtml(displayValue(record.dose_number))}</td><td>${escapeHtml(displayValue(record.date_administered))}</td><td>${escapeHtml(displayValue(record.administered_by))}</td></tr>`).join('')}
                        </tbody></table></div>`
                        : '<p class="public-qr-muted">No vaccinations are recorded.</p>'}`;

                const maintenance = Array.isArray(data.maintenance_medicines) ? data.maintenance_medicines : [];
                document.getElementById('public-qr-maintenance').innerHTML = `
                    <h3>Maintenance Medicines</h3>
                    ${maintenance.length
                        ? `<div class="public-qr-table-wrap"><table class="public-qr-table"><thead><tr><th>Medicine</th><th>Quantity</th><th>Dosage</th><th>Frequency</th><th>Start date</th><th>End date / Status</th><th>Notes</th></tr></thead><tbody>
                            ${maintenance.map(record => `<tr>
                                <td>${escapeHtml(displayValue(record.medicine_name))}</td>
                                <td>${escapeHtml(displayValue(record.quantity_dispensed))}</td>
                                <td>${escapeHtml(displayValue(record.dosage))}</td>
                                <td>${escapeHtml(displayValue(record.frequency))}</td>
                                <td>${escapeHtml(displayValue(record.start_date))}</td>
                                <td>${escapeHtml(record.end_date ? record.end_date : 'Ongoing')}</td>
                                <td>${escapeHtml(displayValue(record.notes))}</td>
                            </tr>`).join('')}
                        </tbody></table></div>`
                        : '<p class="public-qr-muted">No maintenance medicines recorded.</p>'}`;

                placeholder.hidden = true;
                results.hidden = false;
            }

            async function lookupCitizen(rawValue) {
                if (lookupInProgress) return;
                const token = getToken(rawValue);
                if (!token) {
                    showNoResults('Scan a valid Citizen Health ID QR code to view recorded information.');
                    setStatus('This QR code is not a valid Citizen Health ID.', 'error');
                    return;
                }

                lookupInProgress = true;
                summary.setAttribute('aria-busy', 'true');
                showNoResults('Loading the citizen health summary...');
                setStatus('Looking up the Citizen ID...');
                try {
                    const response = await fetch(lookupUrlTemplate.replace('TOKEN_PLACEHOLDER', encodeURIComponent(token)), {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'No citizen record matches this QR code.');
                    renderSummary(data);
                    setStatus('Health summary loaded. Scan another Citizen ID to view its information.', 'success');
                    manualInput.value = '';
                } catch (error) {
                    showNoResults(error.message || 'Unable to load the citizen health summary.');
                    setStatus(error.message || 'Unable to load the citizen health summary.', 'error');
                } finally {
                    summary.setAttribute('aria-busy', 'false');
                    lookupInProgress = false;
                }
            }

            async function stopCamera() {
                if (scanner && cameraRunning) {
                    await scanner.stop();
                    cameraRunning = false;
                    await scanner.clear();
                    scanner = null;
                    stopButton.disabled = true;
                    startButton.disabled = false;
                    setStatus('Camera stopped. Scan another Citizen ID or restart the camera.');
                }
            }

            startButton.addEventListener('click', async () => {
                if (!window.isSecureContext) {
                    setStatus('Camera access requires HTTPS, except on localhost. Use a saved QR image or enter the token manually.', 'error');
                    return;
                }
                if (typeof Html5Qrcode === 'undefined') {
                    setStatus('The camera scanner could not load. Use a saved QR image or enter the token manually.', 'error');
                    return;
                }

                startButton.disabled = true;
                scanHandled = false;
                setStatus('Requesting camera access...');
                try {
                    scanner = new Html5Qrcode('public-qr-reader');
                    await scanner.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 230, height: 230 }, aspectRatio: 1 },
                        async decodedText => {
                            if (scanHandled) return;
                            scanHandled = true;
                            await stopCamera();
                            await lookupCitizen(decodedText);
                        },
                        () => {}
                    );
                    cameraRunning = true;
                    stopButton.disabled = false;
                    setStatus('Camera active. Point it at a Citizen ID QR code.');
                } catch (error) {
                    cameraRunning = false;
                    startButton.disabled = false;
                    stopButton.disabled = true;
                    if (scanner) {
                        await scanner.clear();
                        scanner = null;
                    }
                    setStatus(error.message || 'Unable to start the camera. Check camera permissions and try again.', 'error');
                }
            });

            stopButton.addEventListener('click', async () => {
                try {
                    await stopCamera();
                } catch (error) {
                    setStatus('Unable to stop the camera cleanly. Close this page to release the camera.', 'error');
                }
            });

            imageInput.addEventListener('change', async event => {
                const file = event.target.files?.[0];
                if (!file) return;
                try {
                    if (cameraRunning) await stopCamera();
                    if (typeof Html5Qrcode === 'undefined') throw new Error('Image scanning is unavailable. Enter the QR token manually.');
                    scanner = new Html5Qrcode('public-qr-reader');
                    await lookupCitizen(await scanner.scanFile(file, true));
                    await scanner.clear();
                    scanner = null;
                } catch (error) {
                    setStatus(error.message || 'The selected image does not contain a readable Citizen ID QR code.', 'error');
                    if (scanner && !cameraRunning) {
                        await scanner.clear();
                        scanner = null;
                    }
                } finally {
                    imageInput.value = '';
                }
            });

            document.getElementById('public-qr-submit').addEventListener('click', () => lookupCitizen(manualInput.value));
            manualInput.addEventListener('keydown', event => {
                if (event.key === 'Enter') lookupCitizen(manualInput.value);
            });
            window.addEventListener('pagehide', () => {
                if (scanner && cameraRunning) scanner.stop().catch(() => {});
            });
        });
    </script>
</body>
</html>
