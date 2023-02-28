<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DatatablesController;
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

Route::get('/', [HomeController::class, 'index']);

Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
Route::get('/ajaxLoadWordLanguage', [HomeController::class, 'ajaxLoadWordLanguage']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('words', WordController::class);

    Route::post('/upload', [WordController::class, 'upload'])->name('words.upload');

    Route::get('logout', function(){
        \Auth::logout();
        return redirect('/');
    });

    
    Route::get('datatables/anyData', [DatatablesController::class, 'anyData'])->name('datatables.data');
    Route::get('datatables/getIndex', [DatatablesController::class, 'getIndex'])->name('datatables');
});

require __DIR__.'/auth.php';
