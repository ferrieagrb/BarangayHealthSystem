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
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('citizenhome'))->name('landing');

Route::get('/publiccalendar', function () {

    return app(EventController::class)->publicindex();

})->name('citizen.add');

Route::get('/publicevents', function () {

    return app(EventController::class)->fetchEvents();

})->name('publicevents');

Route::get('/publicannouncements', fn () => view('citizenannouncements'))->name('publicannouncements');

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

    return view('bhw.home');

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

Route::post('/health-record/store', function () {

    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    return app(CitizenController::class)->storeHealthRecord(request());

})->name('health.record.store');

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

Route::get('/admin/users', function () {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return view('admin.admin_users');

})->name('admin.users');


Route::get('/admin/users', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->index(request());

})->name('admin.users');


Route::get('/admin/users/create', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->create();

})->name('admin.users.create');


Route::get('/admin/users', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->store(request());

})->name('admin.users.store');


Route::get('/admin/users', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->show(request());

})->name('admin.users.view');



Route::get('/admin/users', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->destroy(request());

})->name('admin.users.destroy');



Route::get('/admin/analytics', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->index(request());

})->name('admin.analytics');


Route::get('/admin/logs', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->index(request());

})->name('admin.logs');


Route::get('/admin/settings', function ($id) {

    if (!Auth::check() || Auth::user()->role !== 'admin') abort(403);

    return app(AdminUserManagementController::class)->index(request());

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
| CITIZEN ROUTES
|--------------------------------------------------------------------------
*/

