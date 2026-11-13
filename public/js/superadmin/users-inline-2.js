document.addEventListener('DOMContentLoaded', function () {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');

    /*
     * IMPORTANT FIX:
     * Move both modal overlays directly under <body>.
     * This prevents transformed or positioned parent containers
     * from affecting the fixed modal's viewport positioning.
     */
    if (editModal) {
        document.body.appendChild(editModal);
    }

    if (addModal) {
        document.body.appendChild(addModal);
    }

    const editForm = document.getElementById('editUserForm');
    const addForm = document.getElementById('addUserForm');
    const openAddButton = document.getElementById('openAddUserModal');

    let previouslyFocusedElement = null;

    function openModal(modal) {
        if (!modal) {
            return;
        }

        previouslyFocusedElement = document.activeElement;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const firstInput = modal.querySelector(
            'input:not([type="hidden"]), select, button'
        );

        if (firstInput) {
            window.setTimeout(function () {
                firstInput.focus();
            }, 50);
        }
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        const anotherModalIsOpen = document.querySelector(
            '.user-management-modals.modal-overlay.is-open'
        );

        if (!anotherModalIsOpen) {
            document.body.style.overflow = '';

            if (
                previouslyFocusedElement &&
                typeof previouslyFocusedElement.focus === 'function'
            ) {
                previouslyFocusedElement.focus();
            }
        }
    }

    function openAddModal() {
        if (addForm) {
            addForm.reset();
        }

        openModal(addModal);
    }

    function openEditModal(button) {
        if (!button || !editForm) {
            return;
        }

        const userId = button.dataset.id;

        if (!userId) {
            return;
        }

        editForm.action =
            `/superadmin/users/${encodeURIComponent(userId)}`;

        document.getElementById('edit_name').value =
            button.dataset.name || '';

        document.getElementById('edit_email').value =
            button.dataset.email || '';

        document.getElementById('edit_username').value =
            button.dataset.username || '';

        document.getElementById('edit_role').value =
            button.dataset.role || 'citizen';

        document.getElementById('edit_confirm_password').value = '';

        openModal(editModal);
    }

    // Open Add User modal.
    if (openAddButton) {
        openAddButton.addEventListener('click', openAddModal);
    }

    // Open Edit User modal and populate its fields.
    document.querySelectorAll('.js-edit-user').forEach(function (button) {
        button.addEventListener('click', function () {
            openEditModal(button);
        });
    });

    // Close modal using its Close or Cancel button.
    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.dataset.closeModal;
            closeModal(document.getElementById(modalId));
        });
    });

    // Close modal by clicking the backdrop.
    [addModal, editModal].forEach(function (modal) {
        if (!modal) {
            return;
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Close the active modal with Escape.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        if (editModal && editModal.classList.contains('is-open')) {
            closeModal(editModal);
        } else if (addModal && addModal.classList.contains('is-open')) {
            closeModal(addModal);
        }
    });

    // Prevent repeated Add User submissions.
    if (addForm) {
        addForm.addEventListener('submit', function () {
            const button = document.getElementById('createUserButton');

            if (button && addForm.checkValidity()) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Creating...';
            }
        });
    }

    // Prevent repeated Edit User submissions.
    if (editForm) {
        editForm.addEventListener('submit', function () {
            const button = document.getElementById('saveEditUserButton');

            if (button && editForm.checkValidity()) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Saving...';
            }
        });
    }
});
