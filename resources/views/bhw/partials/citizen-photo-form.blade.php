@if($canEdit && !(bool) $citizen->Citizen_IsDeceased)
    <form id="citizen-photo-form" method="POST" action="{{ route('citizen.photo.bhw-update', $citizen->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    </form>
@endif
