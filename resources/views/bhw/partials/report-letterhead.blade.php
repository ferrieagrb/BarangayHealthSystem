<table class="report-letterhead" role="presentation" style="width: 100%; min-width: 0; table-layout: auto; border-collapse: collapse; margin-bottom: 16px; background: #fff !important; border-bottom: 1px solid #cbd5e1;">
    <tbody><tr>
        <td style="width: 78px; padding: 8px 4px 12px; border: 0; vertical-align: middle; text-align: left; background: #fff !important;">
            <img src="{{ \App\Models\SystemSetting::logoDataUri() }}" alt="Barangay Amuyong logo" width="70" height="70" style="object-fit: contain;">
        </td>
        <td style="padding: 8px 12px 12px; border: 0; vertical-align: middle; text-align: center; background: #fff !important;">
            <div style="color: #475569 !important; font-size: 9px;">REPUBLIC OF THE PHILIPPINES</div>
            <div style="color: #07573e !important; font-size: 20px; font-weight: bold; line-height: 1.3;">BARANGAY AMUYONG</div>
            <div style="color: #334155 !important; font-size: 11px;">Municipality of Alfonso | Province of Cavite</div>
            <div style="color: #475569 !important; font-size: 10px;">Barangay Health Management System</div>
        </td>
        <td style="width: 78px; padding: 8px 4px 12px; border: 0; vertical-align: middle; text-align: right; background: #fff !important;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/alfonso.png'))) }}" alt="Municipality of Alfonso logo" width="70" height="70" style="object-fit: contain;">
        </td>
    </tr></tbody>
</table>
