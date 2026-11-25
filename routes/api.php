<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttachmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/public/attachments/{attachment}/download', [AttachmentController::class, 'publicDownload']);

Route::middleware('auth:sanctum')->group(function () {
 
Route::apiResource('tasks', TaskController::class);
Route::apiResource('projects', ProjectController::class);

Route::post('/logout', [AuthController::class, 'logout']);
Route::match(['get', 'post'], '/me', [AuthController::class, 'me']);
Route::patch('/me', [AuthController::class, 'updateProfile']);
Route::get('/comments', [CommentController::class, 'allComments']);

Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);

Route::post('/projects/{project}/report', [ProjectController::class, 'generateReport']);

Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store']);
Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
Route::get('/attachments/{attachment}/signed-url', [AttachmentController::class, 'signedUrl']);
Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

}
);