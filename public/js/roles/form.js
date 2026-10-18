document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('role-form');
    if (!form) return;

    const profiles = [...form.querySelectorAll('.role-profile')];
    const permissions = [...form.querySelectorAll('.role-permission')];
    const nameInput = form.querySelector('#role-name');
    const previewName = form.querySelector('[data-role-preview-name]');
    const totalPermissions = form.querySelector('[data-total-permissions]');
    const selectedProfileLabel = form.querySelector('[data-selected-profile]');

    const updateGroupCount = (group) => {
        const checkboxes = [...group.querySelectorAll('.role-permission')];
        const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
        const counter = group.querySelector('[data-group-count]');
        const toggle = group.querySelector('[data-group-toggle]');

        if (counter) counter.textContent = `${selected}/${checkboxes.length}`;
        if (toggle) toggle.textContent = selected === checkboxes.length ? 'Kosongkan' : 'Pilih semua';
    };

    const updateSummary = () => {
        const selected = permissions.filter((checkbox) => checkbox.checked).length;
        const selectedProfile = profiles.find((profile) => profile.checked);
        const profileName = selectedProfile?.closest('.role-profile-card')?.querySelector('.role-profile-copy strong')?.textContent.trim();

        if (totalPermissions) totalPermissions.textContent = selected;
        if (selectedProfileLabel) selectedProfileLabel.textContent = profileName || 'Atur sendiri';
        form.querySelectorAll('[data-permission-group]').forEach(updateGroupCount);
    };

    profiles.forEach((profile) => {
        profile.addEventListener('change', () => {
            if (profile.value !== 'custom') {
                const allowed = new Set(JSON.parse(profile.dataset.permissions || '[]'));
                permissions.forEach((checkbox) => {
                    checkbox.checked = allowed.has(checkbox.value);
                });
            }
            updateSummary();
        });
    });

    permissions.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const customProfile = form.querySelector('#profile-custom');
            if (customProfile) customProfile.checked = true;
            updateSummary();
        });
    });

    form.querySelectorAll('[data-group-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const group = button.closest('[data-permission-group]');
            const checkboxes = [...group.querySelectorAll('.role-permission')];
            const shouldSelect = checkboxes.some((checkbox) => !checkbox.checked);

            checkboxes.forEach((checkbox) => {
                checkbox.checked = shouldSelect;
            });

            const customProfile = form.querySelector('#profile-custom');
            if (customProfile) customProfile.checked = true;
            updateSummary();
        });
    });

    nameInput?.addEventListener('input', () => {
        if (previewName) previewName.textContent = nameInput.value.trim() || 'Nama role';
    });

    updateSummary();
});
