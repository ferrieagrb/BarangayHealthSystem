@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/citizen.css') }}">

<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<<<<<<< HEAD
=======
    <style>
        .citizen-password-modal { display: none; position: fixed; inset: 0; z-index: 10000; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, .55); }
        .citizen-password-modal.is-open { display: flex; }
        .citizen-password-dialog { width: min(100%, 420px); padding: 24px; border-radius: 12px; background: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, .24); }
        .citizen-password-dialog h2 { margin: 0 0 8px; color: #173b82; font-size: 20px; }
        .citizen-password-dialog p { margin: 0 0 16px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .citizen-password-dialog label { display: block; margin: 12px 0 6px; color: #334155; font-size: 12px; font-weight: 700; }
        .citizen-password-dialog input { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .citizen-password-dialog-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
        .citizen-export-modal { display: none; position: fixed; inset: 0; z-index: 10000; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, .55); }
        .citizen-export-modal.is-open { display: flex; }
        .citizen-export-dialog { width: min(100%, 420px); padding: 24px; border-radius: 12px; background: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, .24); }
        .citizen-export-dialog h2 { margin: 0 0 8px; color: #173b82; font-size: 20px; }
        .citizen-export-dialog p { margin: 0 0 18px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .citizen-export-options { display: grid; gap: 10px; }
        .citizen-export-option { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 13px 14px; border: 1px solid #dbe3ed; border-radius: 8px; background: #f8fafc; color: #172033; text-align: left; cursor: pointer; text-decoration: none; box-sizing: border-box; }
        .citizen-export-option:hover { border-color: #2563eb; background: #eff6ff; }
        .citizen-export-option strong { display: block; color: #173b82; }
        .citizen-export-option small { display: block; margin-top: 3px; color: #64748b; }
        .citizen-export-close { display: flex; justify-content: flex-end; margin-top: 18px; }
        .citizen-export-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 16px; max-height: 300px; overflow-y: auto; padding: 4px 2px; }
        .citizen-export-field { display: flex; align-items: center; gap: 8px; color: #334155; font-size: 13px; }
        .citizen-export-field input { width: 16px; height: 16px; }
        .citizen-export-format { display: flex; justify-content: center; gap: 0; margin-top: 20px; }
        .citizen-export-format button { padding: 10px 18px; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; cursor: pointer; }
        .citizen-export-format button:first-child { border-radius: 7px 0 0 7px; }
        .citizen-export-format button:last-child { border-radius: 0 7px 7px 0; }
        .citizen-export-format button.active { border-color: #1d4ed8; background: #1d4ed8; color: #fff; }
        .citizen-export-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
    </style>
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
@endsection

@section('content')

{{-- SUCCESS POPUP --}}
@if(session('success'))
<div id="citizen-success-popup"
     style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:99999;padding:20px;">
    <div style="background:#fff;width:100%;max-width:400px;padding:30px;border-radius:14px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,.2);">
        <div style="width:58px;height:58px;margin:0 auto 15px;border-radius:50%;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;">✓</div>
        <h2 style="color:#166534;font-size:22px;font-weight:bold;margin-bottom:10px;">Success!</h2>
        <p style="color:#4b5563;margin-bottom:22px;">{{ session('success') }}</p>
        @if(session('import_issues'))
            <div style="text-align:left;max-height:180px;overflow:auto;margin:-8px 0 20px;padding:10px 12px;border-radius:8px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:12px;line-height:1.5;">
                <strong>Some data was not accepted because of wrong format:</strong>
                <ul style="margin:6px 0 0;padding-left:18px;">
                    @foreach(session('import_issues') as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <button type="button" onclick="closeCitizenSuccessPopup()"
                style="background:#1646bd;color:#fff;border:none;border-radius:7px;padding:10px 30px;cursor:pointer;font-weight:bold;">OK</button>
    </div>
</div>
@endif

{{-- PAGE HEADER --}}
<div class="page-top">
    <div class="page-title-group">
        <h1>Citizen Management</h1>
        <p>Manage resident profiles, monitor status, and update citizen health-related information.</p>
    </div>

    <div class="page-header-actions" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('citizen.add') }}" class="btn-primary">+ Add Citizen</a>
        <button type="button" class="btn-secondary"
                onclick="document.getElementById('excelFileInput').click();">
            Import List
        </button>
    </div>
</div>

{{-- CITIZEN OVERVIEW --}}
<div class="citizen-overview">
    <div class="summary-cards">
        <div class="summary-card">
            <span>Total Citizens</span>
            <strong>{{ $totalCitizens }}</strong>
            <div class="summary-card-trend trend-{{ $citizenTrends['total']['state'] }}">
                <span>{{ $citizenTrends['total']['icon'] }} {{ $citizenTrends['total']['value'] }}</span>
                <small>vs previous 14 days</small>
            </div>
        </div>

        <div class="summary-card">
            <span>Kids</span>
            <strong>{{ $kids }}</strong>
            <div class="summary-card-trend trend-{{ $citizenTrends['kids']['state'] }}">
                <span>{{ $citizenTrends['kids']['icon'] }} {{ $citizenTrends['kids']['value'] }}</span>
                <small>vs previous 14 days</small>
            </div>
        </div>

        <div class="summary-card">
            <span>Adults</span>
            <strong>{{ $adults }}</strong>
            <div class="summary-card-trend trend-{{ $citizenTrends['adults']['state'] }}">
                <span>{{ $citizenTrends['adults']['icon'] }} {{ $citizenTrends['adults']['value'] }}</span>
                <small>vs previous 14 days</small>
            </div>
        </div>

        <div class="summary-card">
            <span>Seniors</span>
            <strong>{{ $seniors }}</strong>
            <div class="summary-card-trend trend-{{ $citizenTrends['seniors']['state'] }}">
                <span>{{ $citizenTrends['seniors']['icon'] }} {{ $citizenTrends['seniors']['value'] }}</span>
                <small>vs previous 14 days</small>
            </div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3>Demographics Breakdown</h3>
        </div>
        <div id="demographicsApexChart"></div>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="toolbar">
    <div class="toolbar-left">
        <form method="GET" action="{{ route('citizenlist') }}"
              class="citizen-filter-form" id="citizenFilterForm">

            {{-- SEARCH --}}
            <div class="search-box">
                <input type="search" name="search" id="citizenSearchInput"
                       value="{{ request('search') }}"
                       placeholder="Search citizen name or ID"
                       aria-label="Search citizens by name or ID"
                       autocomplete="off">
            </div>

            {{-- GROUPED PUROK AND SUBGROUP FILTER --}}
            <div class="filter-box purok-filter-box">
                <select id="purokFilter" aria-label="Filter by Purok or Subgroup">
                    <option value="">All Puroks</option>

                    @foreach($puroks ?? [] as $purok)
                        <optgroup label="{{ $purok->name }}">
                            <option value="purok:{{ $purok->id }}"
                                {{ (string) request('purok') === (string) $purok->id && !request()->filled('subgroup') ? 'selected' : '' }}>
                                All of {{ $purok->name }}
                            </option>

                            @foreach($purok->subgroups ?? [] as $subgroup)
                                <option value="subgroup:{{ $subgroup->id }}"
                                    {{ (string) request('subgroup') === (string) $subgroup->id ? 'selected' : '' }}>
                                    └ {{ $subgroup->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <input type="hidden" name="purok" id="purokFilterValue"
                       value="{{ request('purok') }}">
                <input type="hidden" name="subgroup" id="subgroupFilterValue"
                       value="{{ request('subgroup') }}">
            </div>

            {{-- AGE FILTER --}}
            <div class="filter-box">
                <input type="text" name="age"
                       value="{{ request('age') }}"
                       placeholder="Age or range (e.g. 8 or 4-10)">
            </div>

            {{-- STATUS FILTER --}}
            <div class="filter-box">
                <button type="button" id="openCitizenStatusFilter"
                        class="citizen-status-filter-btn">
                    <span>Status & Filters</span>
                    <span class="status-filter-arrow" aria-hidden="true">⌄</span>
                </button>
            </div>
        </form>
    </div>

    <div class="toolbar-actions">
        {{-- <button type="button" id="openCitizenExportModal" class="btn-secondary">Export List</button> --}}

        <form action="{{ route('citizen.import') }}" method="POST"
              enctype="multipart/form-data" id="importForm" style="display:none;">
            @csrf
            <input type="file" name="file" id="excelFileInput"
                   accept=".csv,.txt,.xlsx,.xls"
                   onchange="document.getElementById('importForm').requestSubmit();">
        </form>
    </div>
</div>

{{-- EXPORT MODAL --}}
<div id="citizenExportModal" class="citizen-export-modal" aria-hidden="true">
    <div class="citizen-export-dialog" role="dialog" aria-modal="true" aria-labelledby="citizenExportTitle">
        <div class="citizen-export-header">
            <div>
                <h2 id="citizenExportTitle">Export Citizen Records</h2>
                <p>Choose the exact details to include in your export.</p>
            </div>
            <button type="button" id="closeCitizenExportHeader" class="citizen-export-close" aria-label="Close export options">&times;</button>
        </div>

        <form id="citizenExportForm" data-no-loader="true">
            @php
                $defaultExportFields = [
                    'Citizen_FName',
                    'Citizen_MName',
                    'Citizen_LName',
                    'Citizen_BirthDate',
                    'Citizen_ContactNo',
                    'Citizen_Purok',
                    'Citizen_Sex',
                    'Citizen_Age',
                    'Citizen_HousingStatus',
                ];
                $exportFieldGroups = [
                    'Personal Details' => ['id', 'Citizen_FName', 'Citizen_MName', 'Citizen_LName', 'Citizen_BirthDate', 'Citizen_Age', 'Citizen_Sex'],
                    'Contact & Location' => ['Citizen_ContactNo', 'Citizen_Purok', 'family_id', 'subgroup_id'],
                    'Health & Status' => ['Citizen_Height', 'Citizen_Weight', 'Citizen_HousingStatus', 'Citizen_Pregnant', 'Citizen_InPills', 'Citizen_IsDeceased'],
                    'Immunization' => ['immunization_bcg', 'immunization_hepb', 'immunization_penta', 'immunization_opv', 'immunization_ipv', 'immunization_pcv', 'immunization_mmr', 'immunization_mr', 'immunization_td', 'immunization_hpv'],
                ];
            @endphp

            <div class="citizen-export-body">
                <div class="citizen-export-selection-bar">
                    <span><strong id="citizenExportSelectedCount">0</strong> fields selected</span>
                    <div>
                        <button type="button" id="selectAllCitizenExportFields">Select all</button>
                        <button type="button" id="clearCitizenExportFields">Clear</button>
                    </div>
                </div>

                @foreach($exportFieldGroups as $groupName => $groupFields)
                    <section class="citizen-export-section">
                        <h3>{{ $groupName }}</h3>
                        <div class="citizen-export-fields">
                            @foreach($groupFields as $field)
                                @if(isset($exportFieldDefinitions[$field]))
                                    <label class="citizen-export-field">
                                        <input type="checkbox" name="fields[]" value="{{ $field }}"
                                            {{ in_array($field, $defaultExportFields, true) ? 'checked' : '' }}>
                                        <span>{{ $exportFieldDefinitions[$field] }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="citizen-export-footer">
                <div class="citizen-export-format">
                    <span>File format</span>
                    <div role="group" aria-label="Export format">
                        <button type="button" class="active" aria-pressed="true" data-export-format="xlsx">Excel (.xlsx)</button>
                        <button type="button" aria-pressed="false" data-export-format="pdf">PDF (.pdf)</button>
                    </div>
                </div>

                <input type="hidden" name="format" id="citizenExportFormat" value="xlsx">

                <div class="citizen-export-actions">
                    <button type="button" id="closeCitizenExportModal" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" id="downloadCitizenExport">Download</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- STATUS FILTER MODAL --}}
<div id="citizenStatusFilterModal" class="citizen-status-overlay" aria-hidden="true">
    <div class="citizen-status-modal" role="dialog" aria-modal="true"
         aria-labelledby="citizenStatusFilterTitle">

        <div class="citizen-status-header">
            <h2 id="citizenStatusFilterTitle">Filter Citizen Records</h2>
            <button type="button" id="closeCitizenStatusFilter"
                    class="citizen-status-close" aria-label="Close filter">&times;</button>
        </div>

        <div class="citizen-status-body">
            <section class="citizen-status-section">
                <h3>Sorting / Order</h3>
                <div class="citizen-status-radio-group">
                    <label><input type="radio" name="sort" value="Alphabetical" checked><span>Alphabetical</span></label>
                    <label><input type="radio" name="sort" value="Youngest to Oldest"><span>Youngest to Oldest</span></label>
                    <label><input type="radio" name="sort" value="Oldest to Youngest"><span>Oldest to Youngest</span></label>
                </div>
            </section>

            <section class="citizen-status-section">
                <h3>Sex</h3>
                <div class="citizen-status-radio-group">
                    <label><input type="radio" name="filter_sex" value="all" checked><span>All</span></label>
                    <label><input type="radio" name="filter_sex" value="Male"><span>Male</span></label>
                    <label><input type="radio" name="filter_sex" value="Female"><span>Female</span></label>
                </div>
            </section>

            <section class="citizen-status-section">
                <h3>Housing Status</h3>
                <label class="citizen-status-option"><input type="checkbox" name="filter_housing[]" value="Permanent Resident"><span>Permanent Resident</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="filter_housing[]" value="Renter"><span>Renter</span></label>
            </section>

            <section class="citizen-status-section">
                <h3>Health &amp; Family Planning</h3>
                <label class="citizen-status-option"><input type="checkbox" name="filter_health[]" value="pregnant"><span>Pregnant</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="filter_health[]" value="in_pills"><span>In-Pills</span></label>
            </section>

            <section class="citizen-status-section">
                <h3>Immunization Vaccines</h3>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="bcg"><span>BCG</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="hepb"><span>Hepatitis B (HepB)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="penta"><span>Pentavalent (Penta)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="opv"><span>Oral Polio Vaccine (OPV)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="ipv"><span>Inactivated Polio Vaccine (IPV)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="pcv"><span>Pneumococcal Conjugate Vaccine (PCV)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="mmr"><span>Measles, Mumps, Rubella (MMR)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="mr"><span>Measles-Rubella (MR)</span></label>
                <label class="citizen-status-option"> <input type="checkbox" name="health_filter_vaccine" value="td"><span>Tetanus-Diphtheria (Td)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="hpv"><span>Human Papillomavirus (HPV)</span></label>
            </section>

            <section class="citizen-status-section">
                <h3>Vital Status</h3>
                <label class="citizen-status-option">
                    <input type="checkbox" id="filterIncludeDeceased" name="filter_deceased" value="1">
                    <span>Show Deceased Residents Only</span>
                </label>
            </section>
        </div>

        <div class="citizen-status-footer">
            <button type="button" id="clearCitizenStatusFilters" class="citizen-status-clear">Clear All</button>
            <button type="button" id="applyCitizenStatusFilters" class="citizen-status-apply">Apply Filters</button>
        </div>
    </div>
</div>

{{-- CITIZEN TABLE --}}
<div class="table-container">
    <div class="card-header" style="margin-bottom:20px;">
        <h2>Citizen Records</h2>
        <p>Showing registered barangay residents and their current profile status.</p>
    </div>

    <div id="citizen-results">
        @php
            $activeFilters = [];

            if (request()->filled('search')) {
                $activeFilters[] = 'Search: ' . request('search');
            }

            if (request()->filled('purok')) {
                $purokObj = \App\Models\Purok::find(request('purok'));
                $activeFilters[] = 'Purok: ' . ($purokObj?->name ?? request('purok'));
            }

            if (request()->filled('subgroup')) {
                $subgroupObj = \App\Models\PurokSubgroup::find(request('subgroup'));
                $activeFilters[] = 'Subgroup: ' . ($subgroupObj?->name ?? request('subgroup'));
            }

            if (request()->filled('age')) {
                $activeFilters[] = 'Age: ' . request('age');
            }

            if (request()->filled('sort') && request('sort') !== 'Alphabetical') {
                $activeFilters[] = 'Sort: ' . request('sort');
            }

            if (request()->filled('filter_sex') && request('filter_sex') !== 'all') {
                $activeFilters[] = 'Sex: ' . request('filter_sex');
            }

            if (request()->has('filter_housing')) {
                $activeFilters[] = 'Housing: ' . implode(', ', (array) request('filter_housing'));
            }

            if (request()->has('filter_health')) {
                $activeFilters[] = 'Health: ' . implode(', ', (array) request('filter_health'));
            }

            if (request()->has('health_filter_vaccine')) {
                $activeFilters[] = 'Vaccines: ' . implode(', ', (array) request('health_filter_vaccine'));
            }

            if (request()->boolean('filter_deceased')) {
                $activeFilters[] = 'Deceased Residents Only';
            }
        @endphp

        <div id="citizen-results-summary" class="citizen-results-highlight"
             style="display:inline-flex;align-items:center;flex-wrap:wrap;gap:6px;margin:0 0 12px;padding:8px 12px;border:1px solid #bbf7d0;border-radius:8px;background:#f0fdf4;color:#166534;font-size:13px;font-weight:500;line-height:1.5;">
            <strong style="font-weight:700;">Showing {{ $citizens->total() }} results</strong>

            @if(count($activeFilters) > 0)
                <span aria-hidden="true" style="color:#86a98e;font-weight:700;">·</span>
                <span id="active-citizen-filters">{{ implode(', ', $activeFilters) }}</span>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name / ID</th>
                    <th>Age</th>
                    <th>Birth Date</th>
                    <th>Contact</th>
                    <th>Purok</th>
                    <th>Subgroup</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($citizens as $citizen)
                    <tr>
                        <td>
                            <span class="record-name">{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}</span>
                            <span class="record-sub">ID: {{ $citizenDisplayIds[$citizen->id] ?? 'Unassigned' }}</span>
                        </td>

                        <td>{{ $citizen->Citizen_Age }}</td>
                        <td>{{ $citizen->Citizen_BirthDate }}</td>
                        <td>{{ $citizen->Citizen_ContactNo }}</td>
                        <td>{{ $citizen->Citizen_Purok }}</td>
                        <td>{{ $citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Unassigned' }}</td>

                        <td>
                            @php
                                $user = auth()->user();

                                $isAssigned = $user->role !== 'bhw' ||
                                    \App\Models\PurokSubgroup::where('user_id', $user->id)
                                        ->where(function ($query) use ($citizen) {
                                            $query->where('id', $citizen->subgroup_id)
                                                ->orWhere('id', optional($citizen->family)->subgroup_id);
                                        })->exists();
                            @endphp

                            <div class="action-group">
                                <a href="{{ route('citizendetails', $citizen->id) }}" class="btn-primary">View</a>

                                @if($isAssigned)
                                    <a href="{{ route('bhw.citizen.ecard', $citizen->id) }}" class="btn-primary">E-Card</a>

                                    @if(auth()->user()->hasWriteAccess('citizen_password_reset'))
                                        <button type="button" class="btn-secondary resetCitizenPasswordBtn"
                                                data-citizen-id="{{ $citizen->id }}"
                                                data-citizen-name="{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}">
                                            Reset Password
                                        </button>
                                    @endif

                                    <form action="{{ route('citizen.delete', $citizen->id) }}"
                                          method="POST" class="deleteCitizenForm" data-no-loader="true"
                                          data-citizen-name="{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Archive</button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size:11px;">View Only (Unassigned)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-text" style="text-align:center;padding:20px;">
                            No citizen records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:15px;">
            {{ $citizens->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- ARCHIVE CONFIRMATION MODAL --}}
<div id="citizenDeleteConfirmModal" class="citizen-delete-confirm-overlay">
    <div class="citizen-delete-confirm-modal">
        <h2>Archive Citizen?</h2>
        <p>
            Are you sure you want to archive <strong id="confirmCitizenName"></strong>?
            <br>You can restore them later from the archive.
        </p>
        <div class="citizen-delete-confirm-buttons">
            <button type="button" id="cancelCitizenDelete" class="citizen-delete-cancel">Cancel</button>
            <button type="button" id="confirmCitizenDelete" class="citizen-delete-save">Yes, Archive</button>
        </div>
    </div>
</div>

{{-- RESET PASSWORD MODAL --}}
<div id="citizenPasswordResetModal" class="citizen-password-modal"
     role="dialog" aria-modal="true" aria-labelledby="citizenPasswordResetTitle">
    <div class="citizen-password-dialog">
        <h2 id="citizenPasswordResetTitle">Reset Citizen Password</h2>
        <p>Set a temporary password for <strong id="resetCitizenName"></strong>. The citizen will be required to change it at the next login.</p>

        <form method="POST" id="citizenPasswordResetForm">
            @csrf
            <label for="resetCitizenPassword">Temporary password</label>
            <input type="password" id="resetCitizenPassword" name="password" minlength="8" required autocomplete="new-password">

            <label for="resetCitizenPasswordConfirmation">Confirm temporary password</label>
            <input type="password" id="resetCitizenPasswordConfirmation" name="password_confirmation" minlength="8" required autocomplete="new-password">

            <div class="citizen-password-dialog-actions">
                <button type="button" class="btn-secondary" id="cancelCitizenPasswordReset">Cancel</button>
                <button type="submit" class="btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- SUCCESS POPUP --}}
<script>
function closeCitizenSuccessPopup() {
    document.getElementById('citizen-success-popup')?.remove();
}
</script>

{{-- EXPORT MODAL --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenExportModal');
    const openButton = document.getElementById('openCitizenExportModal');
    const closeButton = document.getElementById('closeCitizenExportModal');
    const headerCloseButton = document.getElementById('closeCitizenExportHeader');
    const form = document.getElementById('citizenExportForm');
    const formatInput = document.getElementById('citizenExportFormat');
    const selectedCount = document.getElementById('citizenExportSelectedCount');
    const downloadButton = document.getElementById('downloadCitizenExport');
    const selectAllButton = document.getElementById('selectAllCitizenExportFields');
    const clearButton = document.getElementById('clearCitizenExportFields');

    if (!modal || !openButton || !closeButton || !headerCloseButton || !form || !formatInput) return;

    const formatButtons = modal.querySelectorAll('[data-export-format]');
    const fieldInputs = Array.from(form.querySelectorAll('input[name="fields[]"]'));

    function updateSelection() {
        const count = fieldInputs.filter(input => input.checked).length;
        fieldInputs.forEach(input => {
            input.closest('.citizen-export-field').classList.toggle('selected', input.checked);
        });
        selectedCount.textContent = count;
        downloadButton.disabled = count === 0;
        downloadButton.setAttribute('aria-disabled', String(count === 0));
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        openButton.focus();
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        headerCloseButton.focus();
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    headerCloseButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    fieldInputs.forEach(input => input.addEventListener('change', updateSelection));
    selectAllButton.addEventListener('click', function () {
        fieldInputs.forEach(input => { input.checked = true; });
        updateSelection();
    });
    clearButton.addEventListener('click', function () {
        fieldInputs.forEach(input => { input.checked = false; });
        updateSelection();
    });

    formatButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            formatInput.value = button.dataset.exportFormat;
            formatButtons.forEach(item => {
                const isActive = item === button;
                item.classList.toggle('active', isActive);
                item.setAttribute('aria-pressed', String(isActive));
            });
        });
    });

    updateSelection();

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const params = new URLSearchParams(window.location.search);
        const formData = new FormData(form);
        const fields = formData.getAll('fields[]');

        if (!fields.length) {
            alert('Select at least one citizen detail to export.');
            return;
        }

        params.delete('fields[]');
        fields.forEach(field => params.append('fields[]', field));

        const exportPath = formatInput.value === 'pdf'
            ? @json(route('citizen.export.pdf'))
            : @json(route('citizen.export'));

        window.location.href = exportPath + '?' + params.toString();
        closeModal();
    });
});
</script>

{{-- RESET PASSWORD --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenPasswordResetModal');
    const form = document.getElementById('citizenPasswordResetForm');
    const nameElement = document.getElementById('resetCitizenName');
    const passwordInput = document.getElementById('resetCitizenPassword');
    const cancelButton = document.getElementById('cancelCitizenPasswordReset');

    if (!modal || !form || !nameElement || !passwordInput || !cancelButton) return;

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.resetCitizenPasswordBtn');
        if (!button) return;

            form.action = @json(url('/citizen')) + '/' + button.dataset.citizenId + '/password-reset';
            nameElement.textContent = button.dataset.citizenName;
            form.reset();
            modal.classList.add('is-open');
            passwordInput.focus();
    });

    function closeModal() {
        modal.classList.remove('is-open');
        form.reset();
    }

    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
});
</script>

{{-- DELETE CONFIRMATION --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenDeleteConfirmModal');
    const nameElement = document.getElementById('confirmCitizenName');
    const cancelButton = document.getElementById('cancelCitizenDelete');
    const confirmButton = document.getElementById('confirmCitizenDelete');

    if (!modal || !nameElement || !cancelButton || !confirmButton) return;

    let selectedForm = null;

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.deleteCitizenForm');
        if (!form) return;

        event.preventDefault();
        selectedForm = form;
        nameElement.textContent = form.dataset.citizenName || 'this citizen';
        modal.classList.add('active');
    });

    cancelButton.addEventListener('click', function () {
        selectedForm = null;
        modal.classList.remove('active');
    });

    confirmButton.addEventListener('click', function () {
        if (!selectedForm) return;

        const formToSubmit = selectedForm;
        selectedForm = null;
        modal.classList.remove('active');
        if (typeof window.showLoader === 'function') window.showLoader();
        formToSubmit.submit();
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            selectedForm = null;
            modal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            selectedForm = null;
            modal.classList.remove('active');
        }
    });
});
</script>

{{-- DEMOGRAPHICS CHART --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartElement = document.querySelector('#demographicsApexChart');
    if (!chartElement || typeof ApexCharts === 'undefined') return;

    const options = {
        series: [{
            name: 'Count',
            data: [
                {{ $kids }},
                {{ $adults }},
                {{ $seniors }}
            ]
        }],
        chart: {
            type: 'bar',
            height: '100%',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                distributed: true,
                columnWidth: '45%'
            }
        },
        colors: ['#3b82f6', '#14b8a6', '#f97316'],
        dataLabels: {
            enabled: true,
            formatter: value => Math.floor(value),
            style: {
                fontSize: '15px',
                fontWeight: 600,
                colors: ['#0f172a']
            },
            offsetY: -4
        },
        legend: { show: false },
        xaxis: {
            categories: ['Kids', 'Adults', 'Seniors'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    fontSize: '13px',
                    colors: ['#334155', '#334155', '#334155']
                }
            }
        },
        yaxis: {
            tickAmount: 3,
            labels: {
                style: { fontSize: '12px', colors: ['#475569'] },
                formatter: value => Math.floor(value)
            }
        },
        grid: {
            strokeDashArray: 4,
            xaxis: { lines: { show: false } }
        }
    };

    new ApexCharts(chartElement, options).render();
});
</script>

{{-- GROUPED PUROK / SUBGROUP FILTER --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const purokFilter = document.getElementById('purokFilter');
    const purokValue = document.getElementById('purokFilterValue');
    const subgroupValue = document.getElementById('subgroupFilterValue');

    if (!purokFilter || !purokValue || !subgroupValue) return;

    purokFilter.addEventListener('change', function () {
        const value = this.value;

        purokValue.value = '';
        subgroupValue.value = '';

        if (value.startsWith('purok:')) {
            purokValue.value = value.slice('purok:'.length);
        } else if (value.startsWith('subgroup:')) {
            subgroupValue.value = value.slice('subgroup:'.length);
        }

        const url = new URL(window.location.href);

        url.searchParams.delete('purok');
        url.searchParams.delete('subgroup');
        url.searchParams.delete('page');

        if (purokValue.value) {
            url.searchParams.set('purok', purokValue.value);
        }

        if (subgroupValue.value) {
            url.searchParams.set('subgroup', subgroupValue.value);
        }

        window.location.href = url.toString();
    });
});
</script>

{{-- STATUS FILTER MODAL --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenStatusFilterModal');
    const openButton = document.getElementById('openCitizenStatusFilter');
    const closeButton = document.getElementById('closeCitizenStatusFilter');
    const clearButton = document.getElementById('clearCitizenStatusFilters');
    const applyButton = document.getElementById('applyCitizenStatusFilters');

    if (!modal || !openButton || !closeButton || !clearButton || !applyButton) return;

    function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        openButton.classList.add('active');
        document.body.style.overflow = 'hidden';
        closeButton.focus();
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        openButton.classList.remove('active');
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    const params = new URLSearchParams(window.location.search);
    const savedSort = params.get('sort') || 'Alphabetical';
    const sortInput = Array.from(modal.querySelectorAll('input[name="sort"]'))
        .find(input => input.value === savedSort);
    if (sortInput) sortInput.checked = true;

    const savedSex = params.get('filter_sex') || 'all';
    const sexInput = Array.from(modal.querySelectorAll('input[name="filter_sex"]'))
        .find(input => input.value === savedSex);
    if (sexInput) sexInput.checked = true;

    [
        ['filter_housing', 'filter_housing[]'],
        ['filter_health', 'filter_health[]'],
        ['health_filter_vaccine', 'health_filter_vaccine']
    ].forEach(function ([key, inputName]) {
        const values = [...params.getAll(`${key}[]`), ...params.getAll(key)];
        values.forEach(function (value) {
            const input = Array.from(modal.querySelectorAll(`input[name="${inputName}"]`))
                .find(item => item.value === value);
            if (input) input.checked = true;
        });
    });

    const deceased = document.getElementById('filterIncludeDeceased');
    if (deceased) deceased.checked = params.get('filter_deceased') === '1';

    clearButton.addEventListener('click', function () {
        const defaultSort = modal.querySelector('input[name="sort"][value="Alphabetical"]');
        if (defaultSort) defaultSort.checked = true;

        const allSex = modal.querySelector('input[name="filter_sex"][value="all"]');
        if (allSex) allSex.checked = true;

        modal.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
            input.checked = false;
        });
    });

    applyButton.addEventListener('click', function () {
        const url = new URL(window.location.href);
        const query = url.searchParams;

        [
            'sort',
            'filter_sex',
            'filter_housing',
            'filter_housing[]',
            'filter_health',
            'filter_health[]',
            'health_filter_vaccine',
            'health_filter_vaccine[]',
            'filter_deceased',
            'page'
        ].forEach(key => query.delete(key));

        const selectedSort = modal.querySelector('input[name="sort"]:checked');
        if (selectedSort) {
            query.set('sort', selectedSort.value);
        }

        const selectedSex = modal.querySelector('input[name="filter_sex"]:checked');
        if (selectedSex && selectedSex.value !== 'all') {
            query.set('filter_sex', selectedSex.value);
        }

        modal.querySelectorAll('input[name="filter_housing[]"]:checked').forEach(function (input) {
            query.append('filter_housing[]', input.value);
        });

        modal.querySelectorAll('input[name="filter_health[]"]:checked').forEach(function (input) {
            query.append('filter_health[]', input.value);
        });

        modal.querySelectorAll('input[name="health_filter_vaccine"]:checked').forEach(function (input) {
            query.append('health_filter_vaccine[]', input.value);
        });

        if (deceased && deceased.checked) {
            query.set('filter_deceased', '1');
        }

        window.location.href = url.toString();
    });
});
</script>

{{-- LIVE SEARCH & AGE FILTER --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.citizen-filter-form');
    const searchInput = searchForm?.querySelector('input[name="search"]');
    const ageInput = searchForm?.querySelector('input[name="age"]');

    if (!searchForm) return;

    let searchTimer = null;
    let requestController = null;

    function triggerLiveSearch(delay = 350) {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(async function () {
            if (requestController) requestController.abort();
            requestController = new AbortController();

            const params = new URLSearchParams(window.location.search);
            ['search', 'purok', 'subgroup', 'age', 'page'].forEach(key => params.delete(key));

            const formData = new FormData(searchForm);
            ['search', 'purok', 'subgroup', 'age'].forEach(function (key) {
                const value = formData.get(key);
                if (value !== null && String(value).trim() !== '') {
                    params.set(key, String(value).trim());
                }
            });

            const results = document.getElementById('citizen-results');
            if (!results) return;

            results.setAttribute('aria-busy', 'true');

            try {
                const requestUrl = new URL(searchForm.action, window.location.origin);
                params.forEach((value, key) => requestUrl.searchParams.append(key, value));

                const response = await fetch(requestUrl.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) {
                    throw new Error(`Search request failed (${response.status})`);
                }

                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const updatedResults = parsed.getElementById('citizen-results');

                if (!updatedResults) {
                    throw new Error('Search response did not contain citizen results.');
                }

                results.replaceWith(updatedResults);
                window.history.replaceState({}, '', requestUrl.toString());
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Citizen search failed:', error);
                }
            } finally {
                document.getElementById('citizen-results')?.removeAttribute('aria-busy');
            }
        }, delay);
    }

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        triggerLiveSearch(0);
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => triggerLiveSearch());
        searchInput.addEventListener('search', () => triggerLiveSearch(0));
    }

    if (ageInput) {
        ageInput.addEventListener('input', () => triggerLiveSearch());
        ageInput.addEventListener('change', () => triggerLiveSearch(0));
    }
});
</script>

@endsection