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
    return view('welcome');
});


Route::group([
    'middleware' => [
        'web',
//        '\crocodicstudio\crudbooster\middlewares\CBBackend'
    ],
//    'prefix' => config('crudbooster.ADMIN_PATH'),
//    'namespace' => $namespace,
], function () {
    Route::get('attachments/{one?}/{two?}/{three?}/{four?}/{five?}', ['uses' => '\App\Http\Controllers\FileController@getPreview', 'as' => 'fileControllerPreviewSecure']);

});

Route::get('/redirect/{provider}', ['uses' => '\App\Http\Controllers\Auth\SocialiteController@redirectToProvider', 'as' => 'redirect']);
Route::get('/callback/{provider}', '\App\Http\Controllers\Auth\SocialiteController@handleProviderCallback');