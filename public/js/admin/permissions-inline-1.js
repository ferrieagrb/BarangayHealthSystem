    (function () {
        let activeModal = null;
        let previousFocus = null;

       
        let pendingPermissionForm = null;
        let confirmationPreviousFocus = null;

        const confirmationModal = document.getElementById(
            'permission-confirmation-modal'
        );

        const confirmationBhwName = document.getElementById(
            'confirmation-bhw-name'
        );

        const confirmationBhwEmail = document.getElementById(
            'confirmation-bhw-email'
        );

        const confirmationList = document.getElementById(
            'permission-confirmation-list'
        );

        const confirmButton = document.getElementById(
            'permission-confirm-button'
        );

        const backButton = document.getElementById(
            'permission-back-button'
        );

        const accessLabels = {
            none: 'No Access',
            read: 'Read Only',
            write: 'Read / Write'
        };

       
        function openPermissionConfirmation(form) {
            if (!form || !confirmationModal) {
                return;
            }

            const parentModal = form.closest('.permission-modal');

            if (!parentModal) {
                return;
            }

            const nameElement = parentModal.querySelector(
                '.bhw-info strong'
            );

            const emailElement = parentModal.querySelector(
                '.bhw-info span'
            );

            confirmationBhwName.textContent = nameElement
                ? nameElement.textContent.trim()
                : 'BHW User';

            confirmationBhwEmail.textContent = emailElement
                ? emailElement.textContent.trim()
                : '';

           
            confirmationList.replaceChildren();

            const categories = form.querySelectorAll(
                '.permission-category'
            );

            categories.forEach(function (category) {
                const titleElement = category.querySelector(
                    '.permission-category-title'
                );

                const selectedRadio = category.querySelector(
                    'input[type="radio"]:checked'
                );

                const featureName = titleElement
                    ? titleElement.textContent.trim()
                    : 'Unnamed Feature';

                const accessValue = selectedRadio
                    ? selectedRadio.value
                    : 'none';

                const accessLabel = accessLabels[accessValue]
                    || 'No Access';

                const row = document.createElement('tr');

                const featureCell = document.createElement('td');
                featureCell.textContent = featureName;

                const accessCell = document.createElement('td');

                const accessBadge = document.createElement('span');

                accessBadge.className = 'permission-access-badge';

                if (accessValue === 'read') {
                    accessBadge.classList.add(
                        'permission-access-read'
                    );
                } else if (accessValue === 'write') {
                    accessBadge.classList.add(
                        'permission-access-write'
                    );
                } else {
                    accessBadge.classList.add(
                        'permission-access-none'
                    );
                }

                accessBadge.textContent = accessLabel;

                accessCell.appendChild(accessBadge);

                row.appendChild(featureCell);
                row.appendChild(accessCell);

                confirmationList.appendChild(row);
            });

            pendingPermissionForm = form;

            confirmationPreviousFocus = document.activeElement;

            confirmationModal.classList.add('is-open');

            confirmationModal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.classList.add(
                'permission-modal-open'
            );

            confirmButton.disabled = false;
            confirmButton.textContent = 'Confirm & Save';

            confirmButton.focus();
        }

       
        function closePermissionConfirmation() {
            if (!confirmationModal) {
                return;
            }

            confirmationModal.classList.remove('is-open');

            confirmationModal.setAttribute(
                'aria-hidden',
                'true'
            );

            pendingPermissionForm = null;

            
            if (activeModal) {
                document.body.classList.add(
                    'permission-modal-open'
                );
            } else {
                document.body.classList.remove(
                    'permission-modal-open'
                );
            }

            if (
                confirmationPreviousFocus &&
                document.contains(confirmationPreviousFocus)
            ) {
                confirmationPreviousFocus.focus();
            }

            confirmationPreviousFocus = null;
        }

       
        document.querySelectorAll('.permissions-form').forEach(
            function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    openPermissionConfirmation(form);
                });
            }
        );

        
        backButton.addEventListener('click', function () {
            closePermissionConfirmation();
        });

        
        confirmButton.addEventListener('click', function () {
            if (!pendingPermissionForm) {
                return;
            }

            const formToSubmit = pendingPermissionForm;

            
            confirmButton.disabled = true;
            confirmButton.textContent = 'Saving...';

            
            confirmationModal.classList.remove('is-open');

            confirmationModal.setAttribute(
                'aria-hidden',
                'true'
            );

            pendingPermissionForm = null;

            
            formToSubmit.submit();
        });

        
        window.openPermissionModal = function (modalId) {
            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

            if (activeModal) {
                window.closePermissionModal(activeModal.id);
            }

            previousFocus = document.activeElement;
            activeModal = modal;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('permission-modal-open');

            const firstRadio = modal.querySelector(
                '.permission-option input[type="radio"]:checked'
            );

            const firstAvailableRadio = modal.querySelector(
                '.permission-option input[type="radio"]'
            );

            const closeButton = modal.querySelector(
                '.close-modal-button'
            );

            if (firstRadio) {
                firstRadio.focus();
            } else if (firstAvailableRadio) {
                firstAvailableRadio.focus();
            } else if (closeButton) {
                closeButton.focus();
            }
        };

        
        window.closePermissionModal = function (modalId) {
            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

           
            if (
                confirmationModal &&
                confirmationModal.classList.contains('is-open')
            ) {
                return;
            }

            modal.classList.remove('is-open');

            modal.setAttribute('aria-hidden', 'true');

            if (activeModal === modal) {
                activeModal = null;

                document.body.classList.remove(
                    'permission-modal-open'
                );

                if (
                    previousFocus &&
                    document.contains(previousFocus)
                ) {
                    previousFocus.focus();
                }

                previousFocus = null;
            }
        };

        
        window.handleModalBackdrop = function (event, modalId) {
            if (event.target === event.currentTarget) {
                window.closePermissionModal(modalId);
            }
        };

        
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (
                confirmationModal &&
                confirmationModal.classList.contains('is-open')
            ) {
                closePermissionConfirmation();
                return;
            }

            if (activeModal) {
                window.closePermissionModal(activeModal.id);
            }
        });

    })();
