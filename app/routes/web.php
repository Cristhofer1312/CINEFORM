<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
Route::get('/bienvenido', function () {
    return view('welcome');
});
*/


Route::get('/', function () {
    //exit;
    return redirect()->route('login');
})->name('main');

Route::get('/elecciones', function () {
    //exit;
    return redirect()->route('votes_statistics');
});