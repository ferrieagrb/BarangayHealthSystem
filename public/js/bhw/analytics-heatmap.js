(function () {
    'use strict';
    const heat = window.DiagnosisHeatmap;

    heat.addBasemap = function (map, prefix) {
        const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
        const satellite = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Imagery &copy; <a href="https://doc.arcgis.com/en/data-appliance/latest/imagery-elevation/world-imagery.htm">Esri</a>, Maxar, Earthstar Geographics, and the GIS User Community'
        });
        const warn = () => {
            const status = document.getElementById(prefix + 'Status');
            if (status) status.textContent = 'The background map could not load. Try the other map view or reload the page.';
        };
        streets.on('tileerror', warn);
        satellite.on('tileerror', warn);
        satellite.addTo(map);
        L.control.layers({ 'Satellite': satellite, 'Street map': streets }, null, { collapsed: false }).addTo(map);
        L.control.scale({ imperial: false }).addTo(map);
    };

    heat.addDiagnosisDots = function (map, areas, prefix) {
        const dots = L.layerGroup().addTo(map);
        const types = new Map();
        areas.forEach(area => {
            const center = heat.location(area);
            if (!center) return;
            const entries = (area.diagnoses || []).filter(entry => Number(entry.count) > 0)
                .sort((a, b) => a.diagnosis.localeCompare(b.diagnosis));
            if (!entries.length) return;
            const group = document.createElement('div');
            group.className = 'diagnosis-dot-group';
            entries.forEach(entry => {
                const name = entry.diagnosis.trim();
                types.set(name.toLowerCase(), name);
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'diagnosis-dot';
                dot.style.backgroundColor = heat.diagnosisColor(name);
                dot.title = `${name}: ${entry.count} diagnosis records`;
                dot.setAttribute('aria-label', `${area.label || area.name}: ${dot.title}`);
                L.DomEvent.disableClickPropagation(dot);
                dot.addEventListener('click', () => {
                    const popup = document.createElement('div');
                    const title = document.createElement('strong');
                    title.textContent = name;
                    const details = document.createElement('div');
                    details.textContent = `${area.label || area.name}: ${entry.count} diagnosis records`;
                    const note = document.createElement('small');
                    note.textContent = 'Saved area location; not an individual household.';
                    popup.append(title, details, note);
                    L.popup().setLatLng([center.lat, center.lng]).setContent(popup).openOn(map);
                });
                group.append(dot);
            });
            L.marker([center.lat, center.lng], {
                icon: L.divIcon({ html: group, className: 'diagnosis-dot-marker', iconSize: [0, 0], iconAnchor: [0, 0] }),
                keyboard: false, zIndexOffset: 500
            }).addTo(dots);
        });
        const legend = document.getElementById(prefix + 'DiagnosisLegend');
        if (legend) {
            legend.replaceChildren();
            [...types.values()].sort((a, b) => a.localeCompare(b)).forEach(name => {
                const item = document.createElement('span');
                const swatch = document.createElement('i');
                swatch.style.backgroundColor = heat.diagnosisColor(name);
                swatch.setAttribute('aria-hidden', 'true');
                item.append(swatch, document.createTextNode(name));
                legend.append(item);
            });
            if (!types.size) legend.textContent = 'No diagnosis types are available for display in these areas.';
        }
        const toggle = document.getElementById(prefix + 'Dots');
        toggle?.addEventListener('change', () => {
            if (toggle.checked) dots.addTo(map);
            else map.removeLayer(dots);
        });
        return dots;
    };

    heat.addToLeaflet = function (map, areas, boundaries, prefix) {
        heat.addDiagnosisDots(map, areas, prefix);
        const points = areas.map(area => {
            const center = heat.location(area);
            return center ? { ...center, weight: Number(area.records_count) } : null;
        }).filter(point => point?.weight > 0);
        const maximum = Math.max(1, ...points.map(point => point.weight));
        const pane = map.createPane('diagnosisHeat');
        pane.style.zIndex = '350';
        pane.style.pointerEvents = 'none';
        const HeatLayer = L.Layer.extend({
            onAdd() {
                this.canvas = L.DomUtil.create('canvas', 'diagnosis-heatmap-canvas', pane);
                this.canvas.setAttribute('aria-hidden', 'true');
                this.redraw();
            },
            onRemove() { cancelAnimationFrame(this.frame); this.canvas.remove(); },
            getEvents() { return { move: this.redraw, zoomend: this.redraw, resize: this.redraw, viewreset: this.redraw }; },
            redraw() {
                cancelAnimationFrame(this.frame);
                this.frame = requestAnimationFrame(() => {
                    if (!this._map) return;
                    const origin = map.containerPointToLayerPoint([0, 0]);
                    L.DomUtil.setPosition(this.canvas, origin);
                    const size = map.getSize();
                    const projected = points.map(point => {
                        const pixel = map.latLngToContainerPoint([point.lat, point.lng]);
                        return { x: pixel.x, y: pixel.y, weight: point.weight };
                    });
                    heat.render(this.canvas, size.x, size.y, projected,
                        settings.radius / heat.metersPerPixel(map.getCenter().lat, map.getZoom()), maximum);
                });
            }
        });
        const layer = new HeatLayer();
        const settings = heat.controls(prefix, () => layer.redraw(), visible => {
            if (visible) boundaries.addTo(map);
            else map.removeLayer(boundaries);
        });
        layer.addTo(map);
        const observer = new ResizeObserver(() => map.invalidateSize());
        observer.observe(map.getContainer());
        map.once('unload', () => observer.disconnect());
        document.getElementById(prefix + 'Status').textContent = points.length ? '' : 'No mapped diagnoses match these filters.';
        return layer;
    };
})();
