document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('demographicsApp');
    if (!root || !window.demographicsMapData) return;

    const data = window.demographicsMapData;
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
    const pinLayers = L.layerGroup();
    const savedBoundaryLayers = L.layerGroup();
    const savedLayerByKey = new Map();
    const markerByKey = new Map();
    const boundaryVisibility = new Map();

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
                map.invalidateSize();
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
    const initialTab = root.dataset.initialTab;
    setTab(tabs.some((tab) => tab.dataset.tab === initialTab) ? initialTab : 'overview');

    function colorFor(type, target = currentTarget()) {
        const colors = type === 'purok'
            ? ['#2563eb', '#059669', '#7c3aed', '#0891b2', '#db2777', '#4f46e5', '#65a30d', '#dc2626']
            : ['#ea580c', '#a21caf', '#ca8a04', '#0d9488', '#e11d48', '#9333ea', '#c2410c', '#0284c7'];
        const id = Number(target?.id);
        const index = Number.isFinite(id) && id > 0 ? (id - 1) % colors.length : 0;
        const color = colors[index];

        return {
            color,
            fillColor: color,
            ...(type === 'subgroup' ? { dashArray: '6 5' } : {}),
        };
    }

    function addTooltip(layer, text) {
        const label = document.createElement('span');
        label.textContent = text;
        layer.bindTooltip(label);
    }

    function createPinIcon(type) {
        return L.divIcon({
            className: 'demographics-pin-icon',
            html: `<span class="demographics-pin ${type === 'purok' ? 'purok-pin' : 'subgroup-pin'}"></span>`,
            iconSize: [20, 24],
            iconAnchor: [10, 22],
        });
    }

    function clearSavedLayers() {
        savedBoundaryLayers.clearLayers();
        pinLayers.clearLayers();
        savedLayerByKey.clear();
        markerByKey.clear();
    }

    function renderBoundaryVisibilityOptions() {
        boundaryVisibilityOptions.replaceChildren();
        const entries = [
            ...data.puroks.map((purok) => ({
                key: entityKey('purok', purok.id),
                name: `${purok.name} boundary`,
                hasBoundary: Boolean(purok.boundary),
                color: colorFor('purok', purok).color,
            })),
            ...data.subgroups.map((subgroup) => ({
                key: entityKey('subgroup', subgroup.id),
                name: `${subgroup.name} subgroup`,
                hasBoundary: Boolean(subgroup.boundary),
                color: colorFor('subgroup', subgroup).color,
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

        visiblePuroks.forEach((purok) => {
            const key = entityKey('purok', purok.id);
            if (purok.boundary) {
                const layer = L.geoJSON(purok.boundary, {
                    style: { ...colorFor('purok', purok), weight: 3, fillOpacity: 0.08 },
                });
                if (boundaryVisibility.get(key) !== false) layer.addTo(savedBoundaryLayers);
                addTooltip(layer, `${purok.name} boundary`);
                savedLayerByKey.set(key, layer);
            }
            if (purok.latitude !== null && purok.longitude !== null) {
                const marker = L.marker([purok.latitude, purok.longitude], {
                    icon: createPinIcon('purok'),
                }).addTo(pinLayers);
                addTooltip(marker, `${purok.name} pin`);
                markerByKey.set(key, marker);
            }
        });

        visibleSubgroups.forEach((subgroup) => {
            const key = entityKey('subgroup', subgroup.id);
            if (subgroup.boundary) {
                const layer = L.geoJSON(subgroup.boundary, {
                    style: { ...colorFor('subgroup', subgroup), weight: 2, fillOpacity: 0.12 },
                });
                if (boundaryVisibility.get(key) !== false) layer.addTo(savedBoundaryLayers);
                addTooltip(layer, `${subgroup.name} subgroup boundary`);
                savedLayerByKey.set(key, layer);
            }
            if (subgroup.latitude !== null && subgroup.longitude !== null) {
                const marker = L.marker([subgroup.latitude, subgroup.longitude], {
                    icon: createPinIcon('subgroup'),
                }).addTo(pinLayers);
                addTooltip(marker, `${subgroup.name} subgroup pin`);
                markerByKey.set(key, marker);
            }
        });
    }

    function selectedBoundary() {
        const target = currentTarget();
        if (!target?.boundary) return null;
        const featureLayer = L.geoJSON(target.boundary);
        let layer = null;
        featureLayer.eachLayer((child) => { layer = child; });
        return layer;
    }

    function clearDraftLayer() {
        if (drawingLine) map.removeLayer(drawingLine);
        if (draftLayer) map.removeLayer(draftLayer);
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
        if (targetPin) map.removeLayer(targetPin);
        targetPin = null;
    }

    function resetTargetPin() {
        removeTargetPin();
        const target = currentTarget();
        const key = target ? entityKey(currentTargetType(), target.id) : null;
        const savedMarker = key ? markerByKey.get(key) : null;
        if (savedMarker) map.removeLayer(savedMarker);
        if (target?.latitude !== null && target?.latitude !== undefined
            && target?.longitude !== null && target?.longitude !== undefined) {
            targetPin = L.marker([target.latitude, target.longitude], {
                icon: createPinIcon(currentTargetType()),
                draggable: true,
            }).addTo(map);
            targetPin.on('dragend', () => {
                targetPinDirty = true;
                savePinButton.disabled = false;
                cancelChangesButton.disabled = false;
                const position = targetPin.getLatLng();
                setStatus(`Pin for ${currentTargetName()} moved to ${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}. Save to keep it.`);
            });
            addTooltip(targetPin, `${target.name} selected pin`);
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

    function fitVisibleLayers() {
        const visibleLayers = [...savedLayerByKey.values(), ...markerByKey.values()];
        if (!visibleLayers.length) return;

        const bounds = L.featureGroup(visibleLayers).getBounds();
        if (bounds.isValid()) map.fitBounds(bounds.pad(0.12), { maxZoom: 16 });
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
        const pinKey = entityKey(currentTargetType(), target.id);
        const savedBoundary = savedLayerByKey.get(pinKey);
        const savedMarker = markerByKey.get(pinKey);
        const boundsLayer = savedBoundary && map.hasLayer(savedBoundary)
            ? savedBoundary
            : savedMarker && map.hasLayer(savedMarker)
                ? savedMarker
                : null;
        if (boundsLayer) {
            const bounds = boundsLayer.getBounds?.();
            if (bounds?.isValid()) map.fitBounds(bounds.pad(0.18), { maxZoom: 17 });
            else map.setView(boundsLayer.getLatLng(), 16);
        } else if (target.latitude !== null && target.longitude !== null) {
            map.setView([target.latitude, target.longitude], 16);
        }
        setStatus(`${target.name} selected. ${target.boundary ? 'Saved boundary is shown.' : 'No boundary saved yet.'} ${target.latitude !== null ? 'Pin is saved.' : 'No pin saved yet.'}`);
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
        const feature = draftLayer.toGeoJSON();
        const geometry = feature.type === 'Feature' ? feature.geometry : feature;
        if (geometry?.type !== 'Polygon' || !Array.isArray(geometry.coordinates?.[0])) return null;
        const ring = geometry.coordinates[0];
        if (ring.length < 4) return null;
        const first = ring[0];
        const last = ring[ring.length - 1];
        if (first[0] !== last[0] || first[1] !== last[1]) ring.push([...first]);
        const area = ring.reduce((sum, point, index) => {
            if (index === 0) return sum;
            const previous = ring[index - 1];
            return sum + previous[0] * point[1] - point[0] * previous[1];
        }, 0);
        if (Math.abs(area) < 1e-10) return null;
        return geometry;
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

    async function initializeMap() {
        if (typeof L === 'undefined' || !mapElement) {
            setStatus('The interactive map library could not be loaded. Check your connection and reload this page.', 'error');
            return;
        }

        map = L.map(mapElement, { doubleClickZoom: false }).setView([14.0668, 120.8531], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap contributors</a>',
        }).addTo(map).on('tileerror', () => {
            setStatus('Map tiles failed to load. Check your connection or try again later.', 'error');
        });
        savedBoundaryLayers.addTo(map);
        pinLayers.addTo(map);
        renderSavedLayers();

        map.on('click', (event) => {
            if (mode === 'draw') {
                if (drawingFinished) return;
                drawingPoints.push([event.latlng.lat, event.latlng.lng]);
                if (drawingLine) map.removeLayer(drawingLine);
                if (draftLayer) map.removeLayer(draftLayer);
                if (drawingPoints.length >= 3) {
                    draftLayer = L.polygon(drawingPoints, {
                        ...colorFor(currentTargetType(), currentTarget()),
                        weight: 3,
                        fillOpacity: 0.16,
                    }).addTo(map);
                } else {
                    drawingLine = L.polyline(drawingPoints, {
                        color: colorFor(currentTargetType(), currentTarget()).color,
                        dashArray: '5 5',
                        weight: 3,
                    }).addTo(map);
                }
                boundaryDirty = drawingPoints.length >= 3;
                updateBoundaryButtons();
                setStatus(`${drawingPoints.length} boundary point${drawingPoints.length === 1 ? '' : 's'} added for ${currentTargetName()}. Finish drawing to preview and save.`);
                return;
            }
            if (mode !== 'pin' || !currentTarget()) return;
            if (targetPin) targetPin.setLatLng(event.latlng);
            else {
                targetPin = L.marker(event.latlng, {
                    icon: createPinIcon(currentTargetType()),
                    draggable: true,
                }).addTo(map);
                targetPin.on('dragend', () => {
                    targetPinDirty = true;
                    updateBoundaryButtons();
                    savePinButton.disabled = false;
                    setStatus(`Pin for ${currentTargetName()} moved. Save to keep it.`);
                });
            }
            targetPinDirty = true;
            savePinButton.disabled = false;
            cancelChangesButton.disabled = false;
            setStatus(`Pin selected for ${currentTargetName()} at ${event.latlng.lat.toFixed(6)}, ${event.latlng.lng.toFixed(6)}. Save to keep it.`);
        });

        map.on('pm:edit pm:vertexadded pm:vertexremoved pm:markerdragend', () => {
            if (mode !== 'edit') return;
            boundaryDirty = true;
            drawingFinished = true;
            updateBoundaryButtons();
            setStatus(`Boundary for ${currentTargetName()} edited. Save to apply the changes.`);
        });

        refreshTarget();
    }

    function chooseMode(nextMode) {
        const target = currentTarget();
        if (!target) {
            setStatus('Select a Purok and target before editing the map.', 'error');
            return;
        }
        if ((boundaryDirty || targetPinDirty) && !window.confirm('Discard unsaved map changes?')) return;
        if (draftLayer?.pm?.enabled?.()) draftLayer.pm.disable();
        clearDraftLayer();
        if (mode === 'draw' && map.pm?.Draw?.getActiveShape?.()) map.pm.disableDraw();
        removeTargetPin();
        mode = nextMode;
        root.querySelectorAll('.map-mode-controls .button-group:first-child .btn').forEach((button) => button.classList.remove('active'));
        const activeButton = nextMode === 'pin'
            ? document.getElementById('pinMode')
            : nextMode === 'edit'
                ? editBoundaryButton
                : nextMode === 'draw'
                    ? currentTargetType() === 'purok'
                        ? document.getElementById('drawPurokBoundary')
                        : document.getElementById('drawSubgroupBoundary')
                    : null;
        const modeButtons = root.querySelectorAll('.map-mode-controls .button-group:first-child .btn');
        modeButtons.forEach((button) => {
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
            if (!L.PM) {
                mode = 'pin';
                resetTargetPin();
                setStatus('Boundary editing tools failed to load. Check your connection and reload the page.', 'error');
                return;
            }
            const layer = selectedBoundary();
            if (!layer) {
                mode = 'pin';
                setStatus(`No saved boundary exists for ${target.name}. Draw a boundary first.`, 'error');
                resetTargetPin();
                return;
            }
            draftLayer = layer;
            draftLayer.setStyle({ ...colorFor(currentTargetType(), target), weight: 3, fillOpacity: 0.2 });
            draftLayer.addTo(map);
            draftLayer.pm?.enable({ allowSelfIntersection: false });
            boundaryDirty = false;
            drawingFinished = true;
            deleteBoundaryButton.disabled = true;
            cancelChangesButton.disabled = false;
            setStatus(`Edit mode for ${target.name}. Drag vertices, add a vertex on an edge, or remove a vertex. Save when finished.`);
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
        if (drawingLine) map.removeLayer(drawingLine);
        drawingLine = null;
        draftLayer = L.polygon(drawingPoints, {
            ...colorFor(currentTargetType(), currentTarget()),
            weight: 3,
            fillOpacity: 0.18,
        }).addTo(map);
        drawingFinished = true;
        boundaryDirty = true;
        updateBoundaryButtons();
        setStatus(`Boundary preview ready for ${currentTargetName()}. Save boundary to replace the existing saved shape.`);
    });

    undoBoundaryButton.addEventListener('click', () => {
        if (mode !== 'draw' || drawingFinished || drawingPoints.length === 0) return;
        drawingPoints.pop();
        if (drawingLine) map.removeLayer(drawingLine);
        if (draftLayer) map.removeLayer(draftLayer);
        drawingLine = null;
        draftLayer = null;
        if (drawingPoints.length >= 3) {
            draftLayer = L.polygon(drawingPoints, {
                ...colorFor(currentTargetType(), currentTarget()),
                weight: 3,
                fillOpacity: 0.16,
            }).addTo(map);
        } else if (drawingPoints.length) {
            drawingLine = L.polyline(drawingPoints, {
                color: colorFor(currentTargetType(), currentTarget()).color,
                dashArray: '5 5',
                weight: 3,
            }).addTo(map);
        }
        boundaryDirty = drawingPoints.length >= 3;
        updateBoundaryButtons();
        setStatus(`${drawingPoints.length} unsaved boundary points remain.`);
    });

    cancelChangesButton.addEventListener('click', () => {
        if ((boundaryDirty || targetPinDirty) && !window.confirm('Discard unsaved map changes?')) return;
        if (draftLayer?.pm?.enabled?.()) draftLayer.pm.disable();
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
        const position = targetPin?.getLatLng();
        savePinButton.disabled = true;
        setStatus(`Saving pin for ${target.name}...`);
        try {
            await sendJson(mapRoute('Pin', target.id), {
                latitude: position ? position.lat : null,
                longitude: position ? position.lng : null,
            });
            target.latitude = position ? position.lat : null;
            target.longitude = position ? position.lng : null;
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
            if (draftLayer?.pm?.enabled?.()) draftLayer.pm.disable();
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
            if (draftLayer?.pm?.enabled?.()) draftLayer.pm.disable();
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
            form.querySelectorAll('input[name="bhw_ids[]"]').forEach((checkbox) => { checkbox.checked = true; });
            form.querySelector('.assignment-count').textContent = form.querySelectorAll('input[name="bhw_ids[]"]:checked').length;
        });
    });
    root.querySelectorAll('.clear-bhws').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            form.querySelectorAll('input[name="bhw_ids[]"]').forEach((checkbox) => { checkbox.checked = false; });
            form.querySelector('.assignment-count').textContent = '0';
        });
    });
    root.querySelectorAll('.subgroup-edit-form input[name="bhw_ids[]"]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const form = checkbox.closest('form');
            form.querySelector('.assignment-count').textContent = form.querySelectorAll('input[name="bhw_ids[]"]:checked').length;
        });
    });

    if (typeof L === 'undefined') {
        setStatus('The map library could not be loaded. Check your connection and reload this page.', 'error');
    } else {
        initializeMap();
    }
});
