@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/demographics.css') }}">
@endsection

@section('content')
<div class="demographics-container">
    <h1>Demographics, Puroks & Subgroups Management</h1>
    <p>
        Creation of citizen demographics, purok distribution, and population data.
    </p>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- Manage Puroks and Subgroups --}}
    <div class="grid-row">

        {{-- Manage Puroks --}}
        <div class="card">
            <h2>Manage Puroks</h2>

            <form action="{{ route('admin.puroks.store') }}" method="POST" style="margin-bottom: 20px;">
                @csrf

                <div class="form-row">
                    <input
                        type="text"
                        name="name"
                        placeholder="Enter Purok name (e.g., Purok 1)"
                        class="form-control"
                        required
                    >

                    <button type="submit" class="btn btn-primary">
                        Add Purok
                    </button>
                </div>
            </form>

            <h3 style="margin: 0 0 10px; color: #34495e; font-size: 15px;">
                Purok Locations
            </h3>

            <p class="location-status" id="purokLocationStatus">
                Select a Purok, then click the map to add or move its pin.
            </p>

            <div class="form-row" style="margin-bottom: 12px;">
                <select id="purokLocationSelect" class="form-control">
                    <option value="">-- Select Purok --</option>

                    @foreach($puroks as $purok)
                        <option
                            value="{{ $purok->id }}"
                            data-lat="{{ $purok->latitude ?? '' }}"
                            data-lng="{{ $purok->longitude ?? '' }}"
                        >
                            {{ $purok->name }}
                        </option>
                    @endforeach
                </select>

                <button
                    type="button"
                    id="savePurokLocation"
                    class="btn btn-primary"
                    disabled
                >
                    Save Location
                </button>
            </div>

            <div
                id="purokLocationMap"
                class="purok-location-map"
                style="margin-bottom: 18px;"
            ></div>

            <ul style="list-style: none; padding: 0; margin: 0; flex: 1;">
                @forelse($puroks as $purok)
                    <li class="purok-item">

                        <form
                            action="{{ route('admin.puroks.update', $purok->id) }}"
                            method="POST"
                            class="purok-update-form purok-location-form"
                            data-purok-id="{{ $purok->id }}"
                            data-purok-name="{{ $purok->name }}"
                        >
                            @csrf
                            @method('PUT')

                            <input
                                type="text"
                                name="name"
                                value="{{ $purok->name }}"
                                class="form-control"
                                style="flex: 1 1 150px;"
                                required
                            >

                            <input
                                type="hidden"
                                name="latitude"
                                value="{{ $purok->latitude }}"
                                class="purok-latitude"
                            >

                            <input
                                type="hidden"
                                name="longitude"
                                value="{{ $purok->longitude }}"
                                class="purok-longitude"
                            >

                            <button
                                type="button"
                                class="btn btn-primary pick-purok-location"
                                data-purok-id="{{ $purok->id }}"
                            >
                                {{ $purok->latitude !== null && $purok->longitude !== null ? 'Move Pin' : 'Pick Location' }}
                            </button>

                            <button type="submit" class="btn btn-success">
                                Save
                            </button>
                        </form>

                        <form
                            action="{{ route('admin.puroks.destroy', $purok->id) }}"
                            method="POST"
                            onsubmit="return confirm('Delete purok and its subgroups?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                Delete
                            </button>
                        </form>

                    </li>
                @empty
                    <li class="empty-text">No puroks added yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- Manage Subgroups and Assigned BHWs --}}
        <div class="card">
            <h2>Manage Subgroups & Assigned BHWs</h2>

            <form
                action="{{ route('admin.subgroups.store') }}"
                method="POST"
                style="margin-bottom: 20px;"
            >
                @csrf

                <div class="form-row">
                    <select name="purok_id" class="form-control" required>
                        <option value="">-- Select Parent Purok --</option>

                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}">
                                {{ $purok->name }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="text"
                        name="name"
                        placeholder="Subgroup name (e.g., P1A)"
                        class="form-control"
                        required
                    >

                    <select name="user_id" class="form-control">
                        <option value="">-- Assign BHW (Optional) --</option>

                        @foreach($bhws as $bhw)
                            <option value="{{ $bhw->id }}">
                                {{ $bhw->name ?? $bhw->email }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">
                        Add Subgroup
                    </button>
                </div>
            </form>

            <ul style="list-style: none; padding: 0; margin: 0; flex: 1;">
                @forelse($subgroups as $sub)
                    <li class="purok-item">

                        <form
                            action="{{ route('admin.subgroups.update', $sub->id) }}"
                            method="POST"
                            class="purok-update-form"
                        >
                            @csrf
                            @method('PUT')

                            <select
                                name="purok_id"
                                class="form-control"
                                style="flex: 1 1 100px;"
                                required
                            >
                                @foreach($puroks as $purok)
                                    <option
                                        value="{{ $purok->id }}"
                                        {{ $sub->purok_id == $purok->id ? 'selected' : '' }}
                                    >
                                        {{ $purok->name }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                type="text"
                                name="name"
                                value="{{ $sub->name }}"
                                class="form-control"
                                style="flex: 1 1 90px;"
                                required
                            >

                            <select
                                name="user_id"
                                class="form-control"
                                style="flex: 2 1 130px;"
                            >
                                <option value="">-- No BHW --</option>

                                @foreach($bhws as $bhw)
                                    <option
                                        value="{{ $bhw->id }}"
                                        {{ $sub->user_id == $bhw->id ? 'selected' : '' }}
                                    >
                                        {{ $bhw->name ?? $bhw->email }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-success">
                                Save
                            </button>
                        </form>

                        <form
                            action="{{ route('admin.subgroups.destroy', $sub->id) }}"
                            method="POST"
                            onsubmit="return confirm('Delete this subgroup?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                Delete
                            </button>
                        </form>

                    </li>
                @empty
                    <li class="empty-text">No subgroups added yet.</li>
                @endforelse
            </ul>
        </div>

    </div>
</div>
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
