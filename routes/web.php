<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::middleware(['auth', 'accepted'])->group(function() {
    Route::get('/', function () {
        return view('welcome');
    })->name('landing_page');

    Route::get('/home', 'HomeController@index')->name('home'); 

    Route::resource('schedule', 'ScheduleController')->except(['show']);

    Route::get('/schedule/show/{schedule?}', 'ScheduleController@show')->name('schedule.show');

    Route::resource('profile', 'ProfileController')->except(['update']);

    Route::post('profile/{id}', 'ProfileController@update')->name('profile.update');

    Route::resource('payment', 'PaymentController')->except(['show', 'destroy']);
    Route::get('/payment/show/{user?}', 'PaymentController@show')->name('payment.show');
    
    Route::get('/schedule/export', 'ScheduleController@export')->name('schedule.export');

    Route::get('tours', 'ToursController@index')->name('tours.index');

    Route::get('tours/show/{tour?}', 'ToursController@show')->name('tours.show');

    Route::get('tours/{tour}/profile', 'ToursController@profile')->name('tours.profile');
});

Route::middleware(['auth', 'admin'])->group(function() {    
    Route::resource('tourguide', 'TourGuideController')->except(['show']);

    Route::get('/tourguide/show/{schedule?}', 'TourGuideController@show')->name('tourguide.show');

    Route::get('/tourguide/register', 'TourGuideController@showRegistrationForm')->name('tourguide.register');

    Route::post('/tourguide/register', 'TourGuideController@register');
    
    Route::resource('payment', 'PaymentController')->only(['destroy']);

    Route::resource('settings', 'SettingsController');

    Route::resource('tours', 'ToursController')->except(['show', 'update', 'index']);

    Route::post('tours/{tour}', 'ToursController@update')->name('tours.update');

    Route::post('tours/{tour}/suspend', 'ToursController@suspend')->name('tours.suspend');

    Route::put('tours/{tour}/profile', 'ToursController@description')->name('tours.description');

    Route::resource('smallgroup', 'SmallGroupController')->except(['show']);

    Route::get('/smallgroup/show/{schedule?}', 'SmallGroupController@show')->name('smallgroup.show');

    Route::resource('privategroup', 'PrivateGroupController')->except(['show']);

    Route::get('/privategroup/show/{schedule?}', 'PrivateGroupController@show')->name('privategroup.show');

    Route::resource('departure', 'TourDepartureController')->only(['store', 'destroy']);
});
