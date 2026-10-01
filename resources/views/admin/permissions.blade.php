@extends('templates.admin')

@section('content')
<div class="page-top" style="margin-bottom: 20px;">
    <h1>BHW User Permissions Management</h1>
    <p>Configure Read-Only or Read/Write access for each BHW feature tab.</p>
</div>

@if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #e5e7eb; color: #374151;">
                <th style="padding: 12px;">BHW User</th>
                @foreach($features as $key => $label)
                    <th style="padding: 12px; text-align: center;">{{ $label }}</th>
                @endforeach
                <th style="padding: 12px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bhwUsers as $bhw)
                <form action="{{ route('admin.permissions.update', $bhw->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px;">
                            <strong>{{ $bhw->name }}</strong><br>
                            <small style="color: #6b7280;">{{ $bhw->email }}</small>
                        </td>

                        @foreach($features as $featureKey => $label)
                            @php
                                $userPerm = $bhw->permissions->where('feature', $featureKey)->first();
                                $currentAccess = $userPerm ? $userPerm->access : 'none';
                            @endphp
                            <td style="padding: 12px; text-align: center;">
                                <select name="permissions[{{ $featureKey }}]" style="padding: 6px 10px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 13px;">
                                    <option value="none" {{ $currentAccess === 'none' ? 'selected' : '' }}>No Access</option>
                                    <option value="read" {{ $currentAccess === 'read' ? 'selected' : '' }}>Read Only</option>
                                    <option value="write" {{ $currentAccess === 'write' ? 'selected' : '' }}>Read / Write</option>
                                </select>
                            </td>
                        @endforeach

                        <td style="padding: 12px; text-align: right;">
                            <button type="submit" style="padding: 6px 14px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Save</button>
                        </td>
                    </tr>
                </form>
            @empty
                <tr>
                    <td colspan="{{ count($features) + 2 }}" style="padding: 20px; text-align: center; color: #6b7280;">
                        No BHW users found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection