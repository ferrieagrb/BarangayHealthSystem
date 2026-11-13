
document.addEventListener("DOMContentLoaded", function () {

    /* ============================================================
       ANNOUNCEMENT FORM & BUTTONS
       ============================================================ */

    const announcementForm =
        document.getElementById("announcementForm");

    const openModalBtn =
        document.getElementById("openModalBtn");


    /* ============================================================
       CONFIRMATION MODAL ELEMENTS
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
       OPEN MODAL ON BUTTON CLICK (Validates form first)
       ============================================================ */

    openModalBtn.addEventListener(
        "click",
        function () {

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
               Trigger browser validation if fields are empty
               ---------------------------------------------------- */

            if (!title || !category || !description) {
                announcementForm.reportValidity();
                return;
            }


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


            const photosWrap = document.getElementById("confirmAnnouncementPhotosWrap");
            const photos = document.getElementById("confirmAnnouncementPhotos");
            photos.querySelectorAll("img").forEach(function (img) {
                URL.revokeObjectURL(img.src);
            });
            photos.innerHTML = "";

            const files = document.getElementById("announcementImages").files;
            Array.from(files).forEach(function (file) {
                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);
                img.alt = file.name;
                img.style.cssText = "max-width:100%; max-height:220px; border-radius:8px; object-fit:contain;";
                photos.appendChild(img);
            });
            photosWrap.style.display = files.length ? "" : "none";

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
       
       Safely triggers the true submit action, allowing your 
       global loader script to run only now.
       ============================================================ */

    confirmAnnouncementSubmission.addEventListener(
        "click",
        function () {

            announcementConfirmModal.classList.remove(
                "active"
            );

            HTMLFormElement.prototype.submit.call(
                announcementForm
            );

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
