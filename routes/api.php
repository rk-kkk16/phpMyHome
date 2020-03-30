<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:api', 'namespace' => 'Api'], function() {
    // Mustbuy model's api
    Route::get('mustbuys','MustbuyController@list');
    Route::get('mustbuys/detail/{id}', 'MustbuyController@detail');
    Route::post('mustbuys/regist', 'MustbuyController@validateRegist');
    Route::put('mustbuys/regist', 'MustbuyController@validateRegist');
    Route::delete('mustbuys/delete/{id}', 'MustbuyController@delete');
    Route::put('mustbuys/toggle/{id}/{newstate}', 'MustbuyController@toggleState');

    // Imagepost model's api
    Route::get('imagepost/list', 'ImagepostController@list');
    Route::post('imagepost/upload', 'ImagepostUploadController@upload');
    Route::post('imagepost/eval/add', 'ImagepostController@validateAddEval');
    Route::delete('imagepost/eval/delete/{id}', 'ImagepostController@delEval');
    Route::get('imagepost/comment/list/{imagepost_id}', 'ImagepostController@listComment');
    Route::get('imagepost/comment/{id}', 'ImagepostController@comment');
    Route::post('imagepost/comment/add', 'ImagepostController@validateAddComment');
    Route::delete('imagepost/comment/delete/{id}', 'ImagepostController@delComment');


    // scrap model's api
    Route::post('scrap/upload', 'ScrapUploadController@upload');
    Route::delete('scrap/tmp/delete', 'ScrapUploadController@deleteTmpFile');
    Route::delete('scrap/delete/{id}', 'ScrapUploadController@deleteScFile');
    Route::post('scrap/urlinfo', 'ScrapController@urlInfo');


    // bugreport model's api
    Route::get('bugreport','BugReportController@list');
    Route::post('bugreport/regist', 'BugReportController@validateRegist');
    Route::put('bugreport/regist', 'BugReportController@validateRegist');
    Route::get('bugreport/detail/{id}', 'BugReportController@detail');
    Route::delete('bugreport/delete/{id}', 'BugReportController@delete');
    Route::put('bugreport/toggle/{id}/{newstate}', 'BugReportController@toggleState');

});
