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

    return view('landing', compact('recentEvents', 'recentAnnouncements'));
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
| BHW ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/home', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') {
        abort(403);
    }

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

Route::get('/citizenlist', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->index(request());

})->name('citizenlist');

Route::get('/addcitizen', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return view('bhw.addcitizen');

})->name('citizen.add');

Route::post('/citizen-store', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->store(request());

})->name('citizen.store');

Route::get('/citizen/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->show($id);

})->name('citizen.show');

Route::get('/citizendetails/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->citizendetails($id);

})->name('citizendetails');

Route::delete('/citizen/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->destroy($id);

})->name('citizen.delete');

Route::put('/citizen/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->update(request(), $id);

})->name('citizen.update');

/*
|--------------------------------------------------------------------------
| HEALTH RECORDS
|--------------------------------------------------------------------------
*/

Route::get('/healthrecord', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->healthIndex();

})->name('healthrecord');

Route::post('/health-record/store', [HealthRecordController::class, 'store'])
    ->name('health.record.store');

Route::get('/health-record/{id}', [HealthRecordController::class, 'show'])
    ->name('health.record.show');

/*
|--------------------------------------------------------------------------
| SUPPLIES
|--------------------------------------------------------------------------
*/

Route::get('/supplies', function (\Illuminate\Http\Request $request) {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(SupplyController::class)->index($request);

});

Route::get('/supplies/create', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return view('bhw.supply_create');

})->name('supplies.create');

Route::post('/supplies/store', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(SupplyController::class)->store(request());

})->name('supplies.store');

Route::post('/supplies/deposit', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(SupplyController::class)->deposit(request());

})->name('supplies.deposit');

Route::post('/supplies/release', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(SupplyController::class)->release(request());

})->name('supplies.release');

/*
|--------------------------------------------------------------------------
| ANNOUNCEMENTS
|--------------------------------------------------------------------------
*/

Route::get('/announcements', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(AnnouncementController::class)->index();

})->name('announcements');

Route::post('/announcements/store', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(AnnouncementController::class)->store(request());

})->name('announcements.store');

Route::get('/announcements/create', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return view('bhw.announcement_create');

})->name('announcements.create');

/*
|--------------------------------------------------------------------------
| LOGS
|--------------------------------------------------------------------------
*/

Route::get('/logs', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(LogController::class)->index();

})->name('logs.index');

/*
|--------------------------------------------------------------------------
| CALENDAR / EVENTS
|--------------------------------------------------------------------------
*/

Route::get('/calendar', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(EventController::class)->index();

});

Route::get('/events', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(EventController::class)->fetchEvents();

});

Route::post('/events', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(EventController::class)->storeEvent(request());

});

Route::delete('/events/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(EventController::class)->destroy($id);

});



Route::post('/vehicle-logs', [VehicleLogController::class, 'store'])
    ->name('vehicle.logs.store');

/*
|--------------------------------------------------------------------------
| QR SCANNER
|--------------------------------------------------------------------------
*/

Route::get('/qr-scanner', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(QRScannerController::class)->index();

})->name('qr.scanner');




/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/




Route::get('/admin/home', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return view('admin.admin_home');

})->name('admin.home');


/*
|--------------------------
| ADMIN USERS (FIXED)
|--------------------------
*/

Route::get('/admin/users', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->index(request());

})->name('admin.users');


Route::get('/admin/users/create', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->create();

})->name('admin.users.create');


Route::post('/admin/users', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->store(request());

})->name('admin.users.store');


Route::get('/admin/users/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->show($id);

})->name('admin.users.view');


Route::delete('/admin/users/{id}', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->destroy($id);

})->name('admin.users.delete');



Route::get('/admin/analytics', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminAnalytics::class)->index();

})->name('admin.analytics');


Route::get('/admin/logs', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminLogController::class)->index();

})->name('admin.logs');


Route::get('/admin/settings', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminSettings::class)->index();

})->name('admin.settings');

   




/*
|--------------------------------------------------------------------------
| NURSE ROUTES
|--------------------------------------------------------------------------
*/


Route::get('/nurse/home', function () {
    if (!Auth::check() || Auth::user()->role !== 'nurse') abort(403);
    return view('nurse.home');
})->name('nurse.home');



/*
|--------------------------------------------------------------------------
| REFERRAL ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
Route::get('/referrals/create', [ReferralController::class, 'create'])->name('referrals.create');
Route::post('/referrals/store', [ReferralController::class, 'store'])->name('referrals.store');
Route::patch('/referrals/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');
Route::get('/referrals/{referral}/download', [ReferralController::class, 'download'])->name('referrals.download');

Route::get('/citizen/{id}/records', [HealthRecordController::class, 'show'])->name('citizen.show');

Route::post('/vaccination/store', [HealthRecordController::class, 'storeVaccination'])->name('vaccination.store');