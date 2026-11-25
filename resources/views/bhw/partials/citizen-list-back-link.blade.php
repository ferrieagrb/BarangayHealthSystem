@php
    $listFilters = request()->query('list', []);
    $listFilters = is_array($listFilters) ? \Illuminate\Support\Arr::only($listFilters, [
        'search', 'purok', 'subgroup', 'age', 'age_group', 'sort', 'filter_sex',
        'filter_housing', 'filter_health', 'health_filter_vaccine', 'filter_deceased',
        'filter_needs_review', 'page',
    ]) : [];
@endphp
<a href="{{ route('citizenlist', $listFilters) }}" class="btn-secondary" style="text-decoration: none !important;" data-ui-action="secondary" data-citizen-list-back>&larr; Back</a>
