(function () {
    'use strict';
    const heat = window.DiagnosisHeatmap;

    heat.addToLeaflet = function (map, areas, boundaries, prefix) {
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
