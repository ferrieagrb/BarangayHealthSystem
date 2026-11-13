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

    // Legacy Google Maps purok picker; its elements no longer exist on this page.
    if (mapElement) initializeMap();
});
