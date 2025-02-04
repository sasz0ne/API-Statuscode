<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('menus', MenuController::class);
