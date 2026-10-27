@extends('templates.layout')

@section('CSSown')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

<style>
#calendar {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.calendar-wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    min-height: 0;
}

.calendar-wrapper,
#calendar,
#calendar .fc,
#calendar .fc-view-harness,
#calendar .fc-scroller-harness,
#calendar .fc-scroller {
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}

#calendar .fc table {
    width: 100% !important;
    table-layout: fixed;
}

#calendar .fc .fc-daygrid-day-frame {
    min-height: 48px;
}

#calendar .fc .fc-daygrid-day-number {
    padding: 4px;
    font-size: 0.85rem;
}

#calendar .fc .fc-event-title,
#calendar .fc .fc-event-time {
    overflow: hidden;
    text-overflow: ellipsis;
}

#calendar .fc .fc-toolbar {
    gap: 8px;
    flex-wrap: wrap;
}

#calendar .fc .fc-toolbar-title {
    font-size: 1.25rem;
    line-height: 1.3;
}

#calendar .fc .fc-button {
    white-space: nowrap;
}

/* =========================
BUTTON STYLES (NEW)
========================= */

.btn-primary {
    background: #0E42B1;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-secondary {
    background: #e2e8f0;
    border: none;
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
}

/* optional delete style override */

.btn-danger {
    background: #e53e3e;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
}

/* =========================
MODAL BASE
========================= */

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
    z-index: 2147483647;
}

/* =========================
MODAL CONTENT
========================= */

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    width: min(760px, calc(100vw - 32px));
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
}

.modal-content h2 {
    margin: 0 0 22px;
    color: #172033;
    font-size: 24px;
}

.add-event-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.add-event-field {
    min-width: 0;
}

.add-event-field-full {
    grid-column: 1 / -1;
}

.add-event-field label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
}

/* =========================
INPUTS
========================= */

.modal-content input,
.modal-content textarea,
.modal-content select {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    margin-bottom: 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background: #fff;
    color: #111;
}

/* DISABLED STYLE */

.modal-content input:disabled,
.modal-content textarea:disabled,
.modal-content select:disabled {
    background: #e5e5e5;
    color: #666;
    cursor: not-allowed;
    opacity: 1;
}

/* ACTIONS */

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
}

<<<<<<< HEAD
=======
.vaccination-schedule-modal .modal-content { width: min(1080px, calc(100vw - 32px)); overflow-x: hidden; }
.vaccination-schedule-help { margin: -10px 0 18px; color: #64748b; font-size: 13px; line-height: 1.5; }
.citizen-picker-modal .modal-content { width: min(680px, calc(100vw - 32px)); }
.citizen-picker-search { margin-bottom: 14px !important; }
.citizen-picker-filters { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 14px; }
.citizen-picker-filters input:not([type="checkbox"]), .citizen-picker-filters select { width: 100%; min-width: 0; margin: 0; padding: 9px 10px; border: 1px solid #dbe3ed; border-radius: 7px; background: #fff; color: #172033; box-sizing: border-box; }
.citizen-picker-filters .citizen-picker-search { grid-column: 1 / -1; }
.citizen-picker-deceased { display: flex; align-items: center; gap: 7px; color: #475569; font-size: 12px; }
.citizen-picker-deceased input { width: auto; margin: 0; }
.citizen-picker-list { display: grid; gap: 8px; max-height: 420px; overflow-y: auto; }
.citizen-picker-option { display: flex; align-items: center; justify-content: space-between; gap: 14px; width: 100%; padding: 12px 14px; border: 1px solid #dbe3ed; border-radius: 8px; background: #fff; color: #172033; text-align: left; cursor: pointer; }
.citizen-picker-option:hover { border-color: #2563eb; background: #eff6ff; }
.citizen-picker-option strong { display: block; font-size: 14px; }
.citizen-picker-option small { display: block; margin-top: 3px; color: #64748b; font-size: 11px; }
.citizen-picker-empty { padding: 18px; color: #64748b; text-align: center; }
.vaccination-schedule-rows { display: grid; gap: 12px; }
.vaccination-schedule-row { display: grid; grid-template-columns: minmax(180px, 1.3fr) minmax(150px, 1fr) minmax(120px, .8fr) minmax(150px, 1fr) 38px; gap: 8px; align-items: end; padding: 12px; border: 1px solid #dbe3ed; border-radius: 8px; background: #f8fafc; }
.vaccination-schedule-row label { display: block; color: #334155; font-size: 12px; font-weight: 600; }
.vaccination-schedule-row input, .vaccination-schedule-row select, .schedule-citizen-picker { width: 100%; min-height: 40px; margin-top: 6px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #172033; box-sizing: border-box; }
.schedule-citizen-picker { overflow: hidden; text-align: left; text-overflow: ellipsis; white-space: nowrap; cursor: pointer; }
.remove-schedule-row { width: 38px; height: 40px; border: 0; border-radius: 7px; background: #fee2e2; color: #b91c1c; cursor: pointer; font-size: 20px; }
.add-schedule-row { margin-top: 12px; border: 1px dashed #2563eb; background: #eff6ff; color: #1d4ed8; }

>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
@media (max-width: 640px) {
    .modal-content { padding: 22px; }
    .add-event-form-grid { grid-template-columns: 1fr; gap: 14px; }
    .add-event-field-full { grid-column: auto; }
<<<<<<< HEAD
=======
}

@media (max-width: 900px) {
    .vaccination-schedule-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .vaccination-schedule-row label[style] { grid-column: 1 / -1 !important; }
    .vaccination-schedule-row .remove-schedule-row { width: 100%; }
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
}

/* =========================================================
ADD EVENT CONFIRMATION MODAL
========================================================= */

.event-confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;

    width: 100%;
    height: 100%;

    background: rgba(0, 0, 0, 0.55);

    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);

    align-items: center;
    justify-content: center;

    z-index: 2147483647;
}

.event-confirm-overlay.active {
    display: flex;
}

.event-confirm-modal {
    width: 515px;
    max-width: 90%;

    background: #ffffff;

    border-radius: 16px;

    padding: 40px 45px;

    text-align: center;

    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);

    animation: eventConfirmModalIn 0.2s ease-out;
}

.event-confirm-modal h2 {
    margin: 0 0 20px;

    font-size: 25px;

    font-weight: 700;

    color: #172033;
}

.event-confirm-modal p {
    margin: 0 auto;

    max-width: 440px;

    font-size: 17px;

    line-height: 1.5;

    font-weight: 500;

    color: #526174;
}

.event-confirm-modal p strong {
    color: #172033;

    font-weight: 700;
}

/* =========================
CONFIRMATION BUTTONS
========================= */

.event-confirm-buttons {
    display: flex;

    justify-content: center;

    align-items: center;

    gap: 15px;

    margin-top: 28px;
}

.event-confirm-cancel,
.event-confirm-save {
    border: none;

    border-radius: 8px;

    padding: 14px 25px;

    font-size: 16px;

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s ease;
}

/* CANCEL */

.event-confirm-cancel {
    background: #e5e7eb;

    color: #374151;
}

.event-confirm-cancel:hover {
    background: #d7dbe1;
}

/* CONFIRM */

.event-confirm-save {
    background: #2563eb;

    color: white;
}

.event-confirm-save:hover {
    background: #1d4ed8;
}

/* =========================
CONFIRMATION ANIMATION
========================= */

@keyframes eventConfirmModalIn {

    from {
        opacity: 0;
        transform: scale(0.96) translateY(8px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

}

/* =========================
MOBILE
========================= */

@media (max-width: 600px) {
    .calendar-wrapper {
        width: 100%;
        overflow: visible;
    }

    #calendar {
        padding: 10px 8px;
        border-radius: 12px;
        overflow: hidden;
    }

    #calendar .fc .fc-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
    }

    /* Keep long month names (for example, September 2028) on one line.
       Place the navigation controls on their own row on narrow screens. */
    #calendar .fc .fc-toolbar-chunk:first-child {
        flex: 1 1 100%;
        width: 100%;
        min-width: 0;
    }

    #calendar .fc .fc-toolbar-chunk:nth-child(2) {
        display: none;
    }

    #calendar .fc .fc-toolbar-chunk:last-child {
        flex: 0 0 auto;
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    #calendar .fc .fc-toolbar-title {
        display: block;
        width: 100%;
        font-size: clamp(1rem, 4.5vw, 1.2rem);
        line-height: 1.25;
        white-space: nowrap;
        overflow: visible;
        word-break: keep-all;
    }

    #calendar .fc .fc-button {
        padding: 0.35em 0.5em;
        font-size: 0.8rem;
    }

    @media (max-width: 360px) {
        #calendar .fc .fc-toolbar-title {
            font-size: 0.98rem;
        }

        #calendar .fc .fc-button {
            padding: 0.3em 0.42em;
            font-size: 0.75rem;
        }
    }

    #calendar .fc .fc-col-header-cell-cushion {
        padding: 6px 1px;
        font-size: 0.72rem;
    }

    #calendar .fc .fc-daygrid-day-frame {
        min-height: 42px;
    }

    #calendar .fc .fc-daygrid-day-number {
        padding: 3px;
        font-size: 0.72rem;
    }

    #calendar .fc .fc-daygrid-day-events {
        margin-top: 1px;
    }

    #calendar .fc .fc-daygrid-event {
        margin: 1px 1px 0;
        font-size: 0.65rem;
        white-space: normal;
    }

    #calendar .fc .fc-event-title {
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #calendar .fc .fc-daygrid-more-link {
        font-size: 0.65rem;
    }

    .modal {
        padding: 12px;
        box-sizing: border-box;
    }

    .modal-content {
        width: 100%;
        max-width: 100%;
        max-height: calc(100dvh - 24px);
        overflow-y: auto;
        box-sizing: border-box;
        padding: 16px;
    }

    .modal-actions {
        flex-wrap: wrap;
    }

    .modal-actions button {
        flex: 1 1 auto;
    }

    .event-confirm-modal {
        width: 100%;
        max-width: 90%;

        padding: 30px 25px;
    }

    .event-confirm-buttons {
        flex-direction: column;
    }

    .event-confirm-cancel,
    .event-confirm-save {
        width: 100%;
    }

    .citizen-picker-filters { grid-template-columns: 1fr; }
    .citizen-picker-filters .citizen-picker-search { grid-column: auto; }
    .vaccination-schedule-row { grid-template-columns: 1fr; }
    .vaccination-schedule-row label[style] { grid-column: auto !important; }

}
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Event Calendar</h1>
        <p>Manage scheduled events and activities</p>
    </div>
    @if($vaccinationSchedulingEnabled)
        <button type="button" id="openVaccinationScheduleBtn" class="btn-primary">Schedule Vaccination</button>
    @endif
</div>

<div class="calendar-wrapper">
    <div id="calendar"></div>
</div>

@if($vaccinationSchedulingEnabled)
    @php
        $calendarVaccineOptions = [
            'BCG',
            'Hepatitis B (HepB)',
            'Pentavalent (Penta)',
            'Oral Polio Vaccine (OPV)',
            'Inactivated Polio Vaccine (IPV)',
            'Pneumococcal Conjugate Vaccine (PCV)',
            'Measles, Mumps, Rubella (MMR)',
            'Measles-Rubella (MR)',
            'Tetanus-Diphtheria (Td)',
            'Human Papillomavirus (HPV)',
        ];
    @endphp
    <div id="vaccinationScheduleModal" class="modal vaccination-schedule-modal">
        <div class="modal-content">
            <h2>Schedule Vaccination</h2>
            <p class="vaccination-schedule-help">Add one schedule at a time. Repeat the action to create multiple schedules for the same citizen.</p>
            <form method="POST" action="{{ route('citizen.vaccination.schedule') }}" id="vaccinationScheduleForm">
                @csrf
                <div class="vaccination-schedule-rows" id="vaccinationScheduleRows">
                    <div class="vaccination-schedule-row" data-schedule-index="0">
                        <label>Citizen
                            <input type="hidden" name="schedules[0][citizen_id]" class="schedule-citizen-id">
                            <button type="button" class="schedule-citizen-picker">Choose citizen</button>
                        </label>
                        <label>Vaccine or immunization
                            <select name="schedules[0][vaccine_name]" class="schedule-vaccine-name" required>
                                <option value="">Select a vaccine</option>
                                @foreach($calendarVaccineOptions as $calendarVaccine)
                                    <option value="{{ $calendarVaccine }}">{{ $calendarVaccine }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Dose / label
                            <input type="text" name="schedules[0][dose_number]" placeholder="Dose 1 or Booster">
                        </label>
                        <label>Scheduled date
                            <input type="date" name="schedules[0][scheduled_date]" min="{{ now()->format('Y-m-d') }}" required>
                        </label>
                        <button type="button" class="remove-schedule-row" aria-label="Remove schedule row" disabled>&times;</button>
                        <label style="grid-column: 1 / -2;">Notes
                            <input type="text" name="schedules[0][notes]" placeholder="Optional instructions">
                        </label>
                    </div>
                </div>
                <button type="button" id="addVaccinationScheduleRow" class="btn-secondary add-schedule-row">+ Add another schedule</button>
                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Save All Schedules</button>
                    <button type="button" id="closeVaccinationScheduleBtn" class="btn-secondary">Close</button>
                </div>
            </form>
        </div>
    </div>

    <div id="citizenPickerModal" class="modal citizen-picker-modal">
        <div class="modal-content">
            <h2>Choose Citizen</h2>
            <div class="citizen-picker-filters">
                <input type="search" id="citizenPickerSearch" class="citizen-picker-search" placeholder="Search citizen name or ID">
                <select id="citizenPickerPurok">
                    <option value="">All Purok</option>
                    @foreach($calendarPuroks as $calendarPurok)
                        <option value="{{ strtolower($calendarPurok->name) }}">{{ $calendarPurok->name }}</option>
                    @endforeach
                </select>
                <input type="text" id="citizenPickerAge" placeholder="Age or range (e.g. 8 or 4-10)">
                <select id="citizenPickerSex">
                    <option value="">All Sex</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                <select id="citizenPickerHousing">
                    <option value="">All Housing</option>
                    <option value="permanent resident">Permanent Resident</option>
                    <option value="renter">Renter</option>
                </select>
                <select id="citizenPickerHealth">
                    <option value="">All Health Status</option>
                    <option value="pregnant">Pregnant</option>
                    <option value="in_pills">In-Pills</option>
                </select>
                <select id="citizenPickerVaccine">
                    <option value="">All Vaccines</option>
                    <option value="bcg">BCG</option>
                    <option value="hepb">Hepatitis B</option>
                    <option value="penta">Pentavalent</option>
                    <option value="opv">OPV</option>
                    <option value="ipv">IPV</option>
                    <option value="pcv">PCV</option>
                    <option value="mmr">MMR</option>
                    <option value="mr">MR</option>
                    <option value="td">Td</option>
                    <option value="hpv">HPV</option>
                </select>
                <label class="citizen-picker-deceased"><input type="checkbox" id="citizenPickerIncludeDeceased"> Include deceased residents</label>
            </div>
            <div class="citizen-picker-list" id="citizenPickerList">
                @forelse($calendarCitizens as $calendarCitizen)
                    <button type="button" class="citizen-picker-option"
                        data-citizen-id="{{ $calendarCitizen->id }}"
                        data-citizen-label="{{ $calendarCitizen->Citizen_LName }}, {{ $calendarCitizen->Citizen_FName }} - {{ $calendarCitizen->Citizen_Purok ?: 'Unassigned' }}"
                        data-search="{{ strtolower($calendarCitizen->Citizen_FName . ' ' . $calendarCitizen->Citizen_LName . ' ' . $calendarCitizen->id) }}"
                        data-purok="{{ strtolower($calendarCitizen->Citizen_Purok ?: '') }}"
                        data-age="{{ $calendarCitizen->Citizen_Age }}"
                        data-sex="{{ strtolower($calendarCitizen->Citizen_Sex ?: '') }}"
                        data-housing="{{ strtolower($calendarCitizen->Citizen_HousingStatus ?: '') }}"
                        data-pregnant="{{ $calendarCitizen->Citizen_Pregnant ? '1' : '0' }}"
                        data-pills="{{ $calendarCitizen->Citizen_InPills ? '1' : '0' }}"
                        data-deceased="{{ $calendarCitizen->Citizen_IsDeceased ? '1' : '0' }}"
                        data-vaccines="{{ strtolower($calendarCitizen->vaccinations->pluck('vaccine_name')->implode('|')) }}">
                        <span>
                            <strong>{{ $calendarCitizen->Citizen_LName }}, {{ $calendarCitizen->Citizen_FName }}</strong>
                            <small>Citizen ID: C-{{ str_pad($calendarCitizen->id, 4, '0', STR_PAD_LEFT) }} · {{ $calendarCitizen->Citizen_Purok ?: 'Unassigned' }}</small>
                        </span>
                        <span aria-hidden="true">Select</span>
                    </button>
                @empty
                    <div class="citizen-picker-empty">No assigned citizens available.</div>
                @endforelse
            </div>
            <div class="modal-actions">
                <button type="button" id="closeCitizenPickerBtn" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>
@endif

<!-- =========================
EDIT MODAL
========================= -->

<div id="eventModal" class="modal">

    <div class="modal-content">

        <h2>Edit Event</h2>

        <input type="hidden" id="event_id">

        <label>Title</label>
        <input type="text" id="event_title" disabled>

        <label>Date</label>
        <input type="date" id="event_date" disabled>

        <label>Time</label>
        <input type="time" id="event_time" disabled>

        <label>Description</label>
        <textarea id="event_description" disabled></textarea>

        <div class="modal-actions">

            <button
                id="editBtn"
                class="btn-primary"
            >
                Edit
            </button>

            <button
                id="saveBtn"
                class="btn-primary"
                style="display:none;"
            >
                Save
            </button>

            <button
                id="deleteBtn"
                class="btn-danger"
            >
                Delete
            </button>

            <button
                onclick="closeModal()"
                class="btn-secondary"
            >
                Close
            </button>

        </div>

    </div>

</div>

<!-- =========================
ADD MODAL
========================= -->

<div id="addModal" class="modal">

    <div class="modal-content add-event-modal-content">

        <h2>Add Event</h2>

        <div class="add-event-form-grid">
            <div class="add-event-field add-event-field-full">
                <label for="add_title">Title</label>
                <input type="text" id="add_title" required>
            </div>

            <div class="add-event-field">
                <label for="add_date">Date</label>
                <input type="date" id="add_date" required>
            </div>

            <div class="add-event-field">
                <label for="add_time">Time</label>
                <input type="time" id="add_time" required>
            </div>

            <div class="add-event-field add-event-field-full">
                <label for="add_description">Description</label>
                <textarea id="add_description" rows="5" required></textarea>
            </div>
        </div>

        <div class="modal-actions">

            <button
                id="addSaveBtn"
                class="btn-primary"
            >
                Save
            </button>

            <button
                onclick="closeAddModal()"
                class="btn-secondary"
            >
                Close
            </button>

        </div>

    </div>

</div>

<!-- =========================================================
ADD EVENT CONFIRMATION MODAL
========================================================= -->

<div
    id="addEventConfirmModal"
    class="event-confirm-overlay"
>

    <div class="event-confirm-modal">

        <h2>
            Confirm New Event
        </h2>

        <p>
            Are you sure you want to add
            <strong id="confirmEventTitle">
                this event
            </strong>?
        </p>

        <div class="event-confirm-buttons">

            <button
                type="button"
                id="cancelEventConfirmation"
                class="event-confirm-cancel"
            >
                Cancel
            </button>

            <button
                type="button"
                id="confirmEventSubmission"
                class="event-confirm-save"
            >
                Yes, Add Event
            </button>

        </div>

    </div>

</div>

<!-- =========================================================
DELETE EVENT CONFIRMATION MODAL
========================================================= -->

<div
    id="deleteEventConfirmModal"
    class="event-confirm-overlay"
>

    <div class="event-confirm-modal">

        <h2>
            Confirm Delete
        </h2>

        <p>
            Are you sure you want to delete
            <strong id="confirmDeleteEventTitle">
                this event
            </strong>?
        </p>

        <div class="event-confirm-buttons">

            <button
                type="button"
                id="cancelDeleteConfirmation"
                class="event-confirm-cancel"
            >
                Cancel
            </button>

            <button
                type="button"
                id="confirmDeleteSubmission"
                class="event-confirm-save"
            >
                Yes, Delete
            </button>

        </div>

    </div>

</div>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       ORIGINAL VARIABLES
    ========================================================= */

    let modal =
        document.getElementById('eventModal');

    let addModal =
        document.getElementById('addModal');

    const vaccinationScheduleModal = document.getElementById('vaccinationScheduleModal');
    const openVaccinationScheduleBtn = document.getElementById('openVaccinationScheduleBtn');
    const closeVaccinationScheduleBtn = document.getElementById('closeVaccinationScheduleBtn');
    const vaccinationScheduleForm = document.getElementById('vaccinationScheduleForm');
    const vaccinationScheduleRows = document.getElementById('vaccinationScheduleRows');
    const addVaccinationScheduleRow = document.getElementById('addVaccinationScheduleRow');
    const citizenPickerModal = document.getElementById('citizenPickerModal');
    const closeCitizenPickerBtn = document.getElementById('closeCitizenPickerBtn');
    const citizenPickerSearch = document.getElementById('citizenPickerSearch');
    const citizenPickerPurok = document.getElementById('citizenPickerPurok');
    const citizenPickerAge = document.getElementById('citizenPickerAge');
    const citizenPickerSex = document.getElementById('citizenPickerSex');
    const citizenPickerHousing = document.getElementById('citizenPickerHousing');
    const citizenPickerHealth = document.getElementById('citizenPickerHealth');
    const citizenPickerVaccine = document.getElementById('citizenPickerVaccine');
    const citizenPickerIncludeDeceased = document.getElementById('citizenPickerIncludeDeceased');
    const citizenPickerOptions = document.querySelectorAll('.citizen-picker-option');
    const vaccineOptions = @json($calendarVaccineOptions ?? []);
    let activeScheduleRow = null;
    let nextScheduleIndex = 1;

    function vaccineOptionsMarkup() {
        return '<option value="">Select a vaccine</option>' + vaccineOptions.map(function (vaccine) {
            return '<option value="' + vaccine.replace(/"/g, '&quot;') + '">' + vaccine + '</option>';
        }).join('');
    }

    function scheduleRowMarkup(index) {
        return '<div class="vaccination-schedule-row" data-schedule-index="' + index + '">' +
            '<label>Citizen<input type="hidden" name="schedules[' + index + '][citizen_id]" class="schedule-citizen-id"><button type="button" class="schedule-citizen-picker">Choose citizen</button></label>' +
            '<label>Vaccine or immunization<select name="schedules[' + index + '][vaccine_name]" required>' + vaccineOptionsMarkup() + '</select></label>' +
            '<label>Dose / label<input type="text" name="schedules[' + index + '][dose_number]" placeholder="Dose 1 or Booster"></label>' +
            '<label>Scheduled date<input type="date" name="schedules[' + index + '][scheduled_date]" min="{{ now()->format('Y-m-d') }}" required></label>' +
            '<button type="button" class="remove-schedule-row" aria-label="Remove schedule row">&times;</button>' +
            '<label style="grid-column: 1 / -2;">Notes<input type="text" name="schedules[' + index + '][notes]" placeholder="Optional instructions"></label>' +
            '</div>';
    }

    function updateRemoveButtons() {
        const rows = vaccinationScheduleRows.querySelectorAll('.vaccination-schedule-row');
        rows.forEach(function (row) {
            row.querySelector('.remove-schedule-row').disabled = rows.length === 1;
        });
    }

    function resetVaccinationScheduleRows() {
        vaccinationScheduleRows.innerHTML = scheduleRowMarkup(0);
        nextScheduleIndex = 1;
        wireScheduleRow(vaccinationScheduleRows.firstElementChild);
        updateRemoveButtons();
    }

    function wireScheduleRow(row) {
        row.querySelector('.schedule-citizen-picker').addEventListener('click', function () {
            activeScheduleRow = row;
            citizenPickerModal.style.display = 'flex';
            citizenPickerSearch.focus();
            applyCitizenPickerFilters();
        });

        row.querySelector('.remove-schedule-row').addEventListener('click', function () {
            row.remove();
            updateRemoveButtons();
        });
    }

    function citizenMatchesAge(age, filter) {
        if (!filter) return true;
        if (filter.includes('-')) {
            const range = filter.split('-').map(Number);
            return range.length === 2 && Number.isFinite(range[0]) && Number.isFinite(range[1]) && Number(age) >= range[0] && Number(age) <= range[1];
        }
        return Number(age) === Number(filter);
    }

    function applyCitizenPickerFilters() {
        const search = citizenPickerSearch.value.toLowerCase().trim();
        const purok = citizenPickerPurok.value;
        const age = citizenPickerAge.value.trim();
        const sex = citizenPickerSex.value;
        const housing = citizenPickerHousing.value;
        const health = citizenPickerHealth.value;
        const vaccine = citizenPickerVaccine.value;
        const includeDeceased = citizenPickerIncludeDeceased.checked;

        citizenPickerOptions.forEach(function (option) {
            const matches = (!search || option.dataset.search.includes(search))
                && (!purok || option.dataset.purok === purok)
                && citizenMatchesAge(option.dataset.age, age)
                && (!sex || option.dataset.sex === sex)
                && (!housing || option.dataset.housing === housing)
                && (!health || (health === 'pregnant' ? option.dataset.pregnant === '1' : option.dataset.pills === '1'))
                && (!vaccine || option.dataset.vaccines.includes(vaccine))
                && (includeDeceased || option.dataset.deceased !== '1');

            option.style.display = matches ? 'flex' : 'none';
        });
    }

    if (openVaccinationScheduleBtn && vaccinationScheduleModal) {
        wireScheduleRow(vaccinationScheduleRows.firstElementChild);

        openVaccinationScheduleBtn.addEventListener('click', function () {
            vaccinationScheduleModal.style.display = 'flex';
        });

        citizenPickerOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                if (!activeScheduleRow) return;
                activeScheduleRow.querySelector('.schedule-citizen-id').value = option.dataset.citizenId;
                activeScheduleRow.querySelector('.schedule-citizen-picker').textContent = option.dataset.citizenLabel;
                citizenPickerModal.style.display = 'none';
            });
        });

        [citizenPickerSearch, citizenPickerPurok, citizenPickerAge, citizenPickerSex, citizenPickerHousing, citizenPickerHealth, citizenPickerVaccine, citizenPickerIncludeDeceased].forEach(function (field) {
            field.addEventListener(field.type === 'search' || field.type === 'text' ? 'input' : 'change', applyCitizenPickerFilters);
        });

        closeCitizenPickerBtn.addEventListener('click', function () {
            citizenPickerModal.style.display = 'none';
        });

        citizenPickerModal.addEventListener('click', function (event) {
            if (event.target === citizenPickerModal) {
                citizenPickerModal.style.display = 'none';
            }
        });

        closeVaccinationScheduleBtn.addEventListener('click', function () {
            vaccinationScheduleModal.style.display = 'none';
            vaccinationScheduleForm.reset();
            resetVaccinationScheduleRows();
        });

        vaccinationScheduleModal.addEventListener('click', function (event) {
            if (event.target === vaccinationScheduleModal) {
                vaccinationScheduleModal.style.display = 'none';
                vaccinationScheduleForm.reset();
                resetVaccinationScheduleRows();
            }
        });

        addVaccinationScheduleRow.addEventListener('click', function () {
            vaccinationScheduleRows.insertAdjacentHTML('beforeend', scheduleRowMarkup(nextScheduleIndex));
            wireScheduleRow(vaccinationScheduleRows.lastElementChild);
            nextScheduleIndex += 1;
            updateRemoveButtons();
        });

        vaccinationScheduleForm.addEventListener('submit', function (event) {
            const missingCitizen = Array.from(vaccinationScheduleRows.querySelectorAll('.schedule-citizen-id')).find(function (input) {
                return !input.value;
            });
            if (missingCitizen) {
                event.preventDefault();
                activeScheduleRow = missingCitizen.closest('.vaccination-schedule-row');
                activeScheduleRow.querySelector('.schedule-citizen-picker').focus();
            }
        });
    }


    let editBtn =
        document.getElementById('editBtn');

    let saveBtn =
        document.getElementById('saveBtn');

    let deleteBtn =
        document.getElementById('deleteBtn');


    let titleField =
        document.getElementById('event_title');

    let dateField =
        document.getElementById('event_date');

    let timeField =
        document.getElementById('event_time');

    let descField =
        document.getElementById('event_description');


    let addTitle =
        document.getElementById('add_title');

    let addDate =
        document.getElementById('add_date');

    let addTime =
        document.getElementById('add_time');

    let addDesc =
        document.getElementById('add_description');


    let currentEventId = null;


    /* =========================================================
       NEW:
       ADD EVENT CONFIRMATION ELEMENTS
    ========================================================= */

    let addEventConfirmModal =
        document.getElementById(
            'addEventConfirmModal'
        );

    let confirmEventTitle =
        document.getElementById(
            'confirmEventTitle'
        );

    let cancelEventConfirmation =
        document.getElementById(
            'cancelEventConfirmation'
        );

    let confirmEventSubmission =
        document.getElementById(
            'confirmEventSubmission'
        );


    /* =========================================================
       NEW:
       DELETE EVENT CONFIRMATION ELEMENTS
    ========================================================= */

    let deleteEventConfirmModal =
        document.getElementById(
            'deleteEventConfirmModal'
        );

    let confirmDeleteEventTitle =
        document.getElementById(
            'confirmDeleteEventTitle'
        );

    let cancelDeleteConfirmation =
        document.getElementById(
            'cancelDeleteConfirmation'
        );

    let confirmDeleteSubmission =
        document.getElementById(
            'confirmDeleteSubmission'
        );


    /* =========================================================
       ORIGINAL FUNCTION
    ========================================================= */

    function setReadOnly(state) {

        titleField.disabled = state;

        dateField.disabled = state;

        timeField.disabled = state;

        descField.disabled = state;


        editBtn.style.display =
            state
                ? 'inline-block'
                : 'none';

        saveBtn.style.display =
            state
                ? 'none'
                : 'inline-block';
    }


    /* =========================================================
       ORIGINAL FUNCTION
    ========================================================= */

    function openModal(event) {

        currentEventId = event.id;


        titleField.value =
            event.title || '';


        let startDate =
            event.start
                ? new Date(event.start)
                : null;


        if (startDate) {

            dateField.value =
                startDate
                    .toISOString()
                    .split('T')[0];

            timeField.value =
                startDate
                    .toTimeString()
                    .slice(0,5);
        }


        descField.value =
            event.extendedProps?.description ?? '';


        setReadOnly(true);

        modal.style.display =
            'flex';
    }


    /* =========================================================
       ORIGINAL FUNCTION
    ========================================================= */

    function openAddModal(dateStr) {

        addDate.value =
            dateStr;

        addTitle.value =
            '';

        addTime.value =
            '';

        addDesc.value =
            '';


        addModal.style.display =
            'flex';
    }


    /* =========================================================
       ORIGINAL FUNCTION
    ========================================================= */

    window.closeModal = function () {

        modal.style.display =
            'none';

        setReadOnly(true);
    };


    /* =========================================================
       ORIGINAL FUNCTION
    ========================================================= */

    window.closeAddModal = function () {

        addModal.style.display =
            'none';
    };


    /* =========================================================
       ORIGINAL EDIT BUTTON
    ========================================================= */

    editBtn.addEventListener(
        'click',
        function () {

            setReadOnly(false);

        }
    );


    /* =========================================================
       ORIGINAL EDIT SAVE
       UNCHANGED
    ========================================================= */

    saveBtn.addEventListener(
        'click',
        function () {

            let datetime =
                dateField.value +
                'T' +
                timeField.value;


            fetch(
                '/events/' + currentEventId,
                {
                    method: 'PUT',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .getAttribute('content')
                    },

                    body: JSON.stringify({

                        title:
                            titleField.value,

                        start:
                            datetime,

                        description:
                            descField.value

                    })
                }
            )
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to save the event.');
                }

                modal.style.display =
                    'none';

                calendar.refetchEvents();
            })
            .catch(error => {
                console.error(error);
                alert(error.message);
            });

        }
    );


    /* =========================================================
       DELETE EVENT
       NOW USES ACTION CONFIRMATION MODAL
    ========================================================= */

    deleteBtn.addEventListener(
        'click',
        function () {

            /*
             * Get the current event title and display it
             * in the delete confirmation message.
             */

            confirmDeleteEventTitle.textContent =
                titleField.value || 'this event';


            /*
             * Show delete confirmation modal.
             */

            deleteEventConfirmModal.classList.add(
                'active'
            );

        }
    );


    /* =========================================================
       CANCEL DELETE CONFIRMATION

       This only closes the confirmation modal.
       The existing event remains untouched.
    ========================================================= */

    cancelDeleteConfirmation.addEventListener(
        'click',
        function () {

            deleteEventConfirmModal.classList.remove(
                'active'
            );

        }
    );


    /* =========================================================
       CONFIRM DELETE EVENT

       The actual DELETE request happens only after
       the user clicks "Yes, Delete".
    ========================================================= */

    confirmDeleteSubmission.addEventListener(
        'click',
        function () {

            /*
             * Close delete confirmation modal.
             */

            deleteEventConfirmModal.classList.remove(
                'active'
            );


            /*
             * Perform the original DELETE request.
             */

            fetch(
                '/events/' + currentEventId,
                {
                    method: 'DELETE',

                    headers: {

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .getAttribute('content')

                    }
                }
            )
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to delete the event.');
                }

                /*
                 * Close the original Edit Event modal.
                 */

                modal.style.display =
                    'none';


                /*
                 * Refresh calendar after deletion.
                 */

                calendar.refetchEvents();

            })
            .catch(error => {
                console.error(error);
                alert(error.message);
            });

        }
    );


    /* =========================================================
       ADD EVENT SAVE BUTTON

       ORIGINAL POST IS NOW MOVED INTO THE
       CONFIRMATION BUTTON BELOW.

       CLICKING SAVE NO LONGER POSTS IMMEDIATELY.
    ========================================================= */

    document
        .getElementById('addSaveBtn')
        .addEventListener(
            'click',
            function () {


                /* =================================================
                   NEW:
                   REQUIRED FIELD VALIDATION

                   The user cannot proceed to the confirmation
                   modal until all four fields are filled.
                ================================================= */

                if (
                    !addTitle.value.trim() ||
                    !addDate.value ||
                    !addTime.value ||
                    !addDesc.value.trim()
                ) {

                    /*
                     * Focus the first empty field.
                     */

                    if (!addTitle.value.trim()) {

                        addTitle.focus();

                    }
                    else if (!addDate.value) {

                        addDate.focus();

                    }
                    else if (!addTime.value) {

                        addTime.focus();

                    }
                    else if (!addDesc.value.trim()) {

                        addDesc.focus();

                    }

                    return;
                }


                /* Get the entered event title */

                let eventTitle =
                    addTitle.value.trim();


                /*
                 * Show the event title in the
                 * confirmation message.
                 */

                confirmEventTitle.textContent =
                    eventTitle || 'this event';


                /*
                 * Show confirmation modal.
                 */

                addEventConfirmModal.classList.add(
                    'active'
                );

            }
        );


    /* =========================================================
       CANCEL ADD EVENT CONFIRMATION

       IMPORTANT:
       This does NOT clear the user's inputs.
       It simply returns them to the Add Event modal.
    ========================================================= */

    cancelEventConfirmation.addEventListener(
        'click',
        function () {

            addEventConfirmModal.classList.remove(
                'active'
            );

        }
    );


    /* =========================================================
       CONFIRM AND ACTUALLY SAVE THE ADDED EVENT
    ========================================================= */

    confirmEventSubmission.addEventListener(
        'click',
        function () {


            /*
             * Get the date and time AFTER confirmation.
             */

            let datetime =
                addDate.value +
                'T' +
                addTime.value;


            /*
             * Close confirmation modal.
             */

            addEventConfirmModal.classList.remove(
                'active'
            );


            /*
             * Send the original POST request.
             */

            fetch(
                '/events',
                {
                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .getAttribute('content')

                    },

                    body: JSON.stringify({

                        title:
                            addTitle.value,

                        start:
                            datetime,

                        description:
                            addDesc.value

                    })
                }
            )
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to create the event.');
                }

                /*
                 * Close Add Event modal
                 * only after saving.
                 */

                addModal.style.display =
                    'none';


                /*
                 * Refresh calendar so the
                 * newly added event appears.
                 */

                calendar.refetchEvents();

            })
            .catch(error => {
                console.error(error);
                alert(error.message);
            });

        }
    );


    /* =========================================================
       CLICK OUTSIDE ADD CONFIRMATION MODAL
    ========================================================= */

    addEventConfirmModal.addEventListener(
        'click',
        function (event) {

            if (
                event.target ===
                addEventConfirmModal
            ) {

                addEventConfirmModal.classList.remove(
                    'active'
                );

            }

        }
    );


    /* =========================================================
       ESCAPE KEY FOR ADD CONFIRMATION MODAL
    ========================================================= */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                addEventConfirmModal.classList.contains(
                    'active'
                )
            ) {

                addEventConfirmModal.classList.remove(
                    'active'
                );

            }

        }
    );


    /* =========================================================
       CLICK OUTSIDE DELETE CONFIRMATION MODAL
    ========================================================= */

    deleteEventConfirmModal.addEventListener(
        'click',
        function (event) {

            if (
                event.target ===
                deleteEventConfirmModal
            ) {

                deleteEventConfirmModal.classList.remove(
                    'active'
                );

            }

        }
    );


    /* =========================================================
       ESCAPE KEY FOR DELETE CONFIRMATION MODAL
    ========================================================= */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                deleteEventConfirmModal.classList.contains(
                    'active'
                )
            ) {

                deleteEventConfirmModal.classList.remove(
                    'active'
                );

            }

        }
    );


    /* =========================================================
       ORIGINAL FULLCALENDAR
    ========================================================= */

    let calendar =
        new FullCalendar.Calendar(
            document.getElementById('calendar'),
            {

                initialView:
                    'dayGridMonth',
                height: 'auto',
                contentHeight: 'auto',
                aspectRatio: 1.15,
                expandRows: true,
                handleWindowResize: true,
                fixedWeekCount: false,
                dayMaxEvents: true,


                events:
                    function(
                        fetchInfo,
                        successCallback
                    ) {

                        fetch('/events')
                            .then(
                                res =>
                                    res.json()
                            )
                            .then(
                                data =>
                                    successCallback(
                                        data
                                    )
                            );

                    },


                eventClick:
                    function(info) {

                        openModal(
                            info.event
                        );

                    },


                dateClick:
                    function(info) {

                        openAddModal(
                            info.dateStr
                        );

                    }

            }
        );


    calendar.render();

});

</script>

@endsection