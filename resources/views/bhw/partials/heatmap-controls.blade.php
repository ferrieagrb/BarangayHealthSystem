<div class="diagnosis-heatmap-controls">
    <div class="diagnosis-heatmap-scale" aria-label="Relative diagnosis concentration: blue is lower, red is higher">
        <span>Low</span><span class="diagnosis-heatmap-scale-bar" aria-hidden="true"></span><span>High concentration</span>
    </div>
    <label><input type="checkbox" id="{{ $prefix }}Boundaries" checked> Show saved boundaries</label>
    <label><input type="checkbox" id="{{ $prefix }}Dots" checked> Diagnosis dots</label>
    <label for="{{ $prefix }}Radius">Heat radius
        <input type="range" id="{{ $prefix }}Radius" min="100" max="800" step="50" value="300">
        <output id="{{ $prefix }}RadiusValue" for="{{ $prefix }}Radius">300 m</output>
    </label>
</div>
<div class="diagnosis-type-key">
    <strong>Diagnosis types</strong>
    <div id="{{ $prefix }}DiagnosisLegend" class="diagnosis-type-legend" aria-label="Diagnosis dot colors"></div>
</div>
<p id="{{ $prefix }}Status" class="diagnosis-heatmap-status" role="status">Loading heatmap…</p>
