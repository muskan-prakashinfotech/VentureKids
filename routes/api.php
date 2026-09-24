<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FollowupController;
use App\Http\Controllers\API\TrainerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware(['ApiLocalization'])->prefix('v1')->namespace('API')->group(function () {
    Route::post('/createstudent', [StudentController::class, 'studentStore']);
});

Route::middleware(['ApiLocalization'])->prefix('v1')->namespace('API')->group(function () {
    Route::post('/createstudentwithparent', [StudentController::class, 'createStudentWithParent']);
});

Route::middleware(['ApiLocalization'])->prefix('v2')->namespace('API')->group(function () {
    // Route::post('/auth/register', [AuthController::class, 'createUser']);
    Route::post('/auth/login', [AuthController::class, 'loginUser']);
});

Route::middleware(['ApiLocalization','auth:sanctum'])->prefix('v2')->namespace('API')->group(function () {
    Route::post('/createstudentwithparent', [StudentController::class, 'createStudentWithParent']);
    Route::post('/followup', [FollowupController::class, 'followUp']);
    Route::post('/add/trainer', [TrainerController::class, 'addTrainer']);
    Route::get('/student/project/{student_username?}', [StudentController::class, 'portfolio']);
    Route::get('/student/list/{school_username?}', [StudentController::class, 'studentList']);
});