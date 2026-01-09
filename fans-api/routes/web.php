<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingLinkController;
use Illuminate\Support\Facades\Artisan;

// Remove the default welcome route
// Route::get('/', function () {
//     return view('welcome');
// });

// Add the tracking route
Route::get('/track/{slug}', [TrackingLinkController::class, 'track'])->name('tracking.link');

// Add the creator profile route
Route::get('/creator/{username}', function ($username) {
    // Get the tracking link ID from the session
    $trackingLinkId = session('tracking_link_id');
    
    // Redirect to the frontend user profile page with the tracking link ID
    $url = config('app.frontend_url') . '/' . $username . '/posts';
    if ($trackingLinkId) {
        $url .= '?tracking_link_id=' . $trackingLinkId;
    }
    
    return redirect($url);
})->name('creator.profile');



Route::get('/run-migrations', function () {
    if (request('secret') !== env('MIGRATION_SECRET')) {
        abort(403, 'Unauthorized');
    }

    Artisan::call('migrate', ['--force' => true]);

    return 'Migrations complete.';
});
