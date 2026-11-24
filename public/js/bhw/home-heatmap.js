(function () {
    'use strict';

    window.initBhwHomeMap = function () {
        // The Maps API can finish before the dashboard markup has been parsed.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.initBhwHomeMap, { once: true });
            return;
        }
        const element = document.getElementById('bhwCitizenHeatmap');
        if (!element || element.dataset.initialized || !window.google?.maps) return;
        element.dataset.initialized = 'true';
        const heat = window.DiagnosisHeatmap;
        const areas = JSON.parse(document.getElementById('bhwHeatmapData').textContent);
        const map = new google.maps.Map(element, {
            zoom: 15, center: { lat: 14.0668, lng: 120.8531 },
            mapTypeId: 'roadmap', disableDefaultUI: true, zoomControl: true,
            styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] },
                { featureType: 'all', stylers: [{ saturation: -65 }] }]
        });
        const bounds = new google.maps.LatLngBounds();
        const infoWindow = new google.maps.InfoWindow();
        const outlines = [];
        const points = [];

        function showArea(area, position) {
            const content = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = area.label;
            const count = document.createElement('div');
            count.textContent = `${area.count} diagnoses recorded in the last 7 days`;
            content.append(title, count);
            infoWindow.setContent(content);
            infoWindow.setPosition(position);
            infoWindow.open(map);
        }

        areas.forEach(area => {
            const center = heat.location({ location: { lat: area.lat, lng: area.lng }, boundary: area.boundary });
            if (center) {
                bounds.extend(center);
                if (area.weight > 0) points.push({ ...center, weight: Number(area.weight) });
            }
            heat.polygons(area.boundary).forEach(rings => {
                const paths = rings.map(ring => ring.map(pair => ({ lat: pair[1], lng: pair[0] })));
                paths.forEach(path => path.forEach(vertex => bounds.extend(vertex)));
                const outline = new google.maps.Polygon({
                    paths, map, strokeColor: area.type === 'purok' ? '#1e293b' : '#475569',
                    strokeWeight: area.type === 'purok' ? 2 : 1, strokeOpacity: 0.8,
                    fillOpacity: 0.001, zIndex: area.type === 'purok' ? 10 : 11
                });
                outline.addListener('click', event => showArea(area, event.latLng));
                outlines.push(outline);
            });
            if (center && !area.boundary) {
                // An invisible hit target preserves area details without point markers.
                new google.maps.Circle({
                    map, center, radius: 70, strokeOpacity: 0, fillOpacity: 0,
                }).addListener('click', event => showArea(area, event.latLng));
            }
        });

        const maximum = Math.max(1, ...points.map(point => point.weight));
        class HeatOverlay extends google.maps.OverlayView {
            constructor() {
                super();
                this.canvas = document.createElement('canvas');
                this.canvas.className = 'diagnosis-heatmap-canvas';
                this.canvas.setAttribute('aria-hidden', 'true');
            }
            onAdd() { this.getPanes().overlayLayer.append(this.canvas); }
            onRemove() { cancelAnimationFrame(this.frame); this.canvas.remove(); }
            draw() {
                cancelAnimationFrame(this.frame);
                this.frame = requestAnimationFrame(() => this.paint());
            }
            paint() {
                const projection = this.getProjection();
                if (!projection || !this.getMap()) return;
                const width = element.clientWidth, height = element.clientHeight;
                const origin = projection.fromContainerPixelToLatLng(new google.maps.Point(0, 0));
                const topLeft = projection.fromLatLngToDivPixel(origin);
                this.canvas.style.left = topLeft.x + 'px';
                this.canvas.style.top = topLeft.y + 'px';
                const projected = points.map(point => {
                    const pixel = projection.fromLatLngToDivPixel(new google.maps.LatLng(point.lat, point.lng));
                    return { x: pixel.x - topLeft.x, y: pixel.y - topLeft.y, weight: point.weight };
                });
                heat.render(this.canvas, width, height, projected,
                    settings.radius / heat.metersPerPixel(map.getCenter().lat(), map.getZoom()), maximum);
            }
        }
        const overlay = new HeatOverlay();
        const settings = heat.controls('bhwHeatmap', () => overlay.draw(), visible => {
            outlines.forEach(outline => outline.setMap(visible ? map : null));
        });
        overlay.setMap(map);
        map.addListener('idle', () => overlay.draw());
        const resize = new ResizeObserver(() => overlay.draw());
        resize.observe(element);
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds, 60);
            google.maps.event.addListenerOnce(map, 'idle', () => {
                if (map.getZoom() > 16) map.setZoom(16);
            });
        }
        document.getElementById('bhwHeatmapStatus').textContent = points.length
            ? ''
            : (areas.length ? 'No mapped diagnoses were recorded in the last 7 days. Saved boundaries remain visible.'
                : 'No saved area locations are available. Add Purok pins or boundaries in Demographics.');
    };

    window.bhwHeatmapLoadError = function () {
        const show = () => {
            const status = document.getElementById('bhwHeatmapStatus');
            if (status) status.textContent = 'The heatmap could not load. Check the map connection and Google Maps configuration.';
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', show, { once: true });
        else show();
    };
    window.gm_authFailure = window.bhwHeatmapLoadError;
})();
