@extends('templates.admin')

@section('CSSown')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.18.0/dist/leaflet-geoman.css">
<link rel="stylesheet" href="{{ asset('css/admin/demographics.css') }}">
@endsection

@section('content')
<main class="demographics-container" id="demographicsApp" data-initial-tab="{{ old('demographics_tab', session('demographics_tab', 'overview')) }}">
    <header class="demographics-header">
        <div>

            <h1>Demographics, Puroks &amp; Subgroups</h1>
            <p>Manage community areas, map coverage, subgroup assignments, and local health workers.</p>
        </div>
        <button type="button" class="btn btn-primary demographics-header-action" data-open-tab="map">Open map workspace</button>
    </header>

    @if(session('success'))
        <div class="demographics-alert success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="demographics-alert error" role="alert">
            <strong>Please review the following:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <nav class="demographics-tabs" role="tablist" aria-label="Demographics management sections">
        <button type="button" class="demographics-tab active" role="tab" aria-selected="true" data-tab="overview">Overview</button>
        <button type="button" class="demographics-tab" role="tab" aria-selected="false" data-tab="puroks">Purok management</button>
        <button type="button" class="demographics-tab" role="tab" aria-selected="false" data-tab="subgroups">Subgroups &amp; BHWs</button>
        <button type="button" class="demographics-tab" role="tab" aria-selected="false" data-tab="map">Map &amp; boundaries</button>
    </nav>

    <section class="demographics-panel active" role="tabpanel" data-panel="overview">
        <div class="demographics-stats">
            <article class="demographics-stat"><span>Puroks</span><strong>{{ $puroks->count() }}</strong></article>
            <article class="demographics-stat"><span>Subgroups</span><strong>{{ $subgroups->count() }}</strong></article>
            <article class="demographics-stat"><span>Barangay health workers</span><strong>{{ $bhws->count() }}</strong></article>
            <article class="demographics-stat"><span>Map pins saved</span><strong>{{ $puroks->filter(fn ($purok) => $purok->latitude !== null && $purok->longitude !== null)->count() + $subgroups->filter(fn ($subgroup) => $subgroup->latitude !== null && $subgroup->longitude !== null)->count() }}</strong></article>
        </div>

        <section class="demographics-card workflow-card">
            <div class="section-heading">
                <div><h2>Recommended workflow</h2><p>Set up the area first, then add local coverage and assignments.</p></div>
            </div>
            <ol class="workflow-steps">
                <li><span>1</span><div><strong>Create or select a Purok</strong><small>Add the area name and an optional description.</small></div></li>
                <li><span>2</span><div><strong>Set its pin and boundary</strong><small>Choose the Purok on the map, place its marker, and draw the coverage outline.</small></div></li>
                <li><span>3</span><div><strong>Create subgroups and assign BHWs</strong><small>Each subgroup can have its own pin, boundary, and multiple BHWs.</small></div></li>
                <li><span>4</span><div><strong>Review and save</strong><small>Map changes are saved explicitly and remain independent for each area.</small></div></li>
            </ol>
        </section>

        <section class="demographics-card overview-list">
            <div class="section-heading"><div><h2>Purok summary</h2><p>Current locations and subgroup coverage.</p></div><button type="button" class="btn btn-primary" data-open-tab="puroks">Manage Puroks</button></div>
            @forelse($puroks as $purok)
                <article class="overview-purok">
                    <div><strong>{{ $purok->name }}</strong><small>{{ $purok->description ?: 'No description provided' }}</small></div>
                    <div class="overview-badges"><span>{{ $purok->subgroups_count }} subgroups</span><span>{{ $purok->latitude !== null ? 'Pin saved' : 'No pin' }}</span><span>{{ $purok->boundary ? 'Boundary saved' : 'No boundary' }}</span></div>
                </article>
            @empty
                <p class="empty-state">No Puroks yet. Create a Purok to begin.</p>
            @endforelse
        </section>
    </section>

    <section class="demographics-panel" role="tabpanel" data-panel="puroks" hidden>
        <div class="demographics-card">
            <div class="section-heading"><div><h2>Create a Purok</h2><p>Names must be unique. Add a description if it helps identify the area.</p></div></div>
            <form action="{{ route('admin.puroks.store') }}" method="POST" class="purok-create-form">
                @csrf
                <label class="field"><span>Purok name</span><input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Purok 1" maxlength="255" required></label>
                <label class="field"><span>Description <small>(optional)</small></span><input type="text" name="description" value="{{ old('description') }}" placeholder="Area notes or landmarks" maxlength="2000"></label>
                <button type="submit" class="btn btn-primary">Add Purok</button>
            </form>
        </div>

        <div class="demographics-card">
            <div class="section-heading"><div><h2>Existing Puroks</h2><p>Update names and descriptions. Locations and boundaries are edited in the map workspace.</p></div></div>
            <div class="purok-list">
                @forelse($puroks as $purok)
                    <article class="purok-record">
                        <form action="{{ route('admin.puroks.update', $purok->id) }}" method="POST" class="purok-edit-form">
                            @csrf
                            @method('PUT')
                            <label class="field"><span>Name</span><input type="text" name="name" value="{{ $purok->name }}" maxlength="255" required></label>
                            <label class="field"><span>Description</span><input type="text" name="description" value="{{ $purok->description }}" maxlength="2000" placeholder="Optional"></label>
                            <div class="record-meta"><span>{{ $purok->families_count }} families</span><span>{{ $purok->subgroups_count }} subgroups</span><span>{{ $purok->purge_citizens_count }} citizens</span></div>
                            <div class="record-actions"><button type="submit" class="btn btn-secondary">Save changes</button></div>
                        </form>
                        <form action="{{ route('admin.puroks.destroy', $purok->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Delete this Purok? It can only be deleted after its families, subgroups, and citizen references have been moved or removed.')">
                            @csrf
                            @method('DELETE')
                            <label class="field delete-password-field"><span>Admin password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                            <button type="submit" class="btn btn-danger" @disabled($purok->families_count || $purok->subgroups_count) title="Move dependent records before deleting">Delete</button>
                        </form>
                        <form action="{{ route('admin.puroks.citizens.destroy', $purok->id) }}" method="POST" class="citizen-purge-form" data-confirm-message="Permanently delete all {{ $purok->purge_citizens_count }} citizen records in {{ $purok->name }}? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="demographics_tab" value="puroks">
                            <div class="purge-heading"><strong>Permanent citizen deletion</strong><span>{{ $purok->purge_citizens_count }} citizens</span></div>
                            <p>Deletes citizen profiles and linked citizen login accounts. Related health, vaccination, medication, maintenance, and supply-release records may also be deleted. Families and BHW accounts are kept.</p>
                            <label class="field"><span>Type <strong>{{ $purok->name }}</strong> to confirm</span><input type="text" name="confirmation" maxlength="255" autocomplete="off" required></label>
                            <label class="field"><span>Admin password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                            <button type="submit" class="btn btn-danger" @disabled(!$purok->purge_citizens_count)>Permanently delete citizens</button>
                        </form>
                    </article>
                @empty
                    <p class="empty-state">No Puroks have been created yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="demographics-panel" role="tabpanel" data-panel="subgroups" hidden>
        <div class="demographics-card">
            <div class="section-heading"><div><h2>Create a subgroup</h2><p>Choose a parent Purok and optionally assign one or more BHWs.</p></div></div>
            <form action="{{ route('admin.subgroups.store') }}" method="POST" class="subgroup-create-form">
                @csrf
                <label class="field"><span>Parent Purok</span><select name="purok_id" required><option value="">Select Purok</option>@foreach($puroks as $purok)<option value="{{ $purok->id }}" {{ old('purok_id') == $purok->id ? 'selected' : '' }}>{{ $purok->name }}</option>@endforeach</select></label>
                <label class="field"><span>Subgroup name</span><input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. P1A" maxlength="255" required></label>
                <label class="field subgroup-description"><span>Description <small>(optional)</small></span><input type="text" name="description" value="{{ old('description') }}" placeholder="Optional area notes" maxlength="2000"></label>
                <div class="field bhw-selector">
                    <span>Assign BHWs <small>(optional, select all that apply)</small></span>
                    <div class="bhw-checklist">
                        @forelse($bhws as $bhw)
                            <label><input type="checkbox" name="bhw_ids[]" value="{{ $bhw->id }}"><span>{{ $bhw->name ?: $bhw->email }}</span></label>
                        @empty
                            <p class="empty-state">No BHW accounts available.</p>
                        @endforelse
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add subgroup</button>
            </form>
        </div>

        <div class="demographics-card">
            <div class="section-heading"><div><h2>Subgroups &amp; assignments</h2><p>Assignments are stored independently; saving one subgroup will not affect another.</p></div></div>
            <div class="subgroup-list">
                @forelse($subgroups as $subgroup)
                    <article class="subgroup-record">
                        <form action="{{ route('admin.subgroups.update', $subgroup->id) }}" method="POST" class="subgroup-edit-form">
                            @csrf
                            @method('PUT')
                            <div class="subgroup-form-fields">
                                <label class="field"><span>Parent Purok</span><select name="purok_id" required>@foreach($puroks as $purok)<option value="{{ $purok->id }}" {{ $subgroup->purok_id == $purok->id ? 'selected' : '' }}>{{ $purok->name }}</option>@endforeach</select></label>
                                <label class="field"><span>Subgroup name</span><input type="text" name="name" value="{{ $subgroup->name }}" maxlength="255" required></label>
                                <label class="field"><span>Description</span><input type="text" name="description" value="{{ $subgroup->description }}" maxlength="2000" placeholder="Optional"></label>
                            </div>
                            <div class="bhw-assignment">
                                <div class="assignment-heading"><strong>Assigned BHWs <span class="assignment-count">{{ $subgroup->bhws->count() }}</span></strong><div><button type="button" class="text-button select-all-bhws">Select all</button><button type="button" class="text-button clear-bhws">Clear</button></div></div>
                                <input type="hidden" name="bhw_ids_present" value="1">
                                <div class="bhw-checklist">
                                    @forelse($bhws as $bhw)
                                        <label><input type="checkbox" name="bhw_ids[]" value="{{ $bhw->id }}" {{ $subgroup->bhws->contains('id', $bhw->id) || (!$subgroup->bhws->count() && $subgroup->user_id == $bhw->id) ? 'checked' : '' }}><span>{{ $bhw->name ?: $bhw->email }}</span></label>
                                    @empty
                                        <p class="empty-state">No BHW accounts available.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="subgroup-record-footer">
                                <div class="record-meta"><span>{{ $subgroup->families_count }} families</span><span>{{ $subgroup->purge_citizens_count }} citizens</span><span>{{ $subgroup->latitude !== null ? 'Pin saved' : 'No pin' }}</span></div>
                                <div class="record-actions"><button type="submit" class="btn btn-secondary">Save subgroup</button></div>
                            </div>
                        </form>
                        <form action="{{ route('admin.subgroups.destroy', $subgroup->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Delete this subgroup? Any empty family records will also be removed. Move or remove its citizens first.')">
                            @csrf
                            @method('DELETE')
                            <label class="field delete-password-field"><span>Admin password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                            <button type="submit" class="btn btn-danger" @disabled($subgroup->purge_citizens_count) title="Move or remove citizens before deleting; empty family records will be removed">Delete</button>
                        </form>
                        <form action="{{ route('admin.subgroups.citizens.destroy', $subgroup->id) }}" method="POST" class="citizen-purge-form" data-confirm-message="Permanently delete all {{ $subgroup->purge_citizens_count }} citizen records in {{ $subgroup->name }} - {{ $subgroup->purok->name }}? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="demographics_tab" value="subgroups">
                            <div class="purge-heading"><strong>Permanent citizen deletion</strong><span>{{ $subgroup->purge_citizens_count }} citizens</span></div>
                            <p>Deletes citizen profiles and linked citizen login accounts. Related health, vaccination, medication, maintenance, and supply-release records may also be deleted. Families and BHW accounts are kept.</p>
                            <label class="field"><span>Type <strong>{{ $subgroup->name }} - {{ $subgroup->purok->name }}</strong> to confirm</span><input type="text" name="confirmation" maxlength="513" autocomplete="off" required></label>
                            <label class="field"><span>Admin password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                            <button type="submit" class="btn btn-danger" @disabled(!$subgroup->purge_citizens_count)>Permanently delete citizens</button>
                        </form>
                    </article>
                @empty
                    <p class="empty-state">No subgroups have been created yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="demographics-panel map-workspace" role="tabpanel" data-panel="map" hidden>
        <div class="demographics-card map-controls">
            <div class="section-heading"><div><h2>Map &amp; boundary workspace</h2><p>Choose a Purok or subgroup, then edit its pin or polygon. Save each change when ready.</p></div></div>
            <div class="map-selector-grid">
                <label class="field"><span>Purok</span><select id="mapPurok"><option value="">All Puroks / choose one</option>@foreach($puroks as $purok)<option value="{{ $purok->id }}">{{ $purok->name }}</option>@endforeach</select></label>
                <label class="field"><span>Map target</span><select id="mapTargetType"><option value="purok">Selected Purok</option><option value="subgroup">Subgroup</option></select></label>
                <label class="field" id="subgroupTargetField"><span>Subgroup</span><select id="mapSubgroup"><option value="">Select a subgroup</option></select></label>
            </div>
            <div class="map-mode-controls">
                <div class="button-group" aria-label="Map editing mode">
                    <button type="button" class="btn btn-secondary active" id="pinMode" aria-pressed="true">Place pin</button>
                    <button type="button" class="btn btn-secondary" id="drawPurokBoundary" aria-pressed="false">Draw Purok boundary</button>
                    <button type="button" class="btn btn-secondary" id="drawSubgroupBoundary" aria-pressed="false">Draw subgroup boundary</button>
                    <button type="button" class="btn btn-secondary" id="editBoundary" aria-pressed="false">Edit saved boundary</button>
                    <button type="button" class="btn btn-secondary" id="finishBoundary" aria-pressed="false" disabled>Finish drawing</button>
                </div>
                <div class="button-group map-edit-actions">
                    <button type="button" class="btn btn-secondary" id="undoBoundary" disabled>Undo point</button>
                    <button type="button" class="btn btn-secondary" id="cancelMapChanges" disabled>Cancel changes</button>
                    <button type="button" class="btn btn-secondary" id="resetPin">Reset pin</button>
                    <button type="button" class="btn btn-primary" id="savePin" disabled>Save pin</button>
                    <button type="button" class="btn btn-primary" id="saveBoundary" disabled>Save boundary</button>
                    <button type="button" class="btn btn-danger" id="deleteBoundary" disabled>Delete Purok boundary</button>
                </div>
            </div>
            <p class="map-status" id="mapStatus" role="status">Choose a Purok to view its map details.</p>
            <div class="map-legend" aria-label="Map legend"><span><i class="legend-pin"></i> Purok pin</span><span><i class="legend-subgroup-pin"></i> Subgroup pin</span><span><i class="legend-area"></i> Purok boundary</span><span><i class="legend-subgroup-area"></i> Subgroup boundary</span></div>
            <fieldset class="boundary-visibility"><legend>Boundary visibility</legend><div id="boundaryVisibilityOptions"></div></fieldset>
            <div id="demographicsMap" class="demographics-map" role="application" aria-label="Purok and subgroup map"></div>
            <p class="map-attribution-note">Map tiles © OpenStreetMap contributors. Click to place pins. In boundary mode, add vertices, then select Finish drawing; use the edit tool to move, add, or remove vertices.</p>
        </div>
    </section>

    <script>window.demographicsMapData = {{ \Illuminate\Support\Js::from($demographicsMapData) }};</script>
</main>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBb3WrQ40r3wzE1NKWVQFYtock7GASNJs&loading=async"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('purokLocationMap');
    const locationSelect = document.getElementById('purokLocationSelect');
    const statusElement = document.getElementById('purokLocationStatus');
    const saveLocationButton = document.getElementById('savePurokLocation');

    const defaultCenter = {
        lat: 14.0668,
        lng: 120.8531
    };

    let map;
    let marker;
    let MapConstructor;
    let MarkerConstructor;
    let activeForm;

    function getForm(id) {
        return document.querySelector(
            `.purok-location-form[data-purok-id="${id}"]`
        );
    }

    function setPin(latitude, longitude, hasSavedLocation) {
        const position = {
            lat: latitude,
            lng: longitude
        };

        map.setCenter(position);
        map.setZoom(hasSavedLocation ? 17 : 15);

        if (!marker) {
            marker = new MarkerConstructor({
                map: map,
                position: position,
                draggable: true
            });

            marker.addListener('dragend', function (event) {
                updateCoordinates(
                    event.latLng.lat(),
                    event.latLng.lng()
                );
            });
        } else {
            marker.setPosition(position);
            marker.setMap(map);
        }
    }

    function updateCoordinates(latitude, longitude) {
        if (!activeForm) {
            return;
        }

        activeForm.querySelector('.purok-latitude').value =
            latitude.toFixed(7);

        activeForm.querySelector('.purok-longitude').value =
            longitude.toFixed(7);

        statusElement.textContent =
            `Pin selected for ${activeForm.dataset.purokName}. Save the Purok to keep this location.`;
    }

    function loadSelectedPurok() {
        if (!map) {
            return;
        }

        const option = locationSelect.options[
            locationSelect.selectedIndex
        ];

        activeForm = option.value
            ? getForm(option.value)
            : null;

        if (!activeForm) {
            statusElement.textContent =
                'Select a Purok, then click the map to add or move its pin.';

            saveLocationButton.disabled = true;

            if (marker) {
                marker.setMap(null);
            }

            map.setCenter(defaultCenter);
            map.setZoom(15);

            return;
        }

        const latitude = Number(option.dataset.lat);
        const longitude = Number(option.dataset.lng);

        const hasSavedLocation =
            option.dataset.lat !== '' &&
            option.dataset.lng !== '' &&
            Number.isFinite(latitude) &&
            Number.isFinite(longitude) &&
            latitude !== 0 &&
            longitude !== 0;

        if (hasSavedLocation) {
            setPin(latitude, longitude, true);

            statusElement.textContent =
                `${activeForm.dataset.purokName} is pinned. Click the map or drag the marker to move it.`;
        } else {
            setPin(
                defaultCenter.lat,
                defaultCenter.lng,
                false
            );

            statusElement.textContent =
                `${activeForm.dataset.purokName} has no location pinned yet. Click the map to add one.`;
        }

        saveLocationButton.disabled = false;
    }

    async function initializeMap() {
        if (
            !mapElement ||
            typeof google === 'undefined' ||
            !google.maps ||
            !google.maps.importLibrary
        ) {
            window.setTimeout(initializeMap, 250);
            return;
        }

        try {
            const mapsLibrary =
                await google.maps.importLibrary('maps');

            const markerLibrary =
                await google.maps.importLibrary('marker');

            MapConstructor = mapsLibrary.Map;
            MarkerConstructor = markerLibrary.Marker;

            map = new MapConstructor(mapElement, {
                center: defaultCenter,
                zoom: 15,
                mapTypeId: 'roadmap',
                streetViewControl: false,
                fullscreenControl: true
            });

            map.addListener('click', function (event) {
                if (!activeForm || !event.latLng) {
                    return;
                }

                const latitude = event.latLng.lat();
                const longitude = event.latLng.lng();

                setPin(latitude, longitude, true);

                updateCoordinates(latitude, longitude);
            });

            locationSelect.addEventListener(
                'change',
                loadSelectedPurok
            );

            saveLocationButton.addEventListener(
                'click',
                function () {
                    if (!activeForm) {
                        return;
                    }

                    const latitude = activeForm.querySelector(
                        '.purok-latitude'
                    ).value;

                    const longitude = activeForm.querySelector(
                        '.purok-longitude'
                    ).value;

                    if (!latitude || !longitude) {
                        statusElement.textContent =
                            'Click the map to place a pin before saving.';

                        return;
                    }

                    statusElement.textContent =
                        `Saving ${activeForm.dataset.purokName} at ${latitude}, ${longitude}...`;

                    activeForm.requestSubmit();
                }
            );

            document.querySelectorAll(
                '.pick-purok-location'
            ).forEach(function (button) {
                button.addEventListener('click', function () {
                    locationSelect.value = button.dataset.purokId;

                    loadSelectedPurok();

                    mapElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                });
            });

        } catch (error) {
            console.error('Failed to initialize Google Maps:', error);

            statusElement.textContent =
                'Unable to load the map. Check your Google Maps API configuration and internet connection.';
        }
    }

    initializeMap();
});
</script>
@endsection
