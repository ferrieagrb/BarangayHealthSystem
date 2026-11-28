(function (root) {
    'use strict';

    const colors = [[0, 55, 255], [0, 190, 255], [0, 210, 90], [255, 225, 0], [255, 120, 0], [225, 25, 30]];

    function diagnosisColor(diagnosis) {
        const key = String(diagnosis).trim().toLowerCase();
        let hash = 0;
        for (const character of key) hash = (Math.imul(hash, 31) + character.charCodeAt(0)) | 0;
        // Derive every color from its diagnosis name so new types work without
        // a maintained list and retain their color across maps and reloads.
        const value = hash >>> 0;
        return `hsl(${value % 360}, ${65 + ((value >>> 9) % 20)}%, ${40 + ((value >>> 17) % 12)}%)`;
    }

    function polygons(boundary) {
        const geometry = boundary?.type === 'Feature' ? boundary.geometry : boundary;
        if (geometry?.type === 'Polygon') return [geometry.coordinates];
        if (geometry?.type === 'MultiPolygon') return geometry.coordinates;
        return [];
    }

    function location(area) {
        const pin = area.location;
        if (pin && pin.lat !== null && pin.lng !== null && Number.isFinite(Number(pin.lat)) && Number.isFinite(Number(pin.lng))) {
            return { lat: Number(pin.lat), lng: Number(pin.lng) };
        }
        // Boundary-only areas still contribute, without inventing household locations.
        const ring = polygons(area.boundary)[0]?.[0];
        if (!ring?.length) return null;
        let twiceArea = 0, x = 0, y = 0;
        const origin = ring[0];
        for (let i = 0; i < ring.length; i++) {
            const a = [ring[i][0] - origin[0], ring[i][1] - origin[1]];
            const next = ring[(i + 1) % ring.length];
            const b = [next[0] - origin[0], next[1] - origin[1]];
            const cross = a[0] * b[1] - b[0] * a[1];
            twiceArea += cross;
            x += (a[0] + b[0]) * cross;
            y += (a[1] + b[1]) * cross;
        }
        if (Math.abs(twiceArea) < 1e-16) return { lat: origin[1], lng: origin[0] };
        return { lat: origin[1] + y / (3 * twiceArea), lng: origin[0] + x / (3 * twiceArea) };
    }

    function metersPerPixel(latitude, zoom) {
        return 156543.03392 * Math.cos(latitude * Math.PI / 180) / Math.pow(2, zoom);
    }

    // Add weighted Gaussian kernels BEFORE applying the palette. Overlapping cases
    // increase intensity instead of painting one area's colour over another.
    function density(width, height, points, radius) {
        const values = new Float32Array(width * height);
        if (!(radius > 0) || !Number.isFinite(radius)) return values;
        const tail = Math.exp(-4.5);
        points.forEach(({ x, y, weight }) => {
            if (!(weight > 0) || ![x, y, weight].every(Number.isFinite)) return;
            const left = Math.max(0, Math.ceil(x - radius));
            const right = Math.min(width - 1, Math.floor(x + radius));
            const top = Math.max(0, Math.ceil(y - radius));
            const bottom = Math.min(height - 1, Math.floor(y + radius));
            for (let row = top; row <= bottom; row++) {
                for (let col = left; col <= right; col++) {
                    const distance = ((col - x) ** 2 + (row - y) ** 2) / (radius * radius);
                    if (distance < 1) values[row * width + col] += weight * (Math.exp(-4.5 * distance) - tail) / (1 - tail);
                }
            }
        });
        return values;
    }

    function render(canvas, width, height, points, radius, maximum) {
        // A half-resolution field keeps pan/zoom responsive on mobile; the browser
        // interpolates the continuous colour surface to the full viewport.
        const scale = 2;
        canvas.width = Math.ceil(width / scale);
        canvas.height = Math.ceil(height / scale);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        const context = canvas.getContext('2d');
        if (!context || !canvas.width || !canvas.height) return;
        const values = density(canvas.width, canvas.height, points.map(p => ({ ...p, x: p.x / scale, y: p.y / scale })), radius / scale);
        const pixels = context.createImageData(canvas.width, canvas.height);
        values.forEach((value, index) => {
            if (value <= 0) return;
            const intensity = Math.min(1, value / Math.max(1, maximum));
            const stop = intensity * (colors.length - 1);
            const low = Math.min(colors.length - 2, Math.floor(stop));
            const mix = stop - low;
            const offset = index * 4;
            for (let channel = 0; channel < 3; channel++) {
                pixels.data[offset + channel] = Math.round(colors[low][channel] * (1 - mix) + colors[low + 1][channel] * mix);
            }
            pixels.data[offset + 3] = Math.round(215 * Math.min(1, intensity * 6));
        });
        context.putImageData(pixels, 0, 0);
    }

    function controls(prefix, redraw, setBoundaries) {
        const slider = document.getElementById(prefix + 'Radius');
        const toggle = document.getElementById(prefix + 'Boundaries');
        const output = document.getElementById(prefix + 'RadiusValue');
        const state = { radius: Number(slider.value) };
        slider.addEventListener('input', () => {
            state.radius = Number(slider.value);
            output.textContent = state.radius + ' m';
            redraw();
        });
        toggle.addEventListener('change', () => setBoundaries(toggle.checked));
        return state;
    }

    root.DiagnosisHeatmap = { polygons, location, metersPerPixel, density, render, controls, diagnosisColor };
})(typeof window === 'undefined' ? globalThis : window);
