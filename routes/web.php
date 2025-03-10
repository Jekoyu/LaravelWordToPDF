<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

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

Route::get('/', function () {
    return view('welcome');


});
Route::get('/form', function () {
    return view('form');


});
Route::post('/generate-pdf', [DocumentController::class, 'generateDocument'])->name('generate-pdf');
Route::post('/generate-word', [DocumentController::class, 'generateWord'])->name('generate-word');
