<?php

use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\TranscriptImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TranscriptController::class, 'index'])->name('transcripts.index');
Route::get('/import', [TranscriptImportController::class, 'create'])->name('transcripts.import');
Route::post('/import', [TranscriptImportController::class, 'store'])->name('transcripts.import.store');
Route::post('/transcripts/pdf', [TranscriptController::class, 'pdf'])->name('transcripts.pdf');
