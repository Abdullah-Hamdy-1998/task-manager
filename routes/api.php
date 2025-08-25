<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->middleware('can:viewAny,App\Models\Task');
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->middleware('can:viewOwn,App\Models\Task');
        Route::post('/', [TaskController::class, 'store'])->middleware('can:create,App\Models\Task');
        Route::get('/{task}', [TaskController::class, 'show'])->middleware('can:view,task');
        Route::put('/{task}', [TaskController::class, 'update'])->middleware('can:update,task');
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->middleware('can:updateStatus,task');
        Route::post('/{task}/dependencies', [TaskController::class, 'addDependency'])->middleware('can:addDependency,task');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->middleware('can:delete,task');
    });
});