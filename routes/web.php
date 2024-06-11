<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductegetController;
use App\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('getproduct',[ProductegetController::class,'getproduct'])->name('getproduct');
Route::get('shopifyimportproduct',[ProductegetController::class,'shopifyimportproduct'])->name('shopifyimportproduct');
Route::get('getdata',[ProductegetController::class,'getdata'])->name('getdata');
Route::any('createproduct',[WebhookController::class,'createproduct'])->name('createproduct');
Route::any('updateproduct',[WebhookController::class,'updateproduct'])->name('updateproduct');
Route::any('deleteproduct',[WebhookController::class,'deleteproduct'])->name('deleteproduct');
