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

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Auth::routes();

Route::redirect('/home', '/', $status = 301);

// show profile icon
Route::get('/users/icon/{id}', 'UserIconController@showIcon');

// auth:admin only
Route::group(['middleware' => ['auth', 'can:admin']], function() {
    Route::get('/users', 'UsersController@index');
    Route::get('/users/{id}', 'UsersController@detail');
});

// auth:admin & user
Route::group(['middleware' => ['auth', 'can:user']], function() {
    Route::get('/changepassword', 'ChangePasswordController@index');
    Route::post('/changepassword', 'ChangePasswordController@validateChange');

    Route::get('/useredit', 'UserEditController@index');
    Route::post('/useredit/upload', 'UserEditController@upload');
    Route::post('/useredit', 'UserEditController@validateEdit');

    // kaimonolist
    Route::get('/kaimono', 'App\KaimonoController@index');

    // imagepost
    // index,list
    Route::get('/imagepost', 'App\ImagepostController@index');
    // upload
    Route::get('/imagepost/add', 'App\ImagepostController@add');
    Route::post('/imagepost/add', 'App\ImagepostController@validateAdd');
    // image view
    Route::get('/imagepost/photo/{id}', 'App\ImagepostImageController@viewImage');
    // detail
    Route::get('/imagepost/{id}', 'App\ImagepostController@detail');
    // delete
    Route::post('/imagepost/{id}/delete', 'App\ImagepostController@delete');
    // edit
    Route::get('/imagepost/{id}/edit', 'App\ImagepostController@edit');
    Route::post('/imagepost/{id}/edit', 'App\ImagepostController@validateEdit');


    // Scrap
    Route::get('/scrap', 'App\ScrapController@index');
    // detail
    //Route::get('/scrap/{id}', 'App\ScrapController@detail');
    // edit view
    Route::get('/scrap/add', 'App\ScrapController@edit');
    Route::get('/scrap/edit/{id}', 'App\ScrapController@edit');
    // post add
    Route::post('/scrap/add', 'App\ScrapController@validateAdd');
    // post edit
    //Route::post('/scrap/edit/{id}', 'App\ScrapController@validateEdit');
    // delete
    //Route::post('/scrap/delete/{id}', 'App\ScrapController@delete');


    // BugReport
    Route::get('/bugreport', 'App\BugReportController@index');

    // top(dashboard)
    Route::get('/', 'HomeController@index')->name('home');
});

