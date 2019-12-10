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
    Route::get('/', 'HomeController@welcome')->name('landing_page');

    Route::get('/home', 'HomeController@index')->name('home'); 

    Route::resource('schedule', 'ScheduleController')->except(['show']);

    Route::get('/schedule/show/{schedule?}', 'ScheduleController@show')->name('schedule.show');

    Route::resource('profile', 'ProfileController')->except(['update']);

    Route::get('myprofile', 'ProfileController@profile')->name('myprofile');

    Route::put('myprofile/language', 'ProfileController@updateLanguage')->name('myprofile.language');

    Route::put('myprofile/contact', 'ProfileController@updateContact')->name('myprofile.contact');

    Route::post('myprofile/picture', 'ProfileController@updatePicture')->name('myprofile.picture');

    Route::post('profile/{id}', 'ProfileController@update')->name('profile.update');

    Route::resource('payment', 'PaymentController')->except(['show', 'destroy']);

    Route::get('/payment/show/{user?}', 'PaymentController@show')->name('payment.show');

    Route::put('payment/notes/{user}', 'PaymentController@notes');
    
    Route::get('/schedule/export', 'ScheduleController@export')->name('schedule.export');

    Route::get('tours', 'ToursController@index')->name('tours.index');

    Route::get('tours/show/{tour?}', 'ToursController@show')->name('tours.show');

    Route::put('tours/rate/update', 'ToursController@tourRateUpdate')->name('tours.update_rate');

    Route::get('tours/{tour}/profile', 'ToursController@profile')->name('tours.profile');

    Route::get('guide/statistics', 'StatisticsController@index');

    Route::get('guide/statistics/filter/{filter?}', 'StatisticsController@guidestats');

    Route::post('schedule/bulk/cancel/{option?}', 'ScheduleController@cancelAvailability');
});

Route::middleware(['auth', 'admin'])->group(function() {    
    Route::resource('tourguide', 'TourGuideController')->except(['show']);

    Route::get('/tourguide/show/{schedule?}', 'TourGuideController@show')->name('tourguide.show');

    Route::get('/tourguide/register', 'TourGuideController@showRegistrationForm')->name('tourguide.register');

    Route::post('/tourguide/register', 'TourGuideController@register');
    
    Route::get('tourguide/{guide}/profile', 'TourGuideController@profile')->name('tourguide.profile');

    Route::put('tourguide/{guide}/profile', 'TourGuideController@note')->name('tourguide.note');

    Route::put('tourguide/{guide}/rating', 'TourGuideController@rating')->name('tourguide.rating');

    Route::put('tourguide/{guide}/language', 'TourGuideController@updateLanguage')->name('tourguide.language');

    Route::put('tourguide/{guide}/contact', 'TourGuideController@updateContact')->name('tourguide.contact');

    Route::post('tourguide/{guide}/picture', 'TourGuideController@updatePicture')->name('tourguide.picture');

    Route::put('tourguide/{guide}/email', 'TourGuideController@updateEmail')->name('tourguide.email');

    Route::put('tourguide/{guide}/payment', 'TourGuideController@payment')->name('tourguide.payment');
    
    Route::resource('payment', 'PaymentController')->only(['destroy']);

    Route::get('admin/payment/{guide}/{option?}', 'PaymentController@paymentByAdmin');

    Route::resource('settings', 'SettingsController');

    Route::resource('tours', 'ToursController')->except(['show', 'update', 'index']);

    Route::post('tours/{tour}', 'ToursController@update')->name('tours.update');

    Route::post('tours/{tour}/suspend', 'ToursController@suspend')->name('tours.suspend');

    Route::put('tours/{tour}/profile', 'ToursController@description')->name('tours.description');

    Route::get('tourcalendar', 'SmallGroupController@index');

    Route::resource('smallgroup', 'SmallGroupController')->except(['show']);

    Route::get('/smallgroup/show/{schedule?}', 'SmallGroupController@show')->name('smallgroup.show');

    Route::resource('privategroup', 'PrivateGroupController')->except(['show']);

    Route::get('/privategroup/show/{schedule?}', 'PrivateGroupController@show')->name('privategroup.show');

    Route::resource('departure', 'TourDepartureController')->only(['store', 'destroy']);

    Route::get('departure/list', 'TourDepartureController@departures_list');

    Route::put('departure/auto', 'TourDepartureController@autoAssignment');

    Route::put('departure/manual', 'TourDepartureController@manualAssignment');

    Route::put('departure/cancel/guide', 'TourDepartureController@cancelAssignment');

    Route::put('departure/serial_number', 'TourDepartureController@serialNumberAssignment');

    Route::put('departure/paid', 'TourDepartureController@paidToggle');

    Route::put('departure/payment/bulk/{option}', 'TourDepartureController@payment');

    Route::put('departure/participant', 'TourDepartureController@participantUpdate');

    Route::put('departure/earning', 'TourDepartureController@earningUpdate');

    Route::post('departure/serial_number/add', 'TourDepartureController@addSerialNumber');

    Route::delete('departure/serial_number/delete', 'TourDepartureController@deleteSerialNumber');

    Route::post('departure/note', 'TourDepartureController@note');

    Route::post('departure/payment_method', 'TourDepartureController@payment_method');

    Route::get('departure/export', 'TourDepartureController@export');

    Route::resource('statistics', 'StatisticsController');

    Route::get('statistics/filter/{filter?}', 'StatisticsController@statistics');

    Route::get('statistics/download/{filter?}', 'StatisticsController@downloadTours');

    Route::get('charts/filter/{filter?}', 'StatisticsController@charts');

    Route::get('statistics/tour_trends/{filter?}', 'StatisticsController@tourTrends');

    Route::resource('notification', 'NotificationController');

    Route::post('notification/departure', 'NotificationController@notifyGuide');

    Route::post('notification/modification', 'NotificationController@modification')->name('notification.modification');

    Route::match(['get', 'post'], 'notification/summary/{option}', 'NotificationController@summary')->name('notification.summary');

    Route::post('notification/no_serial_tours', 'NotificationController@sendToursWithoutVoucherCodes');

    Route::get('notification/no_serial_tours/download', 'NotificationController@downloadToursWithoutVoucherCodes');

    Route::put('voucher/{option}', 'TourDepartureController@voucherStatus');

    Route::get('articles/fetch/all', 'ArticleController@fetchAll');

    Route::resource('articles', 'ArticleController');
});

Route::middleware(['auth', 'accepted'])->group(function() {
    Route::resource('articles', 'ArticleController')->only(['index', 'show']);
});