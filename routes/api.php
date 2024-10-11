<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeporteController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\FacturaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
    
});
Route::post('login',[AuthController::class, 'login']);
Route::post('register',[AuthController::class, 'register']);


Route::middleware(['auth:sanctum'])->group(function (){

    //Usuarios
    Route::put('/edit/{id}', [AuthController::class, 'update']);
    Route::get('/seeusers', [AuthController::class, 'index']);
    Route::get('/seeuser/{id}', [AuthController::class, 'show']);
    Route::get('listprofile', [AuthController::class, 'token']);
    Route::get('/exportusers', [ExcelController::class, 'export']);

    //Deportes
    Route::post('/addSport', [DeporteController::class, 'addSport']);
    Route::get('/watchsports', [DeporteController::class, 'show']);
    Route::delete('delete/{id}', [DeporteController::class, 'delete']);
    
    //Calendario
    Route::post('addCalendar',[CalendarioController::class, 'create']);
    Route::put('editCalendar/{id}',[CalendarioController::class, 'update']);
    Route::delete('deleteCalendar/{id}',[CalendarioController::class, 'destroy']);
    Route::get('showCalendar',[CalendarioController::class, 'show']);

    //Evento
    Route::post('addEvent',[EventoController::class, 'addEvent']);
    Route::get('showEvent',[EventoController::class, 'show']);
    Route::delete('deleteEvent/{id}',[EventoController::class, 'delete']);

    //Factura
    Route::post('addFactura',[FacturaController::class, 'addFactura']);
    Route::get('showFactura',[FacturaController::class, 'show']);
    Route::delete('deleteFactura/{id}',[FacturaController::class, 'delete']);
});


