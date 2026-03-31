<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Calendar\CalendarController;
use App\Http\Controllers\Api\Calendar\CalendarEventController;
use App\Http\Controllers\Api\Calendar\EventInviteeController;
use App\Http\Controllers\Api\Group\GroupController;
use App\Http\Controllers\Api\Group\GroupMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('calendars', CalendarController::class);
    Route::apiResource('calendars.events', CalendarEventController::class);
    Route::apiResource('calendars.events.invitees', EventInviteeController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('groups', GroupController::class);
    Route::get('groups/{group}/members', [GroupMemberController::class, 'index']);
    Route::post('groups/{group}/members', [GroupMemberController::class, 'store']);
    Route::delete('groups/{group}/members/{user}', [GroupMemberController::class, 'destroy']);
});
