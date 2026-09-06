<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('about', 'about')->name('about');
Route::get('demo', [DemoController::class, 'show'])->name('demo');

Route::get('room/create', [RoomController::class, 'create'])->name('room.create');
Route::post('room/create', [RoomController::class, 'store'])->name('room.store');
Route::get('room/{room:code}', [RoomController::class, 'show'])->name('room.show');
Route::post('room/{room:code}/meow', [RoomController::class, 'sendMeow'])
    ->middleware('throttle:12,1')
    ->name('room.meow');
Route::post('room/{room:code}/actions/{action}', [RoomController::class, 'sendPetAction'])
    ->whereIn('action', ['feed', 'play', 'sleep'])
    ->middleware('throttle:12,1')
    ->name('room.actions');
Route::get('room/{room:code}/status', [RoomController::class, 'status'])->name('room.status');

Route::get('tv', [RoomController::class, 'tvEntry'])->name('tv.entry');
Route::post('tv', [RoomController::class, 'enterTv'])->name('tv.enter');
Route::get('tv/{room:code}', [RoomController::class, 'showTv'])->name('tv.show');
Route::post('tv/{room:code}/heartbeat', [RoomController::class, 'heartbeat'])->name('tv.heartbeat');
Route::post('tv/{room:code}/sessions', [RoomController::class, 'startViewSession'])->middleware('throttle:12,1')->name('tv.sessions.start');
Route::post('tv/{room:code}/sessions/{session}/heartbeat', [RoomController::class, 'heartbeatViewSession'])->middleware('throttle:30,1')->name('tv.sessions.heartbeat');
Route::post('tv/{room:code}/sessions/{session}/end', [RoomController::class, 'endViewSession'])->middleware('throttle:12,1')->name('tv.sessions.end');
Route::post('tv/{room:code}/actions/start', [RoomController::class, 'startAutonomousAction'])->middleware('throttle:30,1')->name('tv.actions.start');
Route::post('tv/{room:code}/actions/{execution}/start', [RoomController::class, 'startActionExecution'])->middleware('throttle:30,1')->name('tv.actions.execution.start');
Route::post('tv/{room:code}/actions/{execution}/finish', [RoomController::class, 'finishActionExecution'])->middleware('throttle:30,1')->name('tv.actions.execution.finish');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
