<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\MuscleController;
use App\Http\Controllers\MuscleGroupController;
use App\Http\Controllers\SetController;
use App\Http\Controllers\WorkoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::resources([
    'workouts' => WorkoutController::class,
    'logs' => LogController::class,
    'equipment' => EquipmentController::class,
    'sets' => SetController::class
]);

Route::resource('muscles', MuscleController::class)->only([
    'index', 'show'
]);

Route::resource('exercises', ExerciseController::class)->only([
    'show'
]);

Route::resource('groups', MuscleGroupController::class)->only([
    'index', 'show'
]);

Route::get('/exercise/s', [ExerciseController::class, 'search'])->name('exercise.search');


Route::resource('workouts', WorkoutController::class)->names([
    'index' => 'workouts.list',
    'show' => 'workouts.show',
    'update' => 'workouts.update',
    'store' => 'workouts.store'
]);

Route::resource('logs', LogController::class)->names([
    'store' => 'logs.store',
    'show' => 'logs.show',
    'update' => 'logs.update',
    'destroy' => 'logs.destroy'
]);

Route::resource('exercises', ExerciseController::class)->names([
    'show' => 'exercises.show'
]);






