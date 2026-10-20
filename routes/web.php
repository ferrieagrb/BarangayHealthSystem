<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\CitizenSupplyController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\QRScannerController;
use App\Http\Controllers\AdminAnalytics;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\AdminSettings;
use App\Http\Controllers\VehicleLogController;
use App\Http\Controllers\AdminUserManagementController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\AdminPermissionController;
use App\Http\Controllers\BhwHomeController;

use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\Event;
use App\Models\Supply;
use App\Models\Referral;
use App\Models\Announcement;
use App\Models\VaccinationRecord;
use App\Models\MedicationRecord;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\SuperAdmin\UserAnalyticsController;
use App\Http\Controllers\SuperAdmin\WebsitePerformanceController;
use App\Http\Controllers\SuperAdmin\SystemAnalyticsController;
use App\Http\Controllers\SuperAdmin\AdminAuditLogController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\UserPreferenceController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Public QR landing page. It never exposes private medical records.
Route::get('/health-id/{token}', [QRScannerController::class, 'publicHealthId'])
    ->whereUuid('token')
    ->name('health-id.public');

Route::get('/', function () {
    $recentEvents = Event::latest()->take(5)->get();
    $recentAnnouncements = Announcement::with('images')
        ->whereHas('images')
        ->latest()
        ->take(5)
        ->get();

    return view('citizenhome', compact('recentEvents', 'recentAnnouncements'));
})->name('landing');

Route::get('/publiccalendar', function () {
    return app(EventController::class)->publicindex();
})->name('publiccalendar');

Route::get('/publicevents', function () {
    return app(EventController::class)->fetchEvents();
})->name('publicevents');

Route::get('/publicannouncements', [AnnouncementController::class, 'publicIndex'])->name('public.announcements');
Route::get('/publicannouncements/{announcement}', [AnnouncementController::class, 'publicShow'])->name('public.announcements.show');

Route::get('/contactdirectory', fn () => view('citizencontactdirectory'))->name('contactdirectory');

Route::get('/login', fn () => view('login'))->name('login');

Route::post('/login', [UserController::class, 'login'])->name('login.submit');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| CENTRAL REDIRECTION HUB (Post-Login)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    return match (strtolower(trim($user->role))) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        'admin' => redirect()->route('admin.home'),
        'bhw' => redirect()->route('home'),
        'citizen' => redirect()->route('citizen.dashboard'),
        default => redirect()->route('landing'),
    };
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| CITIZEN ROUTES (Placed first to avoid wildcard URI collisions)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'password.changed'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (!$user->isCitizen() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $announcements = Announcement::with('images')->latest()->take(5)->get();
        $citizen = null;
        if ($user->citizen_id) {
            $citizen = citizens::with(['vaccinations', 'medications', 'healthRecords'])
                ->where('id', $user->citizen_id)->first();
        }

        $qrImage = $citizen
            ? app(\App\Http\Controllers\CitizenController::class)
                ->makeCitizenQrImage($citizen)
            : null;

        return view('citizen.dashboard', compact(
            'announcements',
            'citizen',
            'qrImage'
        ));
    })->name('dashboard');

    // Citizen Supplies Overview
    Route::get('/supplies', [CitizenSupplyController::class, 'index'])->name('supplies');

    // Citizen Calendar
    Route::get('/calendar', function () {
        $user = auth()->user();

        if (!$user->isCitizen() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('citizen.calendar');
    })->name('calendar');

    // Citizen Vaccination Route
    Route::get('/vaccination', function () {
        $user = auth()->user();

        if (!$user->isCitizen() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('citizen.vaccination');
    })->name('vaccination');

    // Citizen Announcements Route
    Route::get('/announcements', function () {
        $user = auth()->user();

        if (!$user->isCitizen() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $announcements = Announcement::with('images')->latest()->get();

        return view('citizen.announcements', compact('announcements'));
    })->name('announcements');

    //  STATIC ROUTE PLACED SAFELY AT THE TOP OF THE PREFIX GROUP
    Route::get('/my-health-card', [CitizenController::class, 'citizenViewECard'])->name('ecard');
    Route::get('/settings', [UserPreferenceController::class, 'edit'])->name('settings');


});



/*
|--------------------------------------------------------------------------
| BHW ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:bhw', 'password.changed'])->group(function () {

    Route::get('/home', [BhwHomeController::class, 'index'])->name('home');

    Route::get('/citizenlist', fn () => app(CitizenController::class)->index(request()))->name('citizenlist');
    Route::get('/addcitizen', function () {
        $puroks = \App\Models\Purok::with('subgroups')->get();
        $families = \App\Models\Family::with(['purok', 'subgroup'])->orderBy('family_name')->get();

        return view('bhw.addcitizen', compact('puroks', 'families'));
    })->name('citizen.add');

    Route::post('/citizen-store', fn () => app(CitizenController::class)->store(request()))->name('citizen.store');

    // Electronic Health & Vaccination Card Routes (NEW)
    Route::get('/citizen/{id}/e-card', [CitizenController::class, 'showElectronicCard'])->name('bhw.citizen.ecard');
    Route::post('/citizen/{id}/vaccination', [CitizenController::class, 'storeVaccination'])->name('citizen.vaccination.store');
    Route::post('/citizen/{id}/medication', [CitizenController::class, 'storeMedication'])->name('citizen.medication.store');

    Route::get('/citizen/{id}', fn ($id) => app(CitizenController::class)->show($id))->name('citizen.show');
    Route::get('/citizendetails/{id}', fn ($id) => app(CitizenController::class)->citizendetails($id))->name('citizendetails');
    Route::delete('/citizen/{id}', fn ($id) => app(CitizenController::class)->destroy($id))->name('citizen.delete');
    Route::put('/citizen/{id}', fn ($id) => app(CitizenController::class)->update(request(), $id))->name('citizen.update');


    // Health Records
    Route::get('/healthrecord', [CitizenController::class, 'healthRecordIndex'])->name('healthrecord');
    Route::post('/health-record/store', [CitizenController::class, 'storeHealthRecord'])->name('health.record.store');
    Route::post('/health-record/maintenance', [CitizenController::class, 'storeMaintenance'])->name('health.maintenance.store');
    Route::get('/health-record/{id}', [CitizenController::class, 'show'])->name('health.record.show');

    // Supplies
    Route::get('/supplies', fn (\Illuminate\Http\Request $request) => app(SupplyController::class)->index($request))->name('supplies.index');
    Route::get('/supplies/create', [SupplyController::class, 'create'])->name('supplies.create');
    Route::post('/supplies/store', fn () => app(SupplyController::class)->store(request()))->name('supplies.store');
    Route::post('/supplies/deposit', fn () => app(SupplyController::class)->deposit(request()))->name('supplies.deposit');
    Route::post('/supplies/release', fn () => app(SupplyController::class)->release(request()))->name('supplies.release');
    Route::post('/supplies/batch-release', [SupplyController::class, 'batchRelease'])->name('supplies.batch.release');
    Route::get('/supplies/batch-release/{eventId}', [SupplyController::class, 'showBatchRelease'])->name('supplies.batch.release.show');
    Route::post('/supplies/batch-release/{id}/claim', [SupplyController::class, 'claimRelease'])->name('supplies.batch.release.claim');
    Route::delete('/supplies/batch/{id}', [SupplyController::class, 'destroy'])->name('supplies.destroy');
    Route::post('/supplies/item/withdraw', [SupplyController::class, 'withdrawItem'])->name('supplies.item.withdraw');
    Route::post('/supplies/batch/withdraw', [SupplyController::class, 'withdrawBatch'])->name('supplies.batch.withdraw');

    // Announcements
    Route::get('/announcements', fn () => app(AnnouncementController::class)->index())->name('announcements');
    Route::post('/announcements/store', fn () => app(AnnouncementController::class)->store(request()))->name('announcements.store');
    Route::get('/announcements/create', fn () => view('bhw.announcement_create'))->name('announcements.create');

    // Logs
    Route::get('/logs', fn () => app(LogController::class)->index())->name('logs.index');

    // Calendar / Events
    Route::get('/calendar', fn () => app(EventController::class)->index());
    Route::get('/events', fn () => app(EventController::class)->fetchEvents());
    Route::post('/events', fn () => app(EventController::class)->storeEvent(request()));
    Route::delete('/events/{id}', fn ($id) => app(EventController::class)->destroy($id));

    Route::post('/vehicle-logs', [VehicleLogController::class, 'store'])->name('vehicle.logs.store');

    // QR Scanner (BHW-only routes; protected by this group's auth + role middleware).
    Route::get('/qr-scanner', [QRScannerController::class, 'index'])->name('qr.scanner');
    Route::get('/qr-scanner/lookup/{token}', [QRScannerController::class, 'lookup'])
        ->whereUuid('token')
        ->name('qr.scanner.lookup');

    // Legacy authenticated route retained for existing QR links/bookmarks.
    Route::get('/citizen/qr/{token}', [CitizenController::class, 'showElectronicCardByToken'])
        ->whereUuid('token')
        ->name('bhw.citizen.qr');
    // Excel
    Route::post('/citizen-import', [CitizenController::class, 'import'])->name('citizen.import');

    Route::get('/citizen-export', [CitizenController::class,'export'])->name('citizen.export');
});

Route::middleware(['auth', 'password.changed'])->prefix('bhw')->name('bhw.')->group(function () {
    Route::get('/families', [FamilyController::class, 'index'])->name('families.index');
    Route::post('/families', [FamilyController::class, 'store'])->name('families.store');
    Route::post('/families/quick-store', [FamilyController::class, 'storeQuick'])->name('families.quick.store');
    Route::get('/families/{family}', [FamilyController::class, 'show'])->name('families.show');
    Route::post('/families/{family}/members', [FamilyController::class, 'addMember'])->name('families.members.add');
    Route::delete('/families/members/{citizenId}', [FamilyController::class, 'removeMember'])->name('families.members.remove');
    Route::put('/families/{family}', [FamilyController::class, 'update'])->name('families.update');

});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/home',[AdminSettings::class,'dashboard'])->name('home');
    Route::get('/analytics', fn () => app(AdminAnalytics::class)->index())->name('analytics');
    Route::get('/logs', fn () => app(AdminLogController::class)->index())->name('logs');
    Route::get('/settings', fn () => app(AdminSettings::class)->index())->name('settings');
    Route::get('/supplies', fn (\Illuminate\Http\Request $request) => app(SupplyController::class)->index($request))->name('supplies');
    Route::post('/supplies/deposit', [SupplyController::class, 'deposit'])->name('supplies.deposit');

    Route::get('/users', fn () => app(AdminUserManagementController::class)->index(request()))->name('users');
    Route::post('/users', fn () => app(AdminUserManagementController::class)->store(request()))->name('users.store');
    Route::put('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->update(request(),$id))->name('users.update');
    Route::delete('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->destroy($id))->name('users.delete');

    Route::get('/demographics',[AdminSettings::class,'demographicsIndex'])->name('demographics');
    Route::post('/demographics/purok',[AdminSettings::class,'storePurok'])->name('puroks.store');
    Route::delete('/demographics/purok/{id}',[AdminSettings::class,'destroyPurok'])->name('puroks.destroy');
    Route::put('/admin/puroks/{id}', [AdminSettings::class, 'updatePurok'])->name('puroks.update');

    Route::post('/purok-subgroups', [AdminSettings::class, 'storeSubgroup'])->name('subgroups.store');
    Route::put('/purok-subgroups/{id}', [AdminSettings::class, 'updateSubgroup'])->name('subgroups.update'); 
    Route::delete('/purok-subgroups/{id}', [AdminSettings::class, 'destroySubgroup'])->name('subgroups.destroy');

    Route::post('/categories', [SupplyController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [SupplyController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [SupplyController::class, 'destroyCategory'])->name('categories.destroy');



    Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
    Route::put('/permissions/{user}', [AdminPermissionController::class, 'update'])->name('permissions.update');

    Route::patch('/users/{id}/unlock', [AdminUserManagementController::class, 'unlockAccount'])->name('admin.users.unlock');
});

/*
|--------------------------------------------------------------------------
| REFERRAL ROUTES & GENERAL HEALTH ACTIONS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/referrals/create', [ReferralController::class, 'create'])->name('referrals.create');
    Route::post('/referrals/store', [ReferralController::class, 'store'])->name('referrals.store');
    Route::patch('/referrals/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');
    Route::get('/referrals/{referral}/download', [ReferralController::class, 'download'])->name('referrals.download');

    Route::get('/citizen/{id}/records', [CitizenController::class, 'show'])->name('citizen.show');
    Route::post('/vaccination/store', [CitizenController::class, 'storeVaccinationRecord'])->name('vaccination.store');
});

/*
|--------------------------------------------------------------------------
| SUPERADMIN ROUTES (Includes User Management)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:superadmin', 'password.changed'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');
    Route::get('/active-sessions-json', [SuperAdminController::class, 'getActiveSessionsData'])->name('sessions.json');

    Route::get('/users', fn () => app(AdminUserManagementController::class)->index(request()))->name('users');
    Route::get('/users/create', fn () => app(AdminUserManagementController::class)->create())->name('users.create');
    Route::get('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->show($id))->name('users.view');
    Route::post('/users', fn () => app(AdminUserManagementController::class)->store(request()))->name('users.store');
    Route::put('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->update(request(), $id))->name('users.update');
    Route::delete('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->destroy($id))->name('users.delete');

    Route::get('/user-analytics', [UserAnalyticsController::class, 'index']);
    Route::get('/website-performance', [WebsitePerformanceController::class, 'getPerformanceData']);

    // Renders the System Analytics view page
    Route::get('/system-analytics-page', function () {
        return view('superadmin.system-analytics');
    })->name('system.analytics.page');

    // JSON endpoint for the widgets
    Route::get('/system-analytics-data', [SystemAnalyticsController::class, 'index']);

    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs');

    Route::get('/system-settings', [SuperAdminController::class, 'systemSettings'])->name('system.settings');

    Route::patch('/users/{id}/unlock', [AdminUserManagementController::class, 'unlockAccount'])->name('admin.users.unlock');
});


Route::middleware(['auth', 'feature.write:supplies', 'password.changed'])->group(function () {
    Route::post('/supplies', [SupplyController::class, 'store']);
    Route::put('/supplies/{id}', [SupplyController::class, 'update']);
    Route::delete('/supplies/{id}', [SupplyController::class, 'destroy']);
});



// Routes that require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/force-password-change', [ForcePasswordChangeController::class, 'update'])->name('password.force-update');
    Route::get('/settings', [UserPreferenceController::class, 'edit'])->name('settings');
    Route::post('/settings/preferences', [UserPreferenceController::class, 'update'])->name('user.preferences.update');
    Route::put('/settings/preferences', [UserPreferenceController::class, 'update'])->name('user.preferences.update');
    Route::put('/settings/password', [UserPreferenceController::class, 'updatePassword'])->name('user.password.update');
});

// Standard signed email-verification flow for authenticated accounts.
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route(auth()->user()->isCitizen() ? 'citizen.settings' : 'settings')
            ->with('success', 'Your email address has been verified.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('success', 'Your email address is already verified.');
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'A verification link has been sent to your email address.');
    })->middleware('throttle:6,1')->name('verification.send');
});
