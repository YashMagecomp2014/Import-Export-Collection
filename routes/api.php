<?php

use App\Http\Controllers\ChargeController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\IECollectionController;
use App\Http\Controllers\PlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return "Hello API";
});


//Collection
Route::get('/import', [CollectionController::class, 'index']);
Route::post('/import', [CollectionController::class, 'store']);
Route::post('/deleteimport', [CollectionController::class, 'delete']);
Route::get('/getcsv', [CollectionController::class, 'exportcsv']);
Route::get('/getallcollection', [CollectionController::class, 'getallcollection']);
Route::get('/getsmartcollection', [CollectionController::class, 'getsmartcollection']);
Route::get('/getcustomcollection', [CollectionController::class, 'getcustomcollection']);

// Export file
Route::post('/GetSelectedCollections', [CollectionController::class, 'GetSelectedCollections']);
Route::post('/GetSelectedCollectionsWithProducts', [CollectionController::class, 'GetSelectedCollectionsWithProducts']);


//maatwebsite
Route::get('file-import-export', [IECollectionController::class, 'fileImportExport']);
Route::post('file-import', [IECollectionController::class, 'fileImport'])->name('file-import');

// Export file
Route::get('file-export', [IECollectionController::class, 'fileExport'])->name('file-export');
Route::get('fileExportwithproduct', [IECollectionController::class, 'fileExportwithproduct'])->name('fileExportwithproduct');
Route::get('GetAllProduct', [IECollectionController::class, 'GetAllProduct'])->name('GetAllProduct');
Route::get('GetAllProductNotInAnyCollection', [IECollectionController::class, 'GetAllProductNotInAnyCollection'])->name('GetAllProductNotInAnyCollection');


//Plan
Route::get('SubscriptionPlan', [PlanController::class, 'PlanCreation'])->name('SubscriptionPlan');
Route::get('getchargeid', [ChargeController::class, 'GetChargeID'])->name('getchargeid');