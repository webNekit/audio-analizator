<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Audio
    Route::get('/audio/upload', [\App\Http\Controllers\AudioFileController::class, 'create'])->name('audio.create');
    Route::post('/audio/upload', [\App\Http\Controllers\AudioFileController::class, 'store'])->name('audio.store');
    Route::get('/audio/{audioFile}', [\App\Http\Controllers\AudioFileController::class, 'show'])->name('audio.show');
    // History
    Route::get('/history', [\App\Http\Controllers\AudioAnalysisController::class, 'history'])->name('analysis.history');
    Route::get('/history/{audioFile}', [\App\Http\Controllers\AudioAnalysisController::class, 'show'])->name('analysis.show');
    Route::get('/history/{audioFile}/download', [\App\Http\Controllers\AudioAnalysisController::class, 'downloadReport'])->name('analysis.download');
    Route::delete('/history/{audioFile}', [\App\Http\Controllers\AudioAnalysisController::class, 'destroy'])->name('analysis.destroy');
});

require __DIR__.'/auth.php';
