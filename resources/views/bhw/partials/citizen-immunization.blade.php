<div class="info-card immunization-card">
    <div class="card">
        <section class="immunization-section" aria-labelledby="immunizationTitle">
            <div class="immunization-heading">
                <div>
                    <h3 id="immunizationTitle">Immunization / Vaccination</h3>
                    <p>Select a vaccine, record the dose and save it to this citizen's history.</p>
                </div>
                <span class="immunization-count" id="immunizationCount" aria-live="polite">Record one vaccination at a time</span>
            </div>

            @if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
                <div class="immunization-form">
                    @php
                        $vaccines = [
                            'BCG' => 'BCG',
                            'HepB' => 'Hepatitis B (HepB)',
                            'Penta' => 'Pentavalent (Penta)',
                            'OPV' => 'Oral Polio Vaccine (OPV)',
                            'IPV' => 'Inactivated Polio Vaccine (IPV)',
                            'PCV' => 'Pneumococcal Conjugate Vaccine (PCV)',
                            'MMR' => 'Measles, Mumps, Rubella (MMR)',
                            'MR' => 'Measles-Rubella (MR)',
                            'Td' => 'Tetanus-Diphtheria (Td)',
                            'HPV' => 'Human Papillomavirus (HPV)',
                        ];
                    @endphp
                    <div class="immunization-grid" id="immunizationGrid">
                        @foreach($vaccines as $vaccineKey => $vaccineLabel)
                            <label class="vaccine-option" for="vaccine_{{ $vaccineKey }}">
                                <input type="radio" id="vaccine_{{ $vaccineKey }}" name="vaccine_name" value="{{ $vaccineLabel }}" class="vaccine-checkbox" form="vaccinationForm" required>
                                <span class="vaccine-checkmark" aria-hidden="true"></span>
                                <span class="vaccine-label">{{ $vaccineLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="immunization-fields">
                        <label>Dose
                            <input type="text" name="dose_number" placeholder="e.g. Dose 1 or Booster" form="vaccinationForm" required>
                        </label>
                        <label>Date administered
                            <input type="date" name="date_administered" value="{{ now()->format('Y-m-d') }}" form="vaccinationForm" required>
                        </label>
                        <label>Administered by
                            <input type="text" name="administered_by" value="{{ auth()->user()->name }}" form="vaccinationForm" required>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary" form="vaccinationForm">Save Vaccination</button>
                </div>
            @elseif((int) $citizen->Citizen_IsDeceased === 1)
                <p class="immunization-preview-note">This record is deceased and locked for updates.</p>
            @else
                <p class="immunization-preview-note">Read-only: this citizen is not assigned to your subgroup.</p>
            @endif

            <div class="immunization-history">
                <h4>Immunization History</h4>
                @forelse($citizen->vaccinations as $vaccination)
                    <div class="immunization-history-item">
                        <strong>{{ $vaccination->vaccine_name }}</strong>
                        <span>{{ $vaccination->dose_number }} · {{ $vaccination->date_administered }}</span>
                        <small>Administered by {{ $vaccination->administered_by }}</small>
                    </div>
                @empty
                    <p>No immunization records yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
