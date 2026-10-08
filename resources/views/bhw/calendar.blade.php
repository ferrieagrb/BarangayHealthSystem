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
}

.calendar-wrapper {
    min-height: 80vh;
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
    padding: 20px;
    border-radius: 12px;
    width: 320px;
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

}
</style>
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Event Calendar</h1>
        <p>Manage scheduled events and activities</p>
    </div>
</div>

<div class="calendar-wrapper">
    <div id="calendar"></div>
</div>

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

    <div class="modal-content">

        <h2>Add Event</h2>

        <label>Title</label>
        <input
            type="text"
            id="add_title"
            required
        >

        <label>Date</label>
        <input
            type="date"
            id="add_date"
            required
        >

        <label>Time</label>
        <input
            type="time"
            id="add_time"
            required
        >

        <label>Description</label>
        <textarea
            id="add_description"
            required
        ></textarea>

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
            .then(() => {

                modal.style.display =
                    'none';

                calendar.refetchEvents();

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
            .then(() => {

                /*
                 * Close the original Edit Event modal.
                 */

                modal.style.display =
                    'none';


                /*
                 * Refresh calendar after deletion.
                 */

                calendar.refetchEvents();

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
            .then(() => {

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