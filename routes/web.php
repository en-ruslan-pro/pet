<?php

use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('demo', 'demo')->name('demo');

Route::get('room/create', [RoomController::class, 'create'])->name('room.create');
Route::post('room/create', [RoomController::class, 'store'])->name('room.store');
Route::get('room/{room:code}', [RoomController::class, 'show'])->name('room.show');
Route::post('room/{room:code}/meow', [RoomController::class, 'sendMeow'])
    ->middleware('throttle:12,1')
    ->name('room.meow');
Route::get('room/{room:code}/status', [RoomController::class, 'status'])->name('room.status');

Route::get('tv', [RoomController::class, 'tvEntry'])->name('tv.entry');
Route::post('tv', [RoomController::class, 'enterTv'])->name('tv.enter');
Route::get('tv/{room:code}', [RoomController::class, 'showTv'])->name('tv.show');
Route::post('tv/{room:code}/heartbeat', [RoomController::class, 'heartbeat'])->name('tv.heartbeat');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
