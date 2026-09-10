<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\SupplyController;
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
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\SuperAdminController;

use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\Event;
use App\Models\Supply;
use App\Models\Referral;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $recentEvents = Event::latest()->take(5)->get();
    $recentAnnouncements = Announcement::latest()->take(5)->get();

    return view('citizenhome', compact('recentEvents', 'recentAnnouncements'));
})->name('landing');

Route::get('/publiccalendar', function () {
    return app(EventController::class)->publicindex();
})->name('publiccalendar');

Route::get('/publicevents', function () {
    return app(EventController::class)->fetchEvents();
})->name('publicevents');

Route::get('/publicannouncements', [AnnouncementController::class, 'publicIndex'])->name('public.announcements');

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

    if ($user->isSuperAdmin()) {
        return redirect()->route('superadmin.dashboard');
    } elseif ($user->isAdmin()) {
        return redirect()->route('admin.home');
    } elseif ($user->isBhw()) {
        return redirect()->route('home');
    } elseif ($user->isNurse()) {
        return redirect()->route('nurse.home');
    }

    return redirect()->route('landing');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| BHW ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:bhw'])->group(function () {

    Route::get('/home', function () {
        $totalCitizens = citizens::count();

        $popularDisease = HealthRecord::select('diagnosis', DB::raw('COUNT(*) as total'))
            ->groupBy('diagnosis')
            ->orderByDesc('total')
            ->first();

        $popularDisease = $popularDisease ? $popularDisease->diagnosis : 'N/A';

        $upcomingEvents = Event::whereDate('start', '>=', now())
            ->orderBy('start', 'asc')
            ->take(5)
            ->get();

        $upcomingEventsCount = Event::whereDate('start', '>=', now())->count();

        $lowStockSupplies = Supply::whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        $totalInventory = Supply::count();

        $recentReferrals = Referral::latest()
            ->take(5)
            ->get();

        $recentReferralsCount = Referral::count();

        return view('bhw.home', compact(
            'totalCitizens',
            'popularDisease',
            'upcomingEvents',
            'upcomingEventsCount',
            'lowStockSupplies',
            'totalInventory',
            'recentReferrals',
            'recentReferralsCount'
        ));
    })->name('home');

    Route::get('/citizenlist', fn () => app(CitizenController::class)->index(request()))->name('citizenlist');
    Route::get('/addcitizen', fn () => view('bhw.addcitizen'))->name('citizen.add');
    Route::post('/citizen-store', fn () => app(CitizenController::class)->store(request()))->name('citizen.store');
    Route::get('/citizen/{id}', fn ($id) => app(CitizenController::class)->show($id))->name('citizen.show');
    Route::get('/citizendetails/{id}', fn ($id) => app(CitizenController::class)->citizendetails($id))->name('citizendetails');
    Route::delete('/citizen/{id}', fn ($id) => app(CitizenController::class)->destroy($id))->name('citizen.delete');
    Route::put('/citizen/{id}', fn ($id) => app(CitizenController::class)->update(request(), $id))->name('citizen.update');

    // Health Records
    Route::get('/healthrecord', fn () => app(CitizenController::class)->healthIndex())->name('healthrecord');
    Route::post('/health-record/store', [HealthRecordController::class, 'store'])->name('health.record.store');
    Route::get('/health-record/{id}', [HealthRecordController::class, 'show'])->name('health.record.show');

    // Supplies
    Route::get('/supplies', fn (\Illuminate\Http\Request $request) => app(SupplyController::class)->index($request));
    Route::get('/supplies/create', fn () => view('bhw.supply_create'))->name('supplies.create');
    Route::post('/supplies/store', fn () => app(SupplyController::class)->store(request()))->name('supplies.store');
    Route::post('/supplies/deposit', fn () => app(SupplyController::class)->deposit(request()))->name('supplies.deposit');
    Route::post('/supplies/release', fn () => app(SupplyController::class)->release(request()))->name('supplies.release');

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

    // QR Scanner
    Route::get('/qr-scanner', fn () => app(QRScannerController::class)->index())->name('qr.scanner');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/home', fn () => view('admin.admin_home'))->name('home');
    Route::get('/analytics', fn () => app(AdminAnalytics::class)->index())->name('analytics');
    Route::get('/logs', fn () => app(AdminLogController::class)->index())->name('logs');
    Route::get('/settings', fn () => app(AdminSettings::class)->index())->name('settings');
});

/*
|--------------------------------------------------------------------------
| NURSE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:nurse'])->group(function () {
    Route::get('/nurse/home', fn () => view('nurse.home'))->name('nurse.home');
});

/*
|--------------------------------------------------------------------------
| REFERRAL ROUTES & GENERAL HEALTH ACTIONS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/referrals/create', [ReferralController::class, 'create'])->name('referrals.create');
    Route::post('/referrals/store', [ReferralController::class, 'store'])->name('referrals.store');
    Route::patch('/referrals/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');
    Route::get('/referrals/{referral}/download', [ReferralController::class, 'download'])->name('referrals.download');

    Route::get('/citizen/{id}/records', [HealthRecordController::class, 'show'])->name('citizen.show');
    Route::post('/vaccination/store', [HealthRecordController::class, 'storeVaccination'])->name('vaccination.store');
});

/*
|--------------------------------------------------------------------------
| SUPERADMIN ROUTES (Includes User Management)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');

    // Superadmin User Management Routes
    Route::get('/users', fn () => app(AdminUserManagementController::class)->index(request()))->name('users');
    Route::post('/users', fn () => app(AdminUserManagementController::class)->store(request()))->name('users.store');
    Route::put('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->update(request(), $id))->name('users.update');
    Route::delete('/users/{id}', fn ($id) => app(AdminUserManagementController::class)->destroy($id))->name('users.delete');
});