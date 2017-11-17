<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::resource('/travel', 'TravelController');

Route::get('/home', 'HomeController@index');


/***************Api************************/
/***************user************************/
Route::post('/signIn','UserController@signIn');
Route::post('/signUp','UserController@signUp');
Route::get('/updateProfile','UserController@updateProfile');
Route::get('/viewProfile','UserController@viewProfile');


/***************travels************************/
Route::get('/setTravel','TravelController@setTravel');
Route::get('/updateTravel','TravelController@updateTravel');
Route::get('/viewTravel','TravelController@viewTravel');
Route::get('/getTravelsByCity','TravelController@getTravelsByCity');
Route::get('/changeStatus','TravelController@changeStatus');
Route::get('/viewMyTravels','TravelController@viewMyTravels');


/***************guests and travels************************/
Route::get('/joinTravel','GuestController@joinTravel');
Route::get('/responseRequest','GuestController@responseRequest');
/****************city**************************/
Route::get('/getCity','CityController@getCity');
Route::get('/setCityForUser','UserController@setCityForUser');

