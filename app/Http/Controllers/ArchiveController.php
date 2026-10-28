<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\citizens;
use App\Models\Event;
use App\Models\Family;
use App\Models\HealthRecord;
use App\Models\MaintenanceRecord;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\Supply;
use App\Models\User;
use App\Models\VaccinationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    private const TYPES = [
        'citizens' => ['label' => 'Citizens', 'model' => citizens::class],
        'families' => ['label' => 'Families', 'model' => Family::class],
        'health_records' => ['label' => 'Diagnoses', 'model' => HealthRecord::class],
        'maintenance_records' => ['label' => 'Maintenance Medicines', 'model' => MaintenanceRecord::class],
        'vaccination_records' => ['label' => 'Vaccinations', 'model' => VaccinationRecord::class],
        'announcements' => ['label' => 'Announcements', 'model' => Announcement::class],
        'supplies' => ['label' => 'Supplies', 'model' => Supply::class],
        'events' => ['label' => 'Events', 'model' => Event::class],
        'puroks' => ['label' => 'Puroks', 'model' => Purok::class],
        'subgroups' => ['label' => 'Subgroups', 'model' => PurokSubgroup::class],
        'categories' => ['label' => 'Categories', 'model' => Category::class],
    ];

    private const ROLE_TYPES = [
        'bhw' => ['citizens', 'families', 'health_records', 'maintenance_records', 'vaccination_records', 'announcements', 'supplies', 'events'],
        'admin' => ['puroks', 'subgroups', 'citizens', 'families', 'supplies', 'categories'],
    ];

    public function index(Request $request)
    {
        $isAdmin = $this->isAdmin();
        $allowed = $this->allowedTypes();

        $type = $request->query('type');
        if (!in_array($type, $allowed, true)) {
            $type = $allowed[0];
        }

        $tabs = [];
        foreach ($allowed as $key) {
            $tabs[$key] = [
                'label' => self::TYPES[$key]['label'],
                'count' => self::TYPES[$key]['model']::onlyTrashed()->count(),
            ];
        }

        $items = self::TYPES[$type]['model']::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('archive.index', [
            'layout' => $isAdmin ? 'templates.admin' : 'templates.layout',
            'restoreRoute' => $isAdmin ? 'admin.archive.restore' : 'archive.restore',
            'indexRoute' => $isAdmin ? 'admin.archive.index' : 'archive.index',
            'tabs' => $tabs,
            'type' => $type,
            'items' => $items,
            'labeler' => fn ($item) => $this->describe($type, $item),
        ]);
    }

    public function restore(string $type, int $id)
    {
        abort_unless(in_array($type, $this->allowedTypes(), true), 404);

        $record = self::TYPES[$type]['model']::onlyTrashed()->findOrFail($id);
        $record->restore();

        if ($record instanceof citizens) {
            // Bring back the citizen's login too, forcing a fresh password.
            User::onlyTrashed()
                ->where('role', User::ROLE_CITIZEN)
                ->where('citizen_id', $record->id)
                ->get()
                ->each(function (User $account) {
                    $account->restore();
                    $account->forceFill(['must_change_password' => true])->save();
                });
        }

        return redirect()->route($this->isAdmin() ? 'admin.archive.index' : 'archive.index', ['type' => $type])
            ->with('success', self::TYPES[$type]['label'] . ' record restored.');
    }

    private function isAdmin(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    private function allowedTypes(): array
    {
        return self::ROLE_TYPES[$this->isAdmin() ? 'admin' : 'bhw'];
    }

    private function describe(string $type, $item): array
    {
        return match ($type) {
            'citizens' => [trim($item->Citizen_FName . ' ' . $item->Citizen_LName), $item->Citizen_Purok],
            'families' => [$item->family_name, null],
            'health_records' => [$item->diagnosis, optional($item->citizen)->Citizen_FName . ' ' . optional($item->citizen)->Citizen_LName],
            'maintenance_records' => [$item->medicine_name, optional($item->citizen)->Citizen_FName . ' ' . optional($item->citizen)->Citizen_LName],
            'vaccination_records' => [$item->vaccine_name, optional($item->citizen)->Citizen_FName . ' ' . optional($item->citizen)->Citizen_LName],
            'announcements' => [$item->title, $item->category ?? null],
            'supplies' => [$item->name, $item->category ?? null],
            'events' => [$item->title, null],
            'puroks' => [$item->name, null],
            'subgroups' => [$item->name, optional($item->purok)->name],
            default => [$item->name ?? ('#' . $item->id), null],
        };
    }
}
