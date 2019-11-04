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
Route::delete('/outgoing-letters/{outgoing_letter}', 'OutgoingLettersController@destroy')->middleware('auth');

Route::post('/outgoing-letters/{outgoing_letter}/remarks', 'OutgoingLetterRemarksController@store')->middleware('auth');
Route::post('/outgoing_letters/{letter}/reminders', 'OutgoingLetterRemindersController@store')->middleware('auth');

Route::patch('/remarks/{remark}', 'RemarksController@update')->middleware('auth');
Route::delete('/remarks/{remark}', 'RemarksController@destroy')->middleware('auth');

Route::delete('/reminders/{reminder}', 'RemindersController@destroy')->middleware('auth');
Route::patch('/reminders/{reminder}', 'RemindersController@update')->middleware('auth');


Route::get('/programmes', 'ProgrammesController@index')->middleware('auth');
Route::post('/programmes', 'ProgrammesController@store')->middleware('auth');
Route::patch('/programmes/{programme}', 'ProgrammesController@update')->middleware('auth');
Route::delete('/programmes/{programme}', 'ProgrammesController@destroy')->middleware('auth');

Route::get('/courses', 'CourseController@index')->middleware('auth');
Route::post('/courses', 'CourseController@store')->middleware('auth');
Route::patch('/courses/{course}', 'CourseController@update')->middleware('auth');
Route::delete('/courses/{course}', 'CourseController@destroy')->middleware('auth');

Route::get('/colleges', 'CollegeController@index')->middleware('auth');
Route::post('/colleges', 'CollegeController@store')->middleware('auth');
Route::patch('/colleges/{college}', 'CollegeController@update')->middleware('auth');
Route::delete('/colleges/{college}', 'CollegeController@destroy')->middleware('auth');

Route::get('/users', 'UserController@index')->middleware('auth');
Route::post('/users', 'UserController@store')->middleware('auth');
Route::patch('/users/{user}', 'UserController@update')->middleware('auth');
Route::delete('/users/{user}', 'UserController@destroy')->middleware('auth');

Route::get('/roles', 'RoleController@index')->middleware('auth');
Route::post('/roles', 'RoleController@store')->middleware('auth');
Route::patch('/roles/{role}', 'RoleController@update')->middleware('auth');
Route::delete('/roles/{role}', 'RoleController@destroy')->middleware('auth');

Route::get('/attachments/{attachment}', 'AttachmentController@show')->middleware('auth');
Route::delete('/attachments/{attachment}', 'AttachmentController@destroy')->middleware('auth');
