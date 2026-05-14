<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">

<title>Barangay Amuyong</title>

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
</style>
</head>

<body>

<div class="topbar">
    <div>BARANGAY HOTLINE: (+63) 958 789 1234</div>
    <div>EMERGENCY HOTLINE: (+63) 958 789 1234</div>
</div>

<div class="hero">
    <div class="nav">
        <img src="../images/amuyong.png" height="75px" width="75px">
        <h2>BARANGAY AMUYONG</h2>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/publiccalendar">Calendar</a></li>
            <li><a href="/publicannouncements">Announcements</a></li>
            <li><a href="/login">Login</a></li>
        </ul>
    </div>

    <div class="tagline">
        <h1>TESTING </h1>
        <h1>AKSYON SA</h1>
        <h1>    IYONG KALUSUGAN</h1>
    </div>
</div>

<div class="section">
    <h2>Calendar</h2>

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
            <button id="editBtn" class="btn-primary">Edit</button>
            <button id="saveBtn" class="btn-primary" style="display:none;">Save</button>
            <button id="deleteBtn" class="btn-danger">Delete</button>
            <button onclick="closeModal()" class="btn-secondary">Close</button>
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
        <input type="text" id="add_title">

        <label>Date</label>
        <input type="date" id="add_date">

        <label>Time</label>
        <input type="time" id="add_time">

        <label>Description</label>
        <textarea id="add_description"></textarea>

        <div class="modal-actions">
            <button id="addSaveBtn" class="btn-primary">Save</button>
            <button onclick="closeAddModal()" class="btn-secondary">Close</button>
        </div>

    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

</div>

<div class="footer">
    <div class="footer-content">
        <div>
            <p>Quick Links</p>
            <ul>
                <li>Home</li>
                <li>Calendar</li>
                <li>Announcements</li>
            </ul>
        </div>

        <div>
            <p>Contacts</p>
            <ul>
                <li>Apply Now</li>
            </ul>
        </div>

        <div>
            <p>Connect to us</p>
            <p>Amuyong Barangay Hall</p>
        </div>
    </div>
</div>

<script>
// Simple scroll effect for navbar
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.hero');
    if (window.scrollY > 50) {
        nav.style.opacity = '0.95';
    } else {
        nav.style.opacity = '1';
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let modal = document.getElementById('eventModal');
    let addModal = document.getElementById('addModal');

    let editBtn = document.getElementById('editBtn');
    let saveBtn = document.getElementById('saveBtn');
    let deleteBtn = document.getElementById('deleteBtn');

    let titleField = document.getElementById('event_title');
    let dateField = document.getElementById('event_date');
    let timeField = document.getElementById('event_time');
    let descField = document.getElementById('event_description');

    let addTitle = document.getElementById('add_title');
    let addDate = document.getElementById('add_date');
    let addTime = document.getElementById('add_time');
    let addDesc = document.getElementById('add_description');

    let currentEventId = null;

    function setReadOnly(state) {
        titleField.disabled = state;
        dateField.disabled = state;
        timeField.disabled = state;
        descField.disabled = state;

        editBtn.style.display = state ? 'inline-block' : 'none';
        saveBtn.style.display = state ? 'none' : 'inline-block';
    }

    function openModal(event) {

        currentEventId = event.id;

        titleField.value = event.title || '';

        let startDate = event.start ? new Date(event.start) : null;

        if (startDate) {
            dateField.value = startDate.toISOString().split('T')[0];
            timeField.value = startDate.toTimeString().slice(0,5);
        }

        descField.value = event.extendedProps?.description ?? '';

        setReadOnly(true);
        modal.style.display = 'flex';
    }

    function openAddModal(dateStr) {
        addDate.value = dateStr;
        addTitle.value = '';
        addTime.value = '';
        addDesc.value = '';

        addModal.style.display = 'flex';
    }

    window.closeModal = function () {
        modal.style.display = 'none';
        setReadOnly(true);
    };

    window.closeAddModal = function () {
        addModal.style.display = 'none';
    };

    editBtn.addEventListener('click', function () {
        setReadOnly(false);
    });

    saveBtn.addEventListener('click', function () {

        let datetime = dateField.value + 'T' + timeField.value;

        fetch('/events/' + currentEventId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                title: titleField.value,
                start: datetime,
                description: descField.value
            })
        })
        .then(() => {
            modal.style.display = 'none';
            calendar.refetchEvents();
        });
    });

    deleteBtn.addEventListener('click', function () {

        if (!confirm("Delete this event?")) return;

        fetch('/events/' + currentEventId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => {
            modal.style.display = 'none';
            calendar.refetchEvents();
        });
    });

    document.getElementById('addSaveBtn').addEventListener('click', function () {

        let datetime = addDate.value + 'T' + addTime.value;

        fetch('/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                title: addTitle.value,
                start: datetime,
                description: addDesc.value
            })
        })
        .then(() => {
            addModal.style.display = 'none';
            calendar.refetchEvents();
        });
    });

    let calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {

        initialView: 'dayGridMonth',

        events: function(fetchInfo, successCallback) {
            fetch('/events')
                .then(res => res.json())
                .then(data => successCallback(data));
        },

        eventClick: function(info) {
            openModal(info.event);
        },

        dateClick: function(info) {
            openAddModal(info.dateStr);
        }
    });

    calendar.render();
});
</script>

</body>
</html>
