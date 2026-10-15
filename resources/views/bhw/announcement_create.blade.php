@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/announce_create.css') }}">
@endsection

@section('content')

<!-- HEADER (same as supplies) -->
<div class="page-top">
    <div class="page-title">
        <h1>Post Announcement</h1>
        <p>Create a new barangay announcement</p>
    </div>

    <a href="{{ route('announcements') }}" class="btn-secondary">
        ← Back
    </a>
</div>

<!-- FORM CONTAINER (same structure as supplies) -->
<div class="form-container">

    <form
        id="announcementForm"
        method="POST"
        action="{{ route('announcements.store') }}"
    >
        @csrf

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- GRID -->
        <div class="form-grid">

            <!-- TITLE -->
            <div class="form-group">
                <label>Title</label>
                <input
                    type="text"
                    name="title"
                    placeholder="Enter title"
                    required
                >
            </div>

            <!-- CATEGORY -->
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Health Advisory">Health Advisory</option>
                    <option value="Urgent Alert">Urgent Alert</option>
                    <option value="Community Program">Community Program</option>
                </select>
            </div>

            <!-- DESCRIPTION (full width like supplies notes style) -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Description</label>
                <textarea
                    name="description"
                    placeholder="Write announcement details..."
                    required
                ></textarea>
            </div>

            <!-- FACEBOOK CROSS-POST CHECKBOX -->
            <div class="form-group" style="grid-column: span 2; margin-top: 5px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: normal;">
                    <input
                        type="checkbox"
                        name="post_to_facebook"
                        value="1"
                        id="postToFacebookCheckbox"
                        checked
                        style="width: 18px; height: 18px; cursor: pointer;"
                    >
                    <span>Also cross-post this announcement directly to the Official Facebook Page</span>
                </label>
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="form-actions">

            <button
                type="submit"
                class="btn-primary"
            >
                Post Announcement
            </button>

            <a
                href="{{ route('announcements') }}"
                class="btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

</div>


<!-- ================================================================
     ANNOUNCEMENT CONFIRMATION MODAL
     ================================================================ -->

<div
    id="announcementConfirmModal"
    class="announcement-confirm-overlay"
>

    <div class="announcement-confirm-modal">

        <h2>
            Confirm Announcement
        </h2>

        <p>
            Are you sure you want to post this announcement?
        </p>


        <!-- ANNOUNCEMENT PREVIEW -->

        <div class="announcement-preview">

            <div class="announcement-preview-item">

                <span class="announcement-preview-label">
                    Title
                </span>

                <strong id="confirmAnnouncementTitle">
                    —
                </strong>

            </div>


            <div class="announcement-preview-item">

                <span class="announcement-preview-label">
                    Category
                </span>

                <strong id="confirmAnnouncementCategory">
                    —
                </strong>

            </div>


            <div class="announcement-preview-item">

                <span class="announcement-preview-label">
                    Description
                </span>

                <p id="confirmAnnouncementDescription">
                    —
                </p>

            </div>

            <div class="announcement-preview-item">

                <span class="announcement-preview-label">
                    Facebook Posting
                </span>

                <strong id="confirmAnnouncementFacebook">
                    —
                </strong>

            </div>

        </div>


        <!-- CONFIRMATION BUTTONS -->

        <div class="announcement-confirm-buttons">

            <button
                type="button"
                id="cancelAnnouncementConfirmation"
                class="announcement-confirm-cancel"
            >
                Cancel
            </button>


            <button
                type="button"
                id="confirmAnnouncementSubmission"
                class="announcement-confirm-save"
            >
                Yes, Post Announcement
            </button>

        </div>

    </div>

</div>


@endsection


@section('scripts')

<script>

document.addEventListener("DOMContentLoaded", function () {

    /* ============================================================
       ANNOUNCEMENT FORM
       ============================================================ */

    const announcementForm =
        document.getElementById("announcementForm");


    /* ============================================================
       CONFIRMATION MODAL
       ============================================================ */

    const announcementConfirmModal =
        document.getElementById(
            "announcementConfirmModal"
        );


    const confirmAnnouncementTitle =
        document.getElementById(
            "confirmAnnouncementTitle"
        );


    const confirmAnnouncementCategory =
        document.getElementById(
            "confirmAnnouncementCategory"
        );


    const confirmAnnouncementDescription =
        document.getElementById(
            "confirmAnnouncementDescription"
        );

    const confirmAnnouncementFacebook =
        document.getElementById(
            "confirmAnnouncementFacebook"
        );


    const cancelAnnouncementConfirmation =
        document.getElementById(
            "cancelAnnouncementConfirmation"
        );


    const confirmAnnouncementSubmission =
        document.getElementById(
            "confirmAnnouncementSubmission"
        );


    /* ============================================================
       FORM SUBMIT
       
       Stop the original POST temporarily and show the
       confirmation modal instead.
       ============================================================ */

    announcementForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            /* ----------------------------------------------------
               Get current form values
               ---------------------------------------------------- */

            const title =
                announcementForm
                    .querySelector('[name="title"]')
                    .value
                    .trim();


            const category =
                announcementForm
                    .querySelector('[name="category"]')
                    .value;


            const description =
                announcementForm
                    .querySelector('[name="description"]')
                    .value
                    .trim();

            const postToFacebook =
                announcementForm
                    .querySelector('[name="post_to_facebook"]')
                    .checked;


            /* ----------------------------------------------------
               Display values in confirmation modal
               ---------------------------------------------------- */

            confirmAnnouncementTitle.textContent =
                title || "—";


            confirmAnnouncementCategory.textContent =
                category || "—";


            confirmAnnouncementDescription.textContent =
                description || "—";

            confirmAnnouncementFacebook.textContent =
                postToFacebook ? "Yes (Will publish to Facebook)" : "No (Local only)";


            /* ----------------------------------------------------
               Show confirmation modal
               ---------------------------------------------------- */

            announcementConfirmModal.classList.add(
                "active"
            );

        }
    );


    /* ============================================================
       CANCEL CONFIRMATION
       
       Keeps all entered information in the form.
       ============================================================ */

    cancelAnnouncementConfirmation.addEventListener(
        "click",
        function () {

            announcementConfirmModal.classList.remove(
                "active"
            );

        }
    );


    /* ============================================================
       CONFIRM POST
       
       Native form.submit() bypasses the submit event,
       so the confirmation modal will NOT appear again.
       ============================================================ */

    confirmAnnouncementSubmission.addEventListener(
        "click",
        function () {

            announcementConfirmModal.classList.remove(
                "active"
            );


            announcementForm.submit();

        }
    );


    /* ============================================================
       CLICK OUTSIDE CONFIRMATION MODAL
       ============================================================ */

    announcementConfirmModal.addEventListener(
        "click",
        function (event) {

            if (
                event.target === announcementConfirmModal
            ) {

                announcementConfirmModal.classList.remove(
                    "active"
                );

            }

        }
    );


    /* ============================================================
       ESC KEY
       ============================================================ */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                announcementConfirmModal.classList.contains(
                    "active"
                )
            ) {

                announcementConfirmModal.classList.remove(
                    "active"
                );

            }

        }
    );

});

</script>

@endsection