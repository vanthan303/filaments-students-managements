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

Route::get('/', function () {
    return view('welcome');
});


// Xử lý PDF
Route::get('{student}/invoice/generate',[App\Http\Controllers\InvoicesController::class, 'generatePdf'])
    ->name('student.invoice.generate');
