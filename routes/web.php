<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CommentController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('tasks', TaskController::Class);
Route::resource('projects', ProjectController::Class);
Route::resource('comments', CommentController::Class);
