use App\Models\Event;

public function exportWord(Request $request)
{
    $type = $request->input('report_type');
    $startDate = $request->input('start_date', now()->subMonth());
    $endDate = $request->input('end_date', now());

    $fileName = 'Health_Center_' . ucfirst($type) . '_Report_' . date('Y-m-d') . '.doc';

    // Build HTML content structure that Microsoft Word interprets natively
    $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
    $html .= '<head><meta charset="utf-8"><title>Report</title></head>';
    $html .= '<body style="font-family: Arial, sans-serif; padding: 20px;">';
    $html .= '<h2 style="color: #2c3e50; text-align: center;">Barangay Health Center Management System</h2>';
    $html .= '<h3 style="text-align: center; color: #555;">' . ucwords(str_replace('_', ' ', $type)) . ' Report</h3>';
    $html .= '<p style="text-align: center; font-size: 12px; color: #777;">Generated on: ' . date('F d, Y') . '</p><hr style="border:0; border-top:1px solid #ccc;"/><br/>';

    if ($type === 'demographics') {
        $total = citizens::count();
        $kids = citizens::where('Citizen_Age', '<=', 17)->count();
        $adults = citizens::whereBetween('Citizen_Age', [18, 59])->count();
        $seniors = citizens::where('Citizen_Age', '>=', 60)->count();

        $html .= '<h4>Summary Statistics</h4><ul>';
        $html .= '<li>Total Citizens: ' . $total . '</li>';
        $html .= '<li>Children (0-17): ' . $kids . '</li>';
        $html .= '<li>Adults (18-59): ' . $adults . '</li>';
        $html .= '<li>Seniors (60+): ' . $seniors . '</li></ul>';
    } elseif ($type === 'health_logs' || $type === 'morbidity') {
        $records = HealthRecord::with('citizen')->whereBetween('record_date', [$startDate, $endDate])->get();
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; font-size: 12px;">';
        $html .= '<tr style="background:#f2f2f2;"><th>Date</th><th>Citizen Name</th><th>Diagnosis</th><th>Comments</th></tr>';
        foreach ($records as $r) {
            $name = ($r->citizen->Citizen_FName ?? '') . ' ' . ($r->citizen->Citizen_LName ?? '');
            $html .= '<tr><td>' . $r->record_date . '</td><td>' . $name . '</td><td>' . $r->diagnosis . '</td><td>' . $r->comments . '</td></tr>';
        }
        $html .= '</table>';
    } elseif ($type === 'inventory') {
        $items = Supply::select('name', 'category', 'min_stock', \DB::raw('SUM(quantity) as total_quantity'))->groupBy('name', 'category', 'min_stock')->get();
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; font-size: 12px;">';
        $html .= '<tr style="background:#f2f2f2;"><th>Item Name</th><th>Category</th><th>Total Quantity</th><th>Status</th></tr>';
        foreach ($items as $item) {
            $status = $item->total_quantity <= $item->min_stock ? 'Low Stock' : 'Normal';
            $html .= '<tr><td>' . $item->name . '</td><td>' . $item->category . '</td><td>' . $item->total_quantity . '</td><td>' . $status . '</td></tr>';
        }
        $html .= '</table>';
    } elseif ($type === 'trends') {
        $trends = HealthRecord::select('diagnosis', \DB::raw('count(*) as total'))->groupBy('diagnosis')->orderByDesc('total')->get();
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; font-size: 12px;">';
        $html .= '<tr style="background:#f2f2f2;"><th>Diagnosis / Trend</th><th>Total Cases Recorded</th></tr>';
        foreach ($trends as $t) {
            $html .= '<tr><td>' . $t->diagnosis . '</td><td>' . $t->total . '</td></tr>';
        }
        $html .= '</table>';
    } elseif ($type === 'events') {
        $events = Event::all();
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; font-size: 12px;">';
        $html .= '<tr style="background:#f2f2f2;"><th>Event Title</th><th>Date</th><th>Description</th></tr>';
        foreach ($events as $e) {
            $html .= '<tr><td>' . $e->title . '</td><td>' . $e->start . '</td><td>' . ($e->description ?? 'N/A') . '</td></tr>';
        }
        $html .= '</table>';
    }

    $html .= '</body></html>';

    return response($html, 200, [
        'Content-Type' => 'application/msword',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Cache-Control' => 'max-age=0',
    ]);
}
