@extends('templates.citizen')
@section('CSSown')
<style>
/* =========================================================
   CITIZEN DASHBOARD
   ========================================================= */

.c-home {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: clamp(18px, 2.4vw, 34px);
    color: #172033;
}

.c-home p {
    line-height: 1.55;
}

.c-muted {
    color: #475569;
    font-size: 15px;
}

.c-section {
    margin: 0 0 100px;
    scroll-margin-top: 145px;
}

.c-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 13px;
}

.c-heading h2 {
    margin: 0;
    color: #173b82;
    font-size: clamp(21px, 1.6vw, 25px);
    font-weight: 800;
}

.c-panel {
    overflow: hidden;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    box-shadow: 0 5px 18px #0f172a0c;
}

.c-btn {
    display: inline-block;
    padding: 13px 18px;
    border-radius: 8px;
    background: #1d4ed8;
    color: #fff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
}

.c-empty {
    padding: 26px;
    color: #64748b;
    text-align: center;
}

/* Announcement components use their existing base styles. */

/* Health information and records */
.c-health {
    padding: 60px;
}

.c-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 11px;
    margin-bottom: 22px;
}

.c-stat {
    min-width: 0;
    padding: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
}

.c-stat span {
    display: block;
    margin-bottom: 5px;
    color: #64748b;
    font-size: 13px;
}

.c-stat strong {
    font-size: 14px;
    overflow-wrap: anywhere;
}

.c-record {
    margin-top: 20px;
}

.c-record h3 {
    margin: 0 0 9px;
    color: #173b82;
    font-size: 14px;
    font-weight: 800;
}

.c-tablewrap {
    overflow-x: auto;
}

.c-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.c-table th,
.c-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.c-table th {
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    text-transform: uppercase;
}

.c-table .none {
    padding: 15px;
    color: #64748b;
    text-align: center;
}

.c-actions {
    display: flex;
    justify-content: flex-end;
    padding: 0 23px 20px;
}

/* Referral section */
.c-referral {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px;
    background: linear-gradient(120deg, #f0fdf4, #fff);
}

.c-ref-icon {
    display: grid;
    flex-shrink: 0;
    width: 54px;
    height: 54px;
    place-items: center;
    border-radius: 14px;
    background: #dcfce7;
    color: #15803d;
    font-size: 27px;
}

.c-ref-copy {
    flex: 1;
}

.c-ref-copy h3 {
    margin: 0 0 5px;
    color: #166534;
    font-size: 17px;
}

.c-ref-copy p {
    margin: 0;
    color: #475569;
    font-size: 13px;
}

.c-green {
    background: #15803d;
}

/* =========================================================
   CITIZEN HEALTH CARD
   ========================================================= */

.c-home .c-id-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 1000px;
    height: auto;
    margin: 0 auto;
    overflow: hidden;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
}

/* Blue header and barangay logo */
.c-home .c-id-header {
    display: flex;
    align-items: center;
    gap: 16px;
    min-height: 105px;
    padding: 15px 22px;
    box-sizing: border-box;
    border-bottom: 3px solid #bfdbfe;
    background: linear-gradient(110deg, #102b78, #1d4c9c);
    color: #fff;
}

.c-home .c-id-header > img {
    flex: 0 0 66px;
    width: 66px;
    height: 66px;
    padding: 3px;
    box-sizing: border-box;
    border: 2px solid #fff;
    border-radius: 50%;
    background: #fff;
    object-fit: contain;
}

.c-home .c-id-header-text {
    flex: 1;
    min-width: 0;
}

.c-home .c-id-header-text h3 {
    margin: 0 0 4px;
    color: #fff;
    font-size: 27px;
    font-weight: 900;
    line-height: 1.15;
}

.c-home .c-id-header-text strong {
    display: block;
    color: #fff;
    font-size: 13px;
}

.c-home .c-id-header-text small {
    display: block;
    margin-top: 5px;
    color: #dbeafe;
    font-size: 8px;
    font-weight: 700;
}

/* QR code in the header */
.c-home .c-id-qr {
    display: flex;
    flex: 0 0 76px;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    text-align: center;
}

.c-home .c-id-qr-frame {
    padding: 3px;
    border-radius: 5px;
    background: #fff;
}

.c-home .c-id-qr-frame img {
    display: block;
    width: 65px;
    height: 65px;
    object-fit: contain;
}

.c-home .c-id-qr > span {
    color: #fff;
    font-size: 7px;
    font-weight: 800;
}

/* Citizen photo and personal information */
.c-home .c-id-body {
    display: grid;
    grid-template-columns: 120px minmax(0, 1fr);
    align-items: center;
    gap: 22px;
    padding: 22px;
    box-sizing: border-box;
    background:
        linear-gradient(
            135deg,
            transparent 82%,
            #f3f6fc 82%,
            #f3f6fc 90%,
            transparent 90%
        ),
        #fff;
}

.c-home .c-id-body .c-photo {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 120px;
    height: 150px;
    overflow: hidden;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    background: #f1f5f9;
}

.c-home .c-id-body .c-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.c-home .c-id-body .c-photo span {
    font-size: 30px;
    font-weight: 900;
}

.c-home .c-id-body .c-photo small {
    color: #64748b;
    font-size: 9px;
}

.c-home .c-id-details {
    min-width: 0;
}

.c-home .c-id-details > h3 {
    margin: 0 0 13px;
    color: #173b82;
    font-size: 17px;
    font-weight: 900;
}

.c-home .c-id-field {
    min-width: 0;
    margin-bottom: 10px;
    overflow-wrap: anywhere;
}

.c-home .c-id-field span {
    display: block;
    margin-bottom: 4px;
    color: #64748b;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.3px;
}

.c-home .c-id-field strong {
    color: #172554;
    font-size: 12px;
    font-weight: 800;
}

.c-home .c-id-field .c-id-number {
    color: #17469a;
    font-size: 15px;
}

.c-home .c-id-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 16px;
}

/* Card footer */
.c-home .c-id-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 11px 22px;
    border-top: 1px solid #dbeafe;
    background: #f8fafc;
    color: #334e85;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.3px;
}

/* Print / full health card button outside the card */
.c-home .c-id-actions-outside {
    display: flex;
    justify-content: flex-end;
    max-width: 1000px;
    margin: 12px auto 0;
}

.c-home .c-id-actions-outside .c-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    margin: 0;
    padding: 11px 16px;
    box-sizing: border-box;
    border-radius: 7px;
    background: #1d4ed8;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    text-align: center;
    text-decoration: none;
}

/* =========================================================
   RESPONSIVE STYLES
   ========================================================= */

@media (max-width: 760px) {
    .c-home {
        padding: 20px 14px 28px;
    }

    .c-heading {
        align-items: flex-start;
    }

    .c-heading h2 {
        font-size: 18px;
    }

    .c-ann-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .c-ann-cover {
        min-height: 125px;
        padding: 17px;
    }

    .c-ann-body {
        padding: 15px;
    }

    .c-section {
        scroll-margin-top: 135px;
    }

    .c-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .c-referral {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .c-ref-copy {
        min-width: 65%;
    }
}

@media (max-width: 600px) {
    .c-home .c-id-card {
        max-width: 100%;
        border-radius: 10px;
    }

    .c-home .c-id-header {
        gap: 9px;
        min-height: 0;
        padding: 12px;
    }

    .c-home .c-id-header > img {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
    }

    .c-home .c-id-header-text h3 {
        font-size: 18px;
    }

    .c-home .c-id-header-text strong {
        font-size: 10px;
    }

    .c-home .c-id-header-text small {
        font-size: 6px;
        line-height: 1.4;
    }

    .c-home .c-id-qr {
        flex-basis: 49px;
    }

    .c-home .c-id-qr-frame img {
        width: 43px;
        height: 43px;
    }

    .c-home .c-id-qr > span {
        font-size: 6px;
    }

    .c-home .c-id-body {
        grid-template-columns: 68px minmax(0, 1fr);
        align-items: start;
        gap: 12px;
        padding: 14px 11px;
    }

    .c-home .c-id-body .c-photo {
        width: 68px;
        height: 88px;
    }

    .c-home .c-id-body .c-photo span {
        font-size: 22px;
    }

    .c-home .c-id-details > h3 {
        margin-bottom: 10px;
        font-size: 14px;
    }

    .c-home .c-id-field span {
        font-size: 8px;
    }

    .c-home .c-id-field strong {
        font-size: 11px;
    }

    .c-home .c-id-field .c-id-number {
        font-size: 13px;
    }

    .c-home .c-id-details-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .c-home .c-id-footer {
        padding: 9px 11px;
        font-size: 7px;
        letter-spacing: 0;
    }

    .c-home .c-id-actions-outside {
        margin-top: 10px;
    }

    .c-home .c-id-actions-outside .c-btn {
        width: 100%;
    }

    .c-section {
        scroll-margin-top: 125px;
    }

    .c-muted {
        font-size: 13px;
    }

    .c-ann-cover {
        min-height: 112px;
    }

    .c-ann-cover h3 {
        font-size: 18px;
    }

    .c-ann-body p {
        font-size: 13px;
    }
}

@media (max-width: 420px) {
    .c-home {
        padding: 18px 12px 24px;
    }

    .c-heading h2 {
        font-size: 19px;
    }

    .c-stats {
        grid-template-columns: 1fr;
    }

    .c-health {
        padding: 16px;
    }

    .c-actions {
        padding: 0 16px 17px;
    }
}

@media (max-width: 360px) {
    .c-home .c-id-header {
        gap: 6px;
        padding: 9px;
    }

    .c-home .c-id-header > img {
        flex-basis: 35px;
        width: 35px;
        height: 35px;
    }

    .c-home .c-id-header-text h3 {
        font-size: 15px;
    }

    .c-home .c-id-header-text strong {
        font-size: 9px;
    }

    .c-home .c-id-header-text small {
        font-size: 5px;
    }

    .c-home .c-id-qr {
        flex-basis: 42px;
    }

    .c-home .c-id-qr-frame img {
        width: 36px;
        height: 36px;
    }

    .c-home .c-id-footer {
        font-size: 6px;
    }
}
</style>
@endsection
@section('content')
    <div class="c-home">
        {{-- 1. ANNOUNCEMENTS --}}
        <section class="c-section" id="announcements">
            <div class="c-heading">
                <div>
                    <h2>Announcements</h2>
                    <p class="c-muted">Latest health center notices and community updates</p>
                </div>
                <a href="{{ route('citizen.announcements') }}"
                    style="font-size:13px;color:#1d4ed8;font-weight:700;text-decoration:none">
                    View all →
                </a>
            </div>
            @if(isset($announcements) && $announcements->count())
                <div class="c-ann-grid">
                    @foreach($announcements as $announcement)
                        <article class="c-ann-card">
                            <div class="c-ann-cover">
                                <span class="c-ann-label">📢 Health Advisory</span>
                                <h3>{{ $announcement->title }}</h3>
                            </div>
                            <div class="c-ann-body">
                                <p>
                                    {{ \Illuminate\Support\Str::limit(
                                    $announcement->content
                                    ?? $announcement->description
                                    ?? 'Please contact the Barangay Health Center for more information.',
                                    180
                                    ) }}
                                </p>
                                <div class="c-ann-footer">
                                    <span>
                                        {{ optional($announcement->created_at)->format('M d, Y') }}
                                    </span>
                                    <span>Barangay Amuyong</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="c-panel c-empty">
                    No announcements available right now.
                </div>
            @endif
        </section>
        

{{-- 2. CITIZEN CARD --}}
<section class="c-section" id="citizen-card">
    <div class="c-heading">
        <div>
            <h2>🪪 My Citizen Health Card</h2>
            <p class="c-muted">Your registered identity details</p>
        </div>
    </div>

    @if($citizen)
        {{-- ACTUAL ID CARD --}}
        <div class="c-panel c-id-card">

            {{-- BLUE HEADER --}}
            <div class="c-id-header">
                <img src="{{ asset('images/amuyong.png') }}"
                     alt="Barangay Amuyong Logo">

                <div class="c-id-header-text">
                    <h3>BHEAMS</h3>
                    <strong>BARANGAY AMUYONG</strong>
                    <small>BARANGAY HEALTH AND EMERGENCY MANAGEMENT SYSTEM</small>
                </div>

                {{-- QR CODE IN HEADER --}}
                <div class="c-id-qr">
                    <div class="c-id-qr-frame">
                        <img src="{{ $qrImage }}"
                             alt="Citizen Verification QR Code">
                    </div>
                    <span>SCAN TO VERIFY</span>
                </div>
            </div>

            {{-- ID BODY --}}
            <div class="c-id-body">

                {{-- CITIZEN PHOTO --}}
                <div class="c-photo">
                    @if($citizen->photo_path)
                        <img src="{{ asset('storage/'.$citizen->photo_path) }}"
                             alt="Citizen photo">
                    @else
                        <span>
                            {{ strtoupper(substr($citizen->Citizen_FName ?? 'C', 0, 1).substr($citizen->Citizen_LName ?? 'I', 0, 1)) }}
                        </span>
                        <small>NO PHOTO</small>
                    @endif
                </div>

                {{-- CITIZEN DETAILS --}}
                <div class="c-id-details">
                    <h3>CITIZEN HEALTH ID</h3>

                    <div class="c-id-field">
                        <span>FULL NAME</span>
                        <strong>
                            {{ strtoupper($citizen->Citizen_LName) }},
                            {{ strtoupper($citizen->Citizen_FName) }}
                        </strong>
                    </div>

                    <div class="c-id-field">
                        <span>CITIZEN IDENTIFICATION NUMBER</span>
                        <strong class="c-id-number">
                            C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}
                        </strong>
                    </div>

                    <div class="c-id-details-grid">
                        <div class="c-id-field">
                            <span>DATE OF BIRTH</span>
                            <strong>
                                {{ $citizen->Citizen_BirthDate
                                    ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('M d, Y')
                                    : 'N/A' }}
                            </strong>
                        </div>

                        <div class="c-id-field">
                            <span>CONTACT NUMBER</span>
                            <strong>{{ $citizen->Citizen_ContactNo ?: 'N/A' }}</strong>
                        </div>

                        <div class="c-id-field">
                            <span>ADDRESS / PUROK</span>
                            <strong>{{ $citizen->Citizen_Purok ?: 'N/A' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER AT THE BOTTOM OF THE ID --}}
            <div class="c-id-footer">
                <span>BARANGAY AMUYONG</span>
                <span>OFFICIAL CITIZEN IDENTIFICATION</span>
            </div>
        </div>

        {{-- BUTTON OUTSIDE THE ID CARD --}}
        <div class="c-id-actions-outside">
            <a class="c-btn"
               href="{{ route('citizen.ecard') }}">
                Open / Print Full Health Card
            </a>
        </div>

    @else
        <div class="c-panel c-empty">
            Your account is not linked to a citizen profile yet.
            Please contact the Barangay Health Worker.
        </div>
    @endif
</section>


        {{-- 3. FULL HEALTH INFORMATION --}}
        <section class="c-section" id="health-information">
            <div class="c-heading">
                <div>
                    <h2>Full Health Information</h2>
                    <p class="c-muted">Immunization and medicine records from the health center</p>
                </div>
            </div>
            <div class="c-panel c-health">
                @if($citizen)
                    <div class="c-stats">
                        <div class="c-stat">
                            <span>Citizen name</span>
                            <strong>{{ $citizen->Citizen_LName }}, {{ $citizen->Citizen_FName }}</strong>
                        </div>
                        <div class="c-stat">
                            <span>Date of birth / age</span>
                            <strong>{{ $citizen->Citizen_BirthDate ?: 'N/A' }} / {{ $citizen->Citizen_Age ?? 'N/A' }}</strong>
                        </div>
                        <div class="c-stat">
                            <span>Purok</span>
                            <strong>{{ $citizen->Citizen_Purok ?: 'N/A' }}</strong>
                        </div>
                        <div class="c-stat">
                            <span>Contact</span>
                            <strong>{{ $citizen->Citizen_ContactNo ?: 'N/A' }}</strong>
                        </div>
                    </div>
                    <div class="c-record">
                        <h3>Immunization History</h3>
                        <div class="c-tablewrap">
                            <table class="c-table">
                                <thead>
                                    <tr>
                                        <th>Vaccine</th>
                                        <th>Product / Dose</th>
                                        <th>Date administered</th>
                                        <th>Administered by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($citizen->vaccinations as $vax)<tr>
                                        <td>COVID-19 / Health Vax</td>
                                        <td>{{ $vax->vaccine_name }} — {{ $vax->dose_number }}</td>
                                        <td>{{ $vax->date_administered ?: 'N/A' }}</td>
                                        <td>{{ $vax->administered_by ?: 'N/A' }}</td>
                                    </tr>
                                @empty<tr>
                                    <td class="none" colspan="4">No vaccination records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="c-record">
                <h3>Dispensed Medicines</h3>
                <div class="c-tablewrap">
                    <table class="c-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Quantity</th>
                                <th>Date dispensed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citizen->medications as $med)<tr>
                                <td>{{ $med->medicine_name }}</td>
                                <td>{{ $med->dosage ?: 'N/A' }}</td>
                                <td>{{ $med->quantity_dispensed }}</td>
                                <td>{{ $med->date_dispensed ?: 'N/A' }}</td>
                            </tr>
                        @empty<tr>
                            <td class="none" colspan="4">No medication records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="c-actions" style="padding:20px 0 0">
        <a class="c-btn" href="{{ route('citizen.ecard') }}">View Printable Health Record</a>
    </div>
@else <div class="c-empty">Health information will appear here once your account is linked to a citizen profile.</div>
@endif
</div>
</section>
{{-- 4. REQUEST REFERRAL --}}
<section class="c-section" id="request-referral">
    <div class="c-heading">
        <div>
            <h2>Request a Referral</h2>
            <p class="c-muted">Need assistance being referred to another health facility?</p>
        </div>
    </div>
    <div class="c-panel c-referral">
        <div class="c-ref-icon">＋</div>
        <div class="c-ref-copy">
            <h3>Talk to your Barangay Health Worker</h3>
            <p>Contact or visit the BHW so your concern can be assessed and your referral can be recorded. This page currently provides referral guidance; an online referral submission form is not yet available. For emergencies, go to the nearest emergency facility.</p>
        </div>
        <a class="c-btn c-green" href="tel:+639587891234">Call Health Center</a>
    </div>
</section>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const nav = document.getElementById('citizenSectionNav');
    if (!nav) return;

    const links = [...nav.querySelectorAll('a[href^="#"]')];

    const sections = links
        .map(link => document.getElementById(
            link.getAttribute('href').substring(1)
        ))
        .filter(Boolean);

    if (!sections.length) return;

    function setActive(section) {
        links.forEach(link => {
            const active =
                link.getAttribute('href') === '#' + section.id;

            link.classList.toggle('active', active);

            if (active) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function updateActiveTab() {
        const header = document.querySelector('.top-bar');
        const headerHeight = header ? header.offsetHeight : 0;

        let current = sections[0];
        const activationPoint = headerHeight + 25;

        sections.forEach(section => {
            if (section.getBoundingClientRect().top <= activationPoint) {
                current = section;
            }
        });

        if (
            window.innerHeight + window.scrollY >=
            document.documentElement.scrollHeight - 5
        ) {
            current = sections[sections.length - 1];
        }

        setActive(current);
    }

    links.forEach(link => {
        link.addEventListener('click', function (event) {
            const target = document.getElementById(
                this.getAttribute('href').substring(1)
            );

            if (!target) return;

            event.preventDefault();
            setActive(target);

            const header = document.querySelector('.top-bar');
            const headerHeight = header ? header.offsetHeight : 0;

            const targetY =
                target.getBoundingClientRect().top +
                window.scrollY -
                headerHeight -
                12;

            window.scrollTo({
                top: Math.max(0, targetY),
                behavior: 'smooth'
            });
        });
    });

    window.addEventListener('scroll', updateActiveTab, {
        passive: true
    });

    window.addEventListener('resize', updateActiveTab);

    updateActiveTab();
});
</script>




@endsection
