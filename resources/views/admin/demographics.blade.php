@extends('templates.admin')

@section('CSSown')
<style>
    /* Full-screen fluid container */
    .demographics-container {
        width: 100%;
        min-height: 100vh;
        margin: 0;
        padding: 24px;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        color: #333;
    }

    .demographics-container h1 {
        font-size: clamp(1.25rem, 2vw, 1.75rem);
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .grid-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
        width: 100%;
    }

    .card {
        background: #ffffff;
        padding: clamp(16px, 2vw, 24px);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
        box-sizing: border-box;
    }

    .card h2 {
        font-size: clamp(1rem, 1.5vw, 1.15rem);
        margin-bottom: 16px;
        color: #34495e;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex: 1;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .form-control {
        flex: 1 1 200px;
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #3498db;
        outline: none;
    }

    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .btn-primary {
        background-color: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2980b9;
    }

    .btn-success {
        background-color: #2ecc71;
        color: white;
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-danger {
        background: transparent;
        color: #e74c3c;
        padding: 6px 12px;
    }

    .btn-danger:hover {
        background-color: #fdf2f2;
        border-radius: 4px;
    }

    .purok-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 0;
        border-bottom: 1px solid #eee;
    }

    .purok-update-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        flex: 1 1 250px;
        align-items: center;
    }

    .empty-text {
        color: #7f8c8d;
        font-style: italic;
        text-align: center;
        padding: 12px 0;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }

    .purok-location-map {
        width: 100%;
        height: 320px;
        border-radius: 6px;
        background: #e5e7eb;
    }

    .location-status {
        margin: 0 0 12px;
        color: #64748b;
        font-size: 13px;
    }

    .location-coordinates {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 10px;
    }

    .location-coordinates input {
        min-width: 0;
    }
</style>
@endsection

@section('content')
<div class="demographics-container">
    <h1>Demographics, Puroks & Subgroups Management</h1>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <!-- Row 1: Add Purok Form & Add Subgroup Form -->
    <div class="grid-row">
        <!-- Add Purok Form Card (Cleaned: No BHW assignment here anymore) -->
        <div class="card">
            <h2>Add New Purok</h2>
            <form action="{{ route('admin.puroks.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1;">
                @csrf
                <div class="form-group">
                    <div class="form-row">
                        <input type="text" name="name" placeholder="Enter Purok name (e.g., Purok 1)" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Add Purok</button>
                </div>
            </form>
        </div>

        <!-- Add Purok Subgroup Form Card (Includes BHW Assignment) -->
        <div class="card">
            <h2>Add Purok Sub-group & Assign BHW</h2>
            <form action="{{ route('admin.subgroups.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1;">
                @csrf
                <div class="form-group">
                    <div class="form-row">
                        <select name="purok_id" class="form-control" required>
                            <option value="">-- Select Parent Purok --</option>
                            @foreach($puroks as $purok)
                                <option value="{{ $purok->id }}">{{ $purok->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="name" placeholder="Subgroup name (e.g., P1A)" class="form-control" required>
                    </div>
                    <div class="form-row" style="margin-top: 10px;">
                        <select name="user_id" class="form-control">
                            <option value="">-- Assign BHW (Optional) --</option>
                            @foreach($bhws as $bhw)
                                <option value="{{ $bhw->id }}">{{ $bhw->name ?? $bhw->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Add Subgroup</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h2>Pin Purok Location</h2>
        <p class="location-status" id="purokLocationStatus">Select a Purok, then click the map to add or move its pin.</p>
        <div class="form-row" style="margin-bottom: 12px;">
            <select id="purokLocationSelect" class="form-control">
                <option value="">-- Select Purok --</option>
                @foreach($puroks as $purok)
                    <option value="{{ $purok->id }}" data-lat="{{ $purok->latitude ?? '' }}" data-lng="{{ $purok->longitude ?? '' }}">{{ $purok->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="purokLocationMap" class="purok-location-map"></div>
    </div>

    <!-- Row 2: Existing Puroks List & Existing Subgroups List -->
    <div class="grid-row">
        <!-- Puroks List (Only name updates and deletion) -->
        <div class="card">
            <h2>Existing Puroks</h2>
            <ul style="list-style: none; padding:0; margin:0; flex: 1;">
                @forelse($puroks as $purok)
                    <li class="purok-item">
                        <form action="{{ route('admin.puroks.update', $purok->id) }}" method="POST" class="purok-update-form purok-location-form" data-purok-id="{{ $purok->id }}" data-purok-name="{{ $purok->name }}">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $purok->name }}" class="form-control" style="flex: 1 1 150px;" required>
                            <input type="hidden" name="latitude" value="{{ $purok->latitude }}" class="purok-latitude">
                            <input type="hidden" name="longitude" value="{{ $purok->longitude }}" class="purok-longitude">
                            <button type="button" class="btn btn-primary pick-purok-location" data-purok-id="{{ $purok->id }}">{{ $purok->latitude !== null && $purok->longitude !== null ? 'Move Pin' : 'Pick Location' }}</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </form>
                        <form action="{{ route('admin.puroks.destroy', $purok->id) }}" method="POST" onsubmit="return confirm('Delete purok and its subgroups?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </li>
                @empty
                    <li class="empty-text">No puroks added yet.</li>
                @endforelse
            </ul>
        </div>

        <!-- Subgroups List (Editable with Purok dropdown, Subgroup name, and BHW assignment) -->
        <div class="card">
            <h2>Existing Subgroups & Assigned BHWs</h2>
            <ul style="list-style: none; padding:0; margin:0; flex: 1;">
                @forelse($subgroups as $sub)
                    <li class="purok-item">
                        <form action="{{ route('admin.subgroups.update', $sub->id) }}" method="POST" class="purok-update-form">
    @csrf
    @method('PUT')
    
    <select name="purok_id" class="form-control" style="flex: 1 1 100px;" required>
        @foreach($puroks as $purok)
            <option value="{{ $purok->id }}" {{ $sub->purok_id == $purok->id ? 'selected' : '' }}>
                {{ $purok->name }}
            </option>
        @endforeach
    </select>

    <input type="text" name="name" value="{{ $sub->name }}" class="form-control" style="flex: 1 1 90px;" required>

    {{-- 👇 CHECK THIS SELECT ELEMENT --}}
    <select name="user_id" class="form-control" style="flex: 2 1 130px;">
        <option value="">-- No BHW --</option>
        @foreach($bhws as $bhw)
            <option value="{{ $bhw->id }}" {{ $sub->user_id == $bhw->id ? 'selected' : '' }}>
                {{ $bhw->name ?? $bhw->email }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-success">Save</button>
</form>
                        <form action="{{ route('admin.subgroups.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Delete this subgroup?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
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
    const defaultCenter = { lat: 14.0668, lng: 120.8531 };
    let map;
    let marker;
    let MapConstructor;
    let MarkerConstructor;
    let activeForm;

    function getForm(id) {
        return document.querySelector(`.purok-location-form[data-purok-id="${id}"]`);
    }

    function setPin(latitude, longitude, hasSavedLocation) {
        const position = { lat: latitude, lng: longitude };
        map.setCenter(position);
        map.setZoom(hasSavedLocation ? 17 : 15);

        if (!marker) {
            marker = new MarkerConstructor({ map, position, draggable: true });
            marker.addListener('dragend', function (event) {
                updateCoordinates(event.latLng.lat(), event.latLng.lng());
            });
        } else {
            marker.setPosition(position);
            marker.setMap(map);
        }
    }

    function updateCoordinates(latitude, longitude) {
        if (!activeForm) return;
        activeForm.querySelector('.purok-latitude').value = latitude.toFixed(7);
        activeForm.querySelector('.purok-longitude').value = longitude.toFixed(7);
        statusElement.textContent = `Pin selected for ${activeForm.dataset.purokName}. Save the Purok to keep this location.`;
    }

    function loadSelectedPurok() {
        if (!map) return;
        const option = locationSelect.options[locationSelect.selectedIndex];
        activeForm = option.value ? getForm(option.value) : null;
        if (!activeForm) {
            statusElement.textContent = 'Select a Purok, then click the map to add or move its pin.';
            if (marker) marker.setMap(null);
            map.setCenter(defaultCenter);
            map.setZoom(15);
            return;
        }

        const latitude = Number(option.dataset.lat);
        const longitude = Number(option.dataset.lng);
        const hasSavedLocation = Number.isFinite(latitude) && Number.isFinite(longitude) && latitude !== 0 && longitude !== 0;

        if (hasSavedLocation) {
            setPin(latitude, longitude, true);
            statusElement.textContent = `${activeForm.dataset.purokName} is pinned. Click the map or drag the marker to move it.`;
        } else {
            setPin(defaultCenter.lat, defaultCenter.lng, false);
            statusElement.textContent = `${activeForm.dataset.purokName} has no location pinned yet. Click the map to add one.`;
        }
    }

    async function initializeMap() {
        if (!mapElement || typeof google === 'undefined' || !google.maps || !google.maps.importLibrary) {
            window.setTimeout(initializeMap, 250);
            return;
        }

        const mapsLibrary = await google.maps.importLibrary('maps');
        const markerLibrary = await google.maps.importLibrary('marker');
        MapConstructor = mapsLibrary.Map;
        MarkerConstructor = markerLibrary.Marker;

        map = new MapConstructor(mapElement, {
            center: defaultCenter,
            zoom: 15,
            mapTypeId: 'roadmap',
            streetViewControl: false,
            fullscreenControl: true,
        });

        map.addListener('click', function (event) {
            if (!activeForm || !event.latLng) return;
            setPin(event.latLng.lat(), event.latLng.lng(), true);
            updateCoordinates(event.latLng.lat(), event.latLng.lng());
        });

        locationSelect.addEventListener('change', loadSelectedPurok);
        document.querySelectorAll('.pick-purok-location').forEach(function (button) {
            button.addEventListener('click', function () {
                locationSelect.value = button.dataset.purokId;
                loadSelectedPurok();
                mapElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    }

    initializeMap();
});
</script>
@endsection