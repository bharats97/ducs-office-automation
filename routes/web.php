<?php

/*
|---4-----------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\OutgoingLettersController;

Route::get('/', function () {
    return view('dashboard');
})->middleware('auth');

Route::get('/login', 'Auth\LoginController@showLoginForm')->middleware('guest')->name('login');
Route::post('/login', 'Auth\LoginController@login')->middleware('guest');
Route::post('/logout', 'Auth\LoginController@logout')->middleware('auth');


Route::get('/outgoing-letters/create', 'OutgoingLettersController@create')->middleware('auth');
Route::post('/outgoing-letters', 'OutgoingLettersController@store')->middleware('auth');
Route::get('/outgoing-letters/{outgoing_letter}/edit', 'OutgoingLettersController@edit')->middleware('auth');
Route::get('/outgoing-letters', 'OutgoingLettersController@index')->middleware('auth');
Route::patch('/outgoing-letters/{outgoing_letter}', 'OutgoingLettersController@update')->middleware('auth');
Route::delete('/outgoing-letters/{outgoing_letter}','OutgoingLettersController@destroy')->middleware('auth');


Route::get('/courses', 'CoursesController@index')->middleware('auth');
Route::post('/courses', 'CoursesController@store')->middleware('auth');
Route::patch('/courses/{course}', 'CoursesController@update')->middleware('auth');
Route::delete('/courses/{course}', 'CoursesController@destroy')->middleware('auth');

