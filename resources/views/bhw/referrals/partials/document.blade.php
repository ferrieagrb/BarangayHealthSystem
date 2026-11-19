<article class="referral-document" aria-label="Patient referral form">
    <table class="document-header" role="presentation">
        <tr>
            <td class="document-seal-cell"><img class="document-seal" src="{{ $forPdf ? \App\Models\SystemSetting::logoDataUri() : \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong seal"></td>
            <td class="document-identity">
                <div class="document-country">REPUBLIC OF THE PHILIPPINES</div>
                <div class="document-center-name">Barangay Amuyong Health Center</div>
                <div class="document-location">Municipality of Alfonso &nbsp;|&nbsp; Province of Cavite</div>
            </td>
            <td class="document-brand"><strong>BHEAMS</strong>HEALTH<br>MANAGEMENT<br>SYSTEM</td>
        </tr>
    </table>
    <h2 class="document-title">Patient Referral Form</h2>
    <p class="document-subtitle">Transfer of patient information to the receiving healthcare facility.</p>
    <table role="presentation"><tr>
        <td class="document-field"><span class="document-label">REFERRAL NO.</span><span class="document-value">{{ $referral->exists ? 'REF-'.($referral->referred_at?->format('Y') ?? substr($referral->date_of_referral, 0, 4)).'-'.str_pad($referral->id, 6, '0', STR_PAD_LEFT) : 'Assigned on generation' }}</span></td>
        <td class="document-field"><span class="document-label">DATE AND TIME</span><span class="document-value">{{ $referral->referred_at?->format('M d, Y · h:i A') }}</span></td>
    </tr></table>

    <h3 class="document-section"><span>01</span> PATIENT INFORMATION</h3>
    <table role="presentation"><tr>
        <td class="document-field" colspan="2"><span class="document-label">FULL NAME</span><span class="document-value">{{ $referral->name }}</span></td>
        <td class="document-field"><span class="document-label">PATIENT ID</span><span class="document-value">{{ $referral->citizen_id }}</span></td>
    </tr><tr>
        <td class="document-field"><span class="document-label">AGE</span><span class="document-value">{{ $referral->age }}</span></td>
        <td class="document-field"><span class="document-label">SEX</span><span class="document-value">{{ $referral->gender }}</span></td>
        <td class="document-field"><span class="document-label">DATE OF BIRTH</span><span class="document-value">{{ $referral->birth_date?->format('M d, Y') }}</span></td>
    </tr><tr>
        <td class="document-field" colspan="2"><span class="document-label">ADDRESS / PUROK</span><span class="document-value">{{ $referral->address }}</span></td>
        <td class="document-field"><span class="document-label">CONTACT NUMBER</span><span class="document-value">{{ $referral->contact_number }}</span></td>
    </tr></table>

    <h3 class="document-section"><span>02</span> REFERRAL INFORMATION</h3>
    <table role="presentation"><tr>
        <td class="document-field"><span class="document-label">RECEIVING FACILITY</span></td>
        <td class="document-field"><span class="document-label">RECEIVING PHYSICIAN (IF KNOWN)</span></td>
    </tr></table>
    <table class="document-priority" role="presentation"><tr>
        <td>REFERRAL PRIORITY</td>
        <td><span class="document-checkbox"></span>Routine</td>
        <td><span class="document-checkbox"></span>Urgent</td>
        <td><span class="document-checkbox"></span>Emergency</td>
    </tr></table>
    <table role="presentation"><tr>
        <td class="document-field document-writing"><span class="document-label">REASON FOR REFERRAL</span><span class="document-value">{{ $referral->requests_for }}</span></td>
    </tr></table>

    <h3 class="document-section"><span>03</span> CLINICAL INFORMATION</h3>
    <table role="presentation"><tr>
        <td class="document-field document-writing"><span class="document-label">CHIEF COMPLAINT</span></td>
        <td class="document-field document-writing"><span class="document-label">INITIAL ASSESSMENT</span></td>
    </tr></table>
    <table class="document-vitals" role="presentation"><tr>
        @foreach(['BP (mmHg)', 'TEMP (°C)', 'PULSE (bpm)', 'RESP. RATE (/min)', 'SpO2 (%)', 'WEIGHT (kg)'] as $vital)
            <td class="document-field"><span class="document-label">{{ $vital }}</span></td>
        @endforeach
    </tr></table>
    <table role="presentation"><tr>
        <td class="document-field document-writing"><span class="document-label">INITIAL TREATMENT / INTERVENTION</span><span class="document-value">{{ $referral->treatment_given }}</span></td>
        <td class="document-field document-writing"><span class="document-label">ADDITIONAL REMARKS</span></td>
    </tr></table>

    <h3 class="document-section"><span>04</span> REFERRING PERSONNEL</h3>
    <table role="presentation"><tr>
        <td class="document-field"><span class="document-label">REFERRED BY</span><span class="document-value">{{ $referral->referred_by }}</span></td>
        <td class="document-field"><span class="document-label">DESIGNATION</span></td>
        <td class="document-field"><span class="document-label">SIGNATURE</span></td>
    </tr></table>

    <h3 class="document-section"><span>05</span> RECEIVING HEALTHCARE FACILITY</h3>
    <table role="presentation"><tr>
        <td class="document-field"><span class="document-label">RECEIVED BY</span></td>
        <td class="document-field"><span class="document-label">DATE AND TIME RECEIVED</span></td>
        <td class="document-field"><span class="document-label">SIGNATURE</span></td>
    </tr></table>
    <table role="presentation"><tr>
        <td class="document-field"><span class="document-label">FINDINGS AND RECOMMENDATIONS</span></td>
    </tr></table>
    <footer class="document-footer">CONFIDENTIAL &nbsp;|&nbsp; For authorized healthcare personnel only.</footer>
</article>
