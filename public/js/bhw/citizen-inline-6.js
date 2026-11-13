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
