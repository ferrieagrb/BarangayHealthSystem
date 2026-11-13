    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }

    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }

    function openEditModal(id, name, email, username, role) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editUserForm');
        const resetForm = document.getElementById('resetPasswordForm');
        
        // Target standard admin route update path
        form.action = `/admin/users/${id}`;
        resetForm.action = `/admin/users/${id}/reset-password`;
        document.getElementById('reset_user_name').textContent = name;
        document.getElementById('reset_confirm_password').value = '';
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_confirm_password').value = '';
        const resetButton = document.getElementById('editResetPassword');
        resetButton.dataset.resetUser = id;
        resetButton.dataset.resetName = name;
        
        modal.style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function openResetModal(button) {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('resetPasswordForm').action = `/admin/users/${button.dataset.resetUser}/reset-password`;
        document.getElementById('reset_user_name').textContent = button.dataset.resetName;
        document.getElementById('reset_confirm_password').value = '';
        document.getElementById('resetModal').style.display = 'flex';
    }

    function closeResetModal() {
        document.getElementById('resetModal').style.display = 'none';
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('editResetPassword').focus();
    }

    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const addModal = document.getElementById('addModal');
        if (event.target === editModal) {
            closeEditModal();
        }
        if (event.target === addModal) {
            closeAddModal();
        }
    }
