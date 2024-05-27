<?php

use App\Http\Controllers\ExcelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('store/file',[ExcelController::class,'uploadDocument']);
// Route::post('import/file',[ExcelController::class,'ExcelImport']);
Route::post('transfer/data',[ExcelController::class,'transferToModule']);