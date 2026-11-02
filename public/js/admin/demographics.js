// Called by the Google Maps script once it has loaded.
window.initDemographicsMap = () => {
    window.demographicsGoogleReady = true;
    if (window.startDemographicsMap) window.startDemographicsMap();
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('demographicsApp');
    if (!root) return;

    const data = window.demographicsMapData || { puroks: [], subgroups: [], routes: {} };
    const tabs = [...root.querySelectorAll('.demographics-tab')];
    const panels = [...root.querySelectorAll('.demographics-panel')];
    const mapElement = document.getElementById('demographicsMap');
    const statusElement = document.getElementById('mapStatus');
    const purokSelect = document.getElementById('mapPurok');
    const targetTypeSelect = document.getElementById('mapTargetType');
    const subgroupSelect = document.getElementById('mapSubgroup');
    const subgroupTargetField = document.getElementById('subgroupTargetField');
    const savePinButton = document.getElementById('savePin');
    const resetPinButton = document.getElementById('resetPin');
    const saveBoundaryButton = document.getElementById('saveBoundary');
    const deleteBoundaryButton = document.getElementById('deleteBoundary');
    const editBoundaryButton = document.getElementById('editBoundary');
    const finishBoundaryButton = document.getElementById('finishBoundary');
    const undoBoundaryButton = document.getElementById('undoBoundary');
    const cancelChangesButton = document.getElementById('cancelMapChanges');
    const boundaryVisibilityOptions = document.getElementById('boundaryVisibilityOptions');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const archiveDialog = document.getElementById('subgroupArchiveDialog');
    const archiveForm = document.getElementById('subgroupArchiveForm');
    const archiveTitle = document.getElementById('subgroupArchiveTitle');
    const archiveDescription = document.getElementById('subgroupArchiveDescription');
    const archiveScope = document.getElementById('subgroupArchiveScope');
    const archiveConfirmationName = document.getElementById('subgroupArchiveConfirmationName');
    const archiveConfirmationInput = document.getElementById('subgroupArchiveConfirmation');
    const archivePasswordInput = document.getElementById('subgroupArchivePassword');
    const archiveConfirmButton = document.getElementById('subgroupArchiveConfirm');
    const archiveError = document.getElementById('subgroupArchiveError');
    const savedPolygons = new Map();
    const savedMarkers = new Map();
    const boundaryVisibility = new Map();
    const defaultCenter = { lat: 14.0668, lng: 120.8531 };

    let activeArchiveCard = null;
    let archiveConfirmationValue = '';
    let archiveMode = 'citizens';
    let archiveSubmitting = false;
    let map;
    let mode = 'pin';
    let targetPin;
    let targetPinDirty = false;
    let draftLayer;
    let drawingLine;
    let drawingPoints = [];
    let boundaryDirty = false;
    let drawingFinished = false;

    function entityKey(type, id) {
        return `${type}-${id}`;
    }

    function findPurok(id) {
        return data.puroks.find((purok) => String(purok.id) === String(id));
    }

    function findSubgroup(id) {
        return data.subgroups.find((subgroup) => String(subgroup.id) === String(id));
    }

    function currentTarget() {
        if (targetTypeSelect.value === 'subgroup') {
            return findSubgroup(subgroupSelect.value);
        }
        return findPurok(purokSelect.value);
    }

    function currentTargetType() {
        return targetTypeSelect.value;
    }

    function currentTargetName() {
        const target = currentTarget();
        return target ? target.name : '';
    }

    function setStatus(message, tone = '') {
        statusElement.textContent = message;
        statusElement.classList.remove('error', 'success');
        if (tone) statusElement.classList.add(tone);
    }

    function setTab(name) {
        tabs.forEach((tab) => {
            const selected = tab.dataset.tab === name;
            tab.classList.toggle('active', selected);
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
        panels.forEach((panel) => {
            const selected = panel.dataset.panel === name;
            panel.classList.toggle('active', selected);
            panel.hidden = !selected;
        });
        if (name === 'map' && map) {
            window.setTimeout(() => {
                google.maps.event.trigger(map, 'resize');
                if (!currentTarget()) fitVisibleLayers();
            }, 80);
        }
    }

    tabs.forEach((tab) => tab.addEventListener('click', () => setTab(tab.dataset.tab)));
    root.querySelectorAll('[data-open-tab]').forEach((button) => {
        button.addEventListener('click', () => setTab(button.dataset.openTab));
    });
    root.querySelectorAll('.citizen-purge-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirmMessage)) {
                event.preventDefault();
            }
        });
    });

    function updateAssignmentCount(form) {
        const count = form.querySelectorAll('input[name="bhw_ids[]"]:checked').length;
        const countElement = form.querySelector('.assignment-count');
        if (countElement) countElement.textContent = count;
    }

    root.querySelectorAll('.bhw-search-input').forEach((input) => {
        input.addEventListener('input', () => {
            const checklist = input.closest('.bhw-selector, .bhw-assignment').querySelector('.bhw-checklist');
            const query = input.value.trim().toLocaleLowerCase();
            let visibleCount = 0;
            checklist.querySelectorAll('.bhw-option').forEach((option) => {
                const matches = option.dataset.bhwName.toLocaleLowerCase().includes(query);
                option.hidden = !matches;
                if (matches) visibleCount++;
            });
            const noResults = checklist.querySelector('.bhw-no-search-results');
            if (noResults) noResults.hidden = visibleCount > 0;
        });
    });

    root.querySelectorAll('.subgroup-create-form input[name="bhw_ids[]"], .subgroup-edit-form input[name="bhw_ids[]"]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => updateAssignmentCount(checkbox.closest('form')));
    });

    function updateArchiveConfirmButton() {
        archiveConfirmButton.disabled = archiveSubmitting
            || archiveConfirmationInput.value !== archiveConfirmationValue
            || !archivePasswordInput.value;
    }

    function clearArchiveError() {
        archiveError.hidden = true;
        archiveError.textContent = '';
    }

    function openArchiveDialog(card) {
        activeArchiveCard = card;
        archiveMode = Number(card.dataset.citizenCount) > 0 ? 'citizens' : 'subgroup';
        archiveConfirmationValue = card.dataset.confirmationName;
        archiveTitle.textContent = archiveMode === 'citizens' ? 'Archive citizens' : 'Archive subgroup';
        archiveDescription.textContent = archiveMode === 'citizens'
            ? `You are archiving ${card.dataset.citizenCount} citizen records in ${archiveConfirmationValue}.`
            : `There are no active citizens in ${archiveConfirmationValue}. Archiving now will archive the subgroup and its empty family records.`;
        archiveScope.textContent = archiveMode === 'citizens'
            ? 'Citizen profiles and linked citizen login accounts will be archived and can be restored. Family records and BHW accounts will remain unchanged.'
            : 'The subgroup and any empty family records will be archived and can be restored. BHW accounts and assignment records will remain unchanged.';
        archiveConfirmationName.textContent = archiveConfirmationValue;
        archiveForm.action = archiveMode === 'citizens'
            ? card.dataset.citizenArchiveAction
            : card.dataset.subgroupArchiveAction;
        archiveForm.reset();
        archivePasswordInput.type = 'password';
        archiveForm.querySelector('[data-toggle-password]').setAttribute('aria-pressed', 'false');
        archiveForm.querySelector('[data-toggle-password]').setAttribute('aria-label', 'Show password');
        archiveForm.querySelector('[data-toggle-password] i').className = 'bx bx-show';
        archiveSubmitting = false;
        archiveConfirmButton.textContent = 'Confirm Archive';
        clearArchiveError();
        updateArchiveConfirmButton();
        archiveDialog.showModal();
        archiveConfirmationInput.focus();
    }

    root.querySelectorAll('[data-open-archive]').forEach((button) => {
        button.addEventListener('click', () => openArchiveDialog(button.closest('[data-subgroup-card]')));
    });

    archiveForm.querySelectorAll('[data-close-archive]').forEach((button) => {
        button.addEventListener('click', () => archiveDialog.close());
    });

    archiveDialog.addEventListener('click', (event) => {
        if (event.target === archiveDialog) archiveDialog.close();
    });

    archiveForm.addEventListener('input', () => {
        clearArchiveError();
        updateArchiveConfirmButton();
    });

    archiveForm.querySelector('[data-toggle-password]').addEventListener('click', (event) => {
        const button = event.currentTarget;
        const showPassword = archivePasswordInput.type === 'password';
        archivePasswordInput.type = showPassword ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(showPassword));
        button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        button.querySelector('i').className = showPassword ? 'bx bx-hide' : 'bx bx-show';
    });

    archiveForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!activeArchiveCard || archiveConfirmButton.disabled) return;

        archiveSubmitting = true;
        archiveConfirmButton.disabled = true;
        archiveConfirmButton.textContent = 'Archiving...';
        clearArchiveError();

        try {
            const response = await fetch(archiveForm.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(archiveForm),
            });
            const result = await response.json();

            if (!response.ok) {
                const messages = Object.values(result.errors || {}).flat();
                throw new Error(messages.join(' ') || result.message || 'Unable to archive this subgroup.');
            }

            archiveDialog.close();
            window.location.reload();
        } catch (error) {
            archiveError.textContent = error.message || 'Unable to archive this subgroup. Please try again.';
            archiveError.hidden = false;
            archiveSubmitting = false;
            archiveConfirmButton.textContent = 'Confirm Archive';
            updateArchiveConfirmButton();
        }
    });

    archiveDialog.addEventListener('close', () => {
        archiveForm.reset();
        clearArchiveError();
        archiveSubmitting = false;
        updateArchiveConfirmButton();
    });

    const initialTab = root.dataset.initialTab;
    setTab(tabs.some((tab) => tab.dataset.tab === initialTab) ? initialTab : 'overview');

    function colorFor(type, target = currentTarget()) {
        const colors = type === 'purok'
            ? ['#2563eb', '#059669', '#7c3aed', '#0891b2', '#db2777', '#4f46e5', '#65a30d', '#dc2626']
            : ['#ea580c', '#a21caf', '#ca8a04', '#0d9488', '#e11d48', '#9333ea', '#c2410c', '#0284c7'];
        const id = Number(target?.id);
        const index = Number.isFinite(id) && id > 0 ? (id - 1) % colors.length : 0;
        return colors[index];
    }

    function polygonOptions(type, target, extra = {}) {
        const color = colorFor(type, target);
        return {
            strokeColor: color,
            fillColor: color,
            strokeOpacity: type === 'subgroup' ? 0.75 : 1,
            strokeWeight: type === 'subgroup' ? 2 : 3,
            fillOpacity: type === 'subgroup' ? 0.12 : 0.08,
            clickable: false,
            ...extra,
        };
    }

    function pinIcon(type) {
        return {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: type === 'purok' ? '#2563eb' : '#ea580c',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
        };
    }

    function ringFromGeoJson(geometry) {
        const ring = geometry?.coordinates?.[0] || [];
        return ring.map(([lng, lat]) => ({ lat, lng }));
    }

    function pathFromPolygon(polygon) {
        return polygon.getPath().getArray().map((point) => [point.lng(), point.lat()]);
    }

    function clearSavedLayers() {
        savedPolygons.forEach((polygon) => polygon.setMap(null));
        savedMarkers.forEach((marker) => marker.setMap(null));
        savedPolygons.clear();
        savedMarkers.clear();
    }

    function renderBoundaryVisibilityOptions() {
        boundaryVisibilityOptions.replaceChildren();
        const entries = [
            ...data.puroks.map((purok) => ({
                key: entityKey('purok', purok.id),
                name: `${purok.name} boundary`,
                hasBoundary: Boolean(purok.boundary),
                color: colorFor('purok', purok),
            })),
            ...data.subgroups.map((subgroup) => ({
                key: entityKey('subgroup', subgroup.id),
                name: `${subgroup.name} subgroup`,
                hasBoundary: Boolean(subgroup.boundary),
                color: colorFor('subgroup', subgroup),
            })),
        ].filter((entry) => entry.hasBoundary);

        if (!entries.length) {
            const empty = document.createElement('span');
            empty.className = 'empty-state';
            empty.textContent = 'No saved boundaries yet.';
            boundaryVisibilityOptions.append(empty);
            return;
        }

        entries.forEach((entry) => {
            if (!boundaryVisibility.has(entry.key)) boundaryVisibility.set(entry.key, true);
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = boundaryVisibility.get(entry.key);
            checkbox.addEventListener('change', () => {
                boundaryVisibility.set(entry.key, checkbox.checked);
                renderSavedLayers();
            });
            const swatch = document.createElement('i');
            swatch.className = 'boundary-color-swatch';
            swatch.setAttribute('aria-hidden', 'true');
            swatch.style.setProperty('--boundary-color', entry.color);
            const text = document.createElement('span');
            text.textContent = entry.name;
            label.append(checkbox, swatch, text);
            boundaryVisibilityOptions.append(label);
        });
    }

    function hasCoordinates(target) {
        return target?.latitude !== null && target?.latitude !== undefined
            && target?.longitude !== null && target?.longitude !== undefined;
    }

    function renderSavedLayers() {
        if (!map) return;
        clearSavedLayers();
        renderBoundaryVisibilityOptions();
        const selectedPurokId = purokSelect.value;
        const visiblePuroks = selectedPurokId
            ? data.puroks.filter((purok) => String(purok.id) === selectedPurokId)
            : data.puroks;
        const visibleSubgroups = selectedPurokId
            ? data.subgroups.filter((subgroup) => String(subgroup.purok_id) === selectedPurokId)
            : data.subgroups;

        [['purok', visiblePuroks, 'boundary'], ['subgroup', visibleSubgroups, 'subgroup boundary']].forEach(([type, items, label]) => {
            items.forEach((item) => {
                const key = entityKey(type, item.id);
                if (item.boundary) {
                    savedPolygons.set(key, new google.maps.Polygon({
                        ...polygonOptions(type, item),
                        paths: ringFromGeoJson(item.boundary),
                        map: boundaryVisibility.get(key) === false ? null : map,
                    }));
                }
                if (hasCoordinates(item)) {
                    savedMarkers.set(key, new google.maps.Marker({
                        position: { lat: Number(item.latitude), lng: Number(item.longitude) },
                        map,
                        icon: pinIcon(type),
                        title: `${item.name} ${type === 'purok' ? 'pin' : 'subgroup pin'}`,
                    }));
                }
            });
        });
    }

    function clearDraftLayer() {
        if (drawingLine) drawingLine.setMap(null);
        if (draftLayer) draftLayer.setMap(null);
        drawingLine = null;
        draftLayer = null;
        drawingPoints = [];
        boundaryDirty = false;
        drawingFinished = false;
        saveBoundaryButton.disabled = true;
        finishBoundaryButton.disabled = true;
        undoBoundaryButton.disabled = true;
        deleteBoundaryButton.disabled = !currentTarget()?.boundary;
        cancelChangesButton.disabled = !targetPinDirty;
    }

    function removeTargetPin() {
        if (targetPin) targetPin.setMap(null);
        targetPin = null;
    }

    function createTargetPin(position, type) {
        const pin = new google.maps.Marker({
            position,
            map,
            icon: pinIcon(type),
            draggable: true,
            zIndex: 10,
        });
        pin.addListener('dragend', () => {
            targetPinDirty = true;
            savePinButton.disabled = false;
            cancelChangesButton.disabled = false;
            const moved = pin.getPosition();
            setStatus(`Pin for ${currentTargetName()} moved to ${moved.lat().toFixed(6)}, ${moved.lng().toFixed(6)}. Save to keep it.`);
        });
        return pin;
    }

    function resetTargetPin() {
        removeTargetPin();
        const target = currentTarget();
        if (hasCoordinates(target)) {
            targetPin = createTargetPin({ lat: Number(target.latitude), lng: Number(target.longitude) }, currentTargetType());
        }
        targetPinDirty = false;
        savePinButton.disabled = true;
        resetPinButton.disabled = !target;
        cancelChangesButton.disabled = false;
    }

    function updateBoundaryButtons() {
        deleteBoundaryButton.textContent = currentTargetType() === 'purok'
            ? 'Delete Purok boundary'
            : 'Delete subgroup boundary';
        saveBoundaryButton.disabled = !boundaryDirty || !drawingFinished;
        finishBoundaryButton.disabled = mode !== 'draw' || drawingFinished;
        undoBoundaryButton.disabled = mode !== 'draw' || drawingPoints.length === 0 || drawingFinished;
        deleteBoundaryButton.disabled = !currentTarget()?.boundary || boundaryDirty;
        cancelChangesButton.disabled = !boundaryDirty && !targetPinDirty;
    }

    function extendWithPolygon(bounds, polygon) {
        polygon.getPath().forEach((point) => bounds.extend(point));
    }

    function fitVisibleLayers() {
        if (!savedPolygons.size && !savedMarkers.size) return;
        const bounds = new google.maps.LatLngBounds();
        savedPolygons.forEach((polygon) => extendWithPolygon(bounds, polygon));
        savedMarkers.forEach((marker) => bounds.extend(marker.getPosition()));
        map.fitBounds(bounds, 40);
        google.maps.event.addListenerOnce(map, 'idle', () => {
            if (map.getZoom() > 16) map.setZoom(16);
        });
    }

    function refreshTarget() {
        if (!map) return;
        const selectedPurokId = purokSelect.value;
        const filtered = data.subgroups.filter((subgroup) => String(subgroup.purok_id) === selectedPurokId);
        const previousSubgroupId = subgroupSelect.value;
        subgroupSelect.innerHTML = '<option value="">Select a subgroup</option>';
        filtered.forEach((subgroup) => {
            const option = document.createElement('option');
            option.value = subgroup.id;
            option.textContent = subgroup.name;
            subgroupSelect.append(option);
        });
        if (filtered.some((subgroup) => String(subgroup.id) === previousSubgroupId)) {
            subgroupSelect.value = previousSubgroupId;
        }
        subgroupTargetField.hidden = targetTypeSelect.value !== 'subgroup';
        clearDraftLayer();
        removeTargetPin();
        targetPinDirty = false;
        renderSavedLayers();
        const target = currentTarget();
        if (!target) {
            savePinButton.disabled = true;
            resetPinButton.disabled = true;
            saveBoundaryButton.disabled = true;
            deleteBoundaryButton.disabled = true;
            cancelChangesButton.disabled = true;
            setStatus('Choose a Purok and map target to view its saved pin and boundary.');
            fitVisibleLayers();
            return;
        }

        resetTargetPin();
        const key = entityKey(currentTargetType(), target.id);
        const polygon = savedPolygons.get(key);
        if (polygon && polygon.getMap()) {
            const bounds = new google.maps.LatLngBounds();
            extendWithPolygon(bounds, polygon);
            map.fitBounds(bounds, 40);
        } else if (hasCoordinates(target)) {
            map.setCenter({ lat: Number(target.latitude), lng: Number(target.longitude) });
            map.setZoom(16);
        }
        setStatus(`${target.name} selected. ${target.boundary ? 'Saved boundary is shown.' : 'No boundary saved yet.'} ${hasCoordinates(target) ? 'Pin is saved.' : 'No pin saved yet.'}`);
        updateBoundaryButtons();
    }

    function mapRoute(kind, id) {
        const type = currentTargetType();
        const template = type === 'purok' ? data.routes[`purok${kind}`] : data.routes[`subgroup${kind}`];
        return template.replace('__ID__', encodeURIComponent(id));
    }

    async function sendJson(url, body, method = 'PUT') {
        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body === undefined ? undefined : JSON.stringify(body),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = result.message
                || Object.values(result.errors || {}).flat().join(' ')
                || 'The change could not be saved.';
            throw new Error(message);
        }
        return result;
    }

    function geometryFromDraft() {
        if (!draftLayer) return null;
        const ring = pathFromPolygon(draftLayer);
        if (ring.length < 3) return null;
        ring.push([...ring[0]]);
        const area = ring.reduce((sum, point, index) => {
            if (index === 0) return sum;
            const previous = ring[index - 1];
            return sum + previous[0] * point[1] - point[0] * previous[1];
        }, 0);
        if (Math.abs(area) < 1e-10) return null;
        return { type: 'Polygon', coordinates: [ring] };
    }

    function pointInRing(point, ring) {
        const [x, y] = point;
        let inside = false;
        for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            const [xi, yi] = ring[i];
            const [xj, yj] = ring[j];
            const intersects = ((yi > y) !== (yj > y))
                && (x < ((xj - xi) * (y - yi)) / (yj - yi) + xi);
            if (intersects) inside = !inside;
        }
        return inside;
    }

    function boundaryWarnings(geometry, subgroup) {
        const warnings = [];
        const ring = geometry.coordinates[0];
        const parent = findPurok(subgroup.purok_id);
        const parentRing = parent?.boundary?.coordinates?.[0];
        if (parentRing && ring.slice(0, -1).some((point) => !pointInRing(point, parentRing))) {
            warnings.push('Some subgroup boundary points extend beyond the parent Purok.');
        }

        const overlaps = data.subgroups.some((other) => {
            if (String(other.id) === String(subgroup.id)
                || String(other.purok_id) !== String(subgroup.purok_id)
                || !other.boundary?.coordinates?.[0]) return false;
            const otherRing = other.boundary.coordinates[0];
            return ring.slice(0, -1).some((point) => pointInRing(point, otherRing))
                || otherRing.slice(0, -1).some((point) => pointInRing(point, ring));
        });
        if (overlaps) warnings.push('This subgroup boundary overlaps another saved subgroup boundary.');
        return warnings;
    }

    function drawDraft(fillOpacity) {
        const type = currentTargetType();
        const target = currentTarget();
        const color = colorFor(type, target);
        if (drawingLine) drawingLine.setMap(null);
        if (draftLayer) draftLayer.setMap(null);
        drawingLine = null;
        draftLayer = null;
        if (drawingPoints.length >= 3) {
            draftLayer = new google.maps.Polygon({
                ...polygonOptions(type, target, { strokeWeight: 3, fillOpacity }),
                paths: drawingPoints,
                map,
            });
        } else if (drawingPoints.length) {
            drawingLine = new google.maps.Polyline({
                path: drawingPoints,
                strokeColor: color,
                strokeWeight: 3,
                clickable: false,
                map,
            });
        }
    }

    function initializeMap() {
        if (!mapElement || typeof google === 'undefined' || !google.maps) {
            setStatus('Google Maps could not be loaded. Check your connection and reload this page.', 'error');
            return;
        }

        map = new google.maps.Map(mapElement, {
            center: defaultCenter,
            zoom: 15,
            mapTypeId: 'roadmap',
            streetViewControl: false,
            fullscreenControl: true,
            clickableIcons: false,
            disableDoubleClickZoom: true,
        });
        renderSavedLayers();

        map.addListener('click', (event) => {
            const latLng = event.latLng;
            if (!latLng) return;
            if (mode === 'draw') {
                if (drawingFinished) return;
                drawingPoints.push({ lat: latLng.lat(), lng: latLng.lng() });
                drawDraft(0.16);
                boundaryDirty = drawingPoints.length >= 3;
                updateBoundaryButtons();
                setStatus(`${drawingPoints.length} boundary point${drawingPoints.length === 1 ? '' : 's'} added for ${currentTargetName()}. Finish drawing to preview and save.`);
                return;
            }
            if (mode !== 'pin' || !currentTarget()) return;
            if (targetPin) targetPin.setPosition(latLng);
            else targetPin = createTargetPin(latLng, currentTargetType());
            targetPinDirty = true;
            savePinButton.disabled = false;
            cancelChangesButton.disabled = false;
            setStatus(`Pin selected for ${currentTargetName()} at ${latLng.lat().toFixed(6)}, ${latLng.lng().toFixed(6)}. Save to keep it.`);
        });

        refreshTarget();
    }

    window.startDemographicsMap = initializeMap;
    if (window.demographicsGoogleReady) initializeMap();

    function markBoundaryEdited() {
        if (mode !== 'edit') return;
        boundaryDirty = true;
        drawingFinished = true;
        updateBoundaryButtons();
        setStatus(`Boundary for ${currentTargetName()} edited. Save to apply the changes.`);
    }

    function chooseMode(nextMode) {
        const target = currentTarget();
        if (!target) {
            setStatus('Select a Purok and target before editing the map.', 'error');
            return;
        }
        if ((boundaryDirty || targetPinDirty) && !window.confirm('Discard unsaved map changes?')) return;
        clearDraftLayer();
        removeTargetPin();
        renderSavedLayers();
        mode = nextMode;

        const activeButton = nextMode === 'pin'
            ? document.getElementById('pinMode')
            : nextMode === 'edit'
                ? editBoundaryButton
                : nextMode === 'draw'
                    ? currentTargetType() === 'purok'
                        ? document.getElementById('drawPurokBoundary')
                        : document.getElementById('drawSubgroupBoundary')
                    : null;
        root.querySelectorAll('.map-mode-controls .button-group:first-child .btn').forEach((button) => {
            const selected = button === activeButton;
            button.classList.toggle('active', selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });

        if (nextMode === 'pin') {
            resetTargetPin();
            setStatus(`Pin mode for ${target.name}. Click the map or drag the marker, then save.`);
            return;
        }
        if (nextMode === 'draw') {
            setStatus(`Draw mode for ${target.name}. Click the map to add vertices, then finish the drawing.`);
            updateBoundaryButtons();
            return;
        }
        if (nextMode === 'edit') {
            if (!target.boundary) {
                mode = 'pin';
                setStatus(`No saved boundary exists for ${target.name}. Draw a boundary first.`, 'error');
                resetTargetPin();
                return;
            }
            const key = entityKey(currentTargetType(), target.id);
            savedPolygons.get(key)?.setMap(null);
            draftLayer = new google.maps.Polygon({
                ...polygonOptions(currentTargetType(), target, { strokeWeight: 3, fillOpacity: 0.2 }),
                paths: ringFromGeoJson(target.boundary),
                editable: true,
                clickable: true,
                map,
            });
            const path = draftLayer.getPath();
            ['set_at', 'insert_at', 'remove_at'].forEach((eventName) => {
                google.maps.event.addListener(path, eventName, markBoundaryEdited);
            });
            boundaryDirty = false;
            drawingFinished = true;
            deleteBoundaryButton.disabled = true;
            cancelChangesButton.disabled = false;
            setStatus(`Edit mode for ${target.name}. Drag vertices or drag a midpoint to add one. Save when finished.`);
            updateBoundaryButtons();
        }
    }

    document.getElementById('pinMode').addEventListener('click', () => chooseMode('pin'));
    document.getElementById('drawPurokBoundary').addEventListener('click', () => {
        targetTypeSelect.value = 'purok';
        refreshTarget();
        chooseMode('draw');
    });
    document.getElementById('drawSubgroupBoundary').addEventListener('click', () => {
        targetTypeSelect.value = 'subgroup';
        refreshTarget();
        chooseMode('draw');
    });
    editBoundaryButton.addEventListener('click', () => chooseMode('edit'));

    finishBoundaryButton.addEventListener('click', () => {
        if (drawingPoints.length < 3) {
            setStatus('Add at least three points before finishing the boundary.', 'error');
            return;
        }
        drawDraft(0.18);
        drawingFinished = true;
        boundaryDirty = true;
        updateBoundaryButtons();
        setStatus(`Boundary preview ready for ${currentTargetName()}. Save boundary to replace the existing saved shape.`);
    });

    undoBoundaryButton.addEventListener('click', () => {
        if (mode !== 'draw' || drawingFinished || drawingPoints.length === 0) return;
        drawingPoints.pop();
        drawDraft(0.16);
        boundaryDirty = drawingPoints.length >= 3;
        updateBoundaryButtons();
        setStatus(`${drawingPoints.length} unsaved boundary points remain.`);
    });

    cancelChangesButton.addEventListener('click', () => {
        if ((boundaryDirty || targetPinDirty) && !window.confirm('Discard unsaved map changes?')) return;
        clearDraftLayer();
        mode = 'pin';
        removeTargetPin();
        renderSavedLayers();
        resetTargetPin();
        setStatus(`Unsaved changes discarded. ${currentTargetName()} saved map data restored.`);
    });

    resetPinButton.addEventListener('click', () => {
        if (!currentTarget()) return;
        removeTargetPin();
        targetPinDirty = true;
        savePinButton.disabled = false;
        cancelChangesButton.disabled = false;
        setStatus(`Pin for ${currentTargetName()} will be removed when you save.`);
    });

    savePinButton.addEventListener('click', async () => {
        const target = currentTarget();
        if (!target || !targetPinDirty) return;
        const position = targetPin?.getPosition();
        savePinButton.disabled = true;
        setStatus(`Saving pin for ${target.name}...`);
        try {
            await sendJson(mapRoute('Pin', target.id), {
                latitude: position ? position.lat() : null,
                longitude: position ? position.lng() : null,
            });
            target.latitude = position ? position.lat() : null;
            target.longitude = position ? position.lng() : null;
            targetPinDirty = false;
            renderSavedLayers();
            refreshTarget();
            setStatus(`Pin for ${target.name} saved.`, 'success');
        } catch (error) {
            savePinButton.disabled = false;
            setStatus(error.message, 'error');
        }
    });

    saveBoundaryButton.addEventListener('click', async () => {
        const target = currentTarget();
        if (!target || !boundaryDirty || !drawingFinished) return;
        const geometry = geometryFromDraft();
        if (!geometry) {
            setStatus('Boundary is invalid. Draw a closed polygon with at least three non-collinear points.', 'error');
            return;
        }
        const warnings = currentTargetType() === 'subgroup' ? boundaryWarnings(geometry, target) : [];
        if (warnings.length && !window.confirm(`${warnings.join('\n')}\n\nSave this boundary anyway?`)) return;
        saveBoundaryButton.disabled = true;
        setStatus(`Saving boundary for ${target.name}...`);
        try {
            await sendJson(mapRoute('Boundary', target.id), { boundary: geometry });
            target.boundary = geometry;
            clearDraftLayer();
            mode = 'pin';
            renderSavedLayers();
            refreshTarget();
            setStatus(`Boundary for ${target.name} saved.`, 'success');
        } catch (error) {
            saveBoundaryButton.disabled = false;
            setStatus(error.message, 'error');
        }
    });

    deleteBoundaryButton.addEventListener('click', async () => {
        const target = currentTarget();
        if (!target?.boundary || !window.confirm(`Delete the saved ${currentTargetType()} boundary for ${target.name}? This cannot be undone.`)) return;
        deleteBoundaryButton.disabled = true;
        try {
            await sendJson(mapRoute('BoundaryDelete', target.id), undefined, 'DELETE');
            target.boundary = null;
            clearDraftLayer();
            renderSavedLayers();
            refreshTarget();
            setStatus(`${currentTargetType() === 'purok' ? 'Purok' : 'Subgroup'} boundary for ${target.name} deleted.`, 'success');
        } catch (error) {
            deleteBoundaryButton.disabled = false;
            setStatus(error.message, 'error');
        }
    });

    [purokSelect, targetTypeSelect, subgroupSelect].forEach((select) => {
        select.addEventListener('change', () => {
            if ((boundaryDirty || targetPinDirty) && !window.confirm('Discard unsaved map changes?')) {
                refreshTarget();
                return;
            }
            mode = 'pin';
            refreshTarget();
        });
    });

    root.querySelectorAll('.select-all-bhws').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            const visibleOptions = form.querySelectorAll('.bhw-option:not([hidden]) input[name="bhw_ids[]"]');
            visibleOptions.forEach((checkbox) => { checkbox.checked = true; });
            updateAssignmentCount(form);
        });
    });
    root.querySelectorAll('.clear-bhws').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            form.querySelectorAll('input[name="bhw_ids[]"]').forEach((checkbox) => { checkbox.checked = false; });
            updateAssignmentCount(form);
        });
    });

    root.querySelectorAll('.subgroup-create-form, .subgroup-edit-form').forEach(updateAssignmentCount);

    const subgroupSearch = document.getElementById('subgroupSearch');
    const subgroupPurokFilter = document.getElementById('subgroupPurokFilter');
    const subgroupCards = [...root.querySelectorAll('[data-subgroup-card]')];
    const noSubgroupResults = root.querySelector('.subgroup-no-results');

    function filterSubgroups() {
        const query = subgroupSearch.value.trim().toLocaleLowerCase();
        const purokId = subgroupPurokFilter.value;
        let visibleCount = 0;

        subgroupCards.forEach((card) => {
            const bhwNames = [...card.querySelectorAll('.subgroup-edit-form .bhw-option input:checked')]
                .map((checkbox) => checkbox.closest('.bhw-option').dataset.bhwName)
                .join(' ');
            const subgroupForm = card.querySelector('.subgroup-edit-form');
            const selectedPurok = subgroupForm.querySelector('[name="purok_id"]');
            const currentPurokId = selectedPurok.value;
            const searchableText = [
                subgroupForm.querySelector('[name="name"]').value,
                selectedPurok.selectedOptions[0]?.textContent || '',
                card.querySelector('[name="description"]')?.value || '',
                bhwNames,
            ].join(' ').toLocaleLowerCase();
            const matches = (!query || searchableText.includes(query))
                && (!purokId || currentPurokId === purokId);

            card.hidden = !matches;
            if (matches) visibleCount++;
        });

        noSubgroupResults.hidden = visibleCount > 0 || subgroupCards.length === 0;
    }

    subgroupSearch.addEventListener('input', filterSubgroups);
    subgroupPurokFilter.addEventListener('change', filterSubgroups);
    root.querySelectorAll('.subgroup-edit-form [name="name"], .subgroup-edit-form [name="description"], .subgroup-edit-form [name="purok_id"]').forEach((input) => {
        input.addEventListener('input', filterSubgroups);
        input.addEventListener('change', filterSubgroups);
    });

    root.querySelectorAll('.subgroup-edit-form input[name="bhw_ids[]"]').forEach((checkbox) => {
        checkbox.addEventListener('change', filterSubgroups);
    });
    filterSubgroups();
});
