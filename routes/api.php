<?php
// header('Access-Control-Allow-Origin: *');
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'namespace' => 'Api\v1'
], function () {
    Route::get('send-email', 'LoginController@sendMail');
    // Registration & Logins
    Route::post('register', 'LoginController@register');
    Route::post('login', 'LoginController@login');
    Route::post('password/email', 'LoginController@forgotPasswordEmail');
    Route::post('password/reset', 'LoginController@resetPassword');
});

// MAIN AUTH MIDDLEWARE
Route::group([
    'middleware' => ['Cors'],
    'namespace' => 'Api\v1'
], function () {
    // Mailer API'S
    Route::group([], function () {        
        Route::post('sendandsubmitmail', 'MailController@sendMail');
        Route::post('searchmails', 'MailController@getmails');
        Route::get('getemaildetail/{id}', 'MailController@getsinglemail');        
    });

   
    Route::get('logout', 'LoginController@logout');
});