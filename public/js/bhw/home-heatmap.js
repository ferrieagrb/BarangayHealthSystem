(function () {
    'use strict';

    window.initBhwHomeMap = function () {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.initBhwHomeMap, { once: true });
            return;
        }
        const element = document.getElementById('bhwCitizenHeatmap');
        if (!element || element.dataset.initialized) return;
        if (!window.L || !window.DiagnosisHeatmap?.addToLeaflet) {
            window.bhwHeatmapLoadError();
            return;
        }
        element.dataset.initialized = 'true';
        const heat = window.DiagnosisHeatmap;
        const areas = JSON.parse(document.getElementById('bhwHeatmapData').textContent).map(area => ({
            ...area,
            location: { lat: area.lat, lng: area.lng },
            records_count: area.weight
        }));
        const map = L.map(element, { zoomAnimation: false }).setView([14.0668, 120.8531], 15);
        heat.addBasemap(map, 'bhwHeatmap');
        const boundaries = L.layerGroup().addTo(map);
        const bounds = L.latLngBounds([]);
        let mappedDiagnoses = false;

        areas.forEach(area => {
            const popup = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = area.label;
            const count = document.createElement('div');
            count.textContent = `${area.count} diagnoses recorded in the last 7 days`;
            popup.append(title, count);

            if (heat.polygons(area.boundary).length) {
                const outline = L.geoJSON(area.boundary, {
                    style: {
                        color: area.type === 'purok' ? '#1e293b' : '#475569',
                        weight: area.type === 'purok' ? 2 : 1,
                        opacity: 0.8, fillOpacity: 0.001
                    }
                }).bindPopup(popup).addTo(boundaries);
                bounds.extend(outline.getBounds());
            }
            const center = heat.location(area);
            if (center) {
                bounds.extend([center.lat, center.lng]);
                if (Number(area.weight) > 0) mappedDiagnoses = true;
                if (!heat.polygons(area.boundary).length) {
                    L.circle([center.lat, center.lng], {
                        radius: 70, stroke: false, fillOpacity: 0
                    }).bindPopup(popup).addTo(map);
                }
            }
        });
        if (bounds.isValid()) map.fitBounds(bounds, { padding: [60, 60], maxZoom: 16 });
        heat.addToLeaflet(map, areas, boundaries, 'bhwHeatmap');
        document.getElementById('bhwHeatmapStatus').textContent = mappedDiagnoses ? ''
            : (areas.length ? 'No mapped diagnoses were recorded in the last 7 days. Saved boundaries remain visible.'
                : 'No saved area locations are available. Add Purok pins or boundaries in Demographics.');
    };

    window.bhwHeatmapLoadError = function () {
        const status = document.getElementById('bhwHeatmapStatus');
        if (status) status.textContent = 'The heatmap could not load. Check your internet connection and reload the page.';
    };
})();
