@props(['action'])
@php($level = \App\Support\AuditActionLevel::forAction($action))
<span class="audit-level-badge audit-level-{{ $level }}">
    <span>{{ $action }}</span>
    <span class="audit-level-label">{{ ucfirst($level) }}</span>
</span>
