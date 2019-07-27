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

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

Route::middleware(['auth', 'accepted'])->group(function() {
    Route::get('/home', 'HomeController@index')->name('home'); 

    Route::resource('schedule', 'ScheduleController')->except(['show']);
    Route::get('/schedule/show/{schedule?}', 'ScheduleController@show')->name('schedule.show');

    Route::resource('profile', 'ProfileController')->except(['update']);
    Route::post('profile/{id}', 'ProfileController@update')->name('profile.update');

    // Route::post('payment/store/{category}', 'PaymentController@store');
    Route::get('/payment/show/{schedule?}', 'PaymentController@show')->name('payment.show');
    Route::resource('payment', 'PaymentController')->except(['show']);;
});

Route::middleware(['auth', 'admin'])->group(function() {    
    Route::resource('tourguide', 'TourGuideController')->except(['show']);
    Route::get('/tourguide/show/{schedule?}', 'TourGuideController@show')->name('tourguide.show');
    
    Route::get('/schedule/export', 'ScheduleController@export')->name('schedule.export');
});
