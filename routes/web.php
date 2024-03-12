<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
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



Route::get('/contacts/import', [ContactController::class, 'showImportForm'])->name('contacts.import');
Route::post('/contacts/upload', [ContactController::class, 'uploadCsv'])->name('contacts.upload');
Route::post('/contacts/process-mapping', [ContactController::class, 'processMapping'])->name('contacts.processMapping');
Route::post('/contacts/complete-import', [ContactController::class, 'completeImport'])->name('contacts.completeImport');



