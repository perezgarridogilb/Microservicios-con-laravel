<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

 Route::prefix('v1')->middleware('api','jwt.verify')->group(function(){
    Route::prefix('products')->group(function(){
    Route::get('/', [ProductController::class,'index']);

    Route::post('/', [ProductController::class,'store']);
    Route::get('{id?}', [ProductController::class,'show']);
    Route::get('search/searchByName', [ProductController::class,'searchByName']);
    Route::put('{id?}', [ProductController::class,'update']);
    Route::delete('{id?}', [ProductController::class,'destroy']);
    // Route::post('refresh', [AuthController::class,'refresh']);
    Route::get('me', [AuthController::class,'me']);
});    
});

Route::get('test', function() {
try {
    $client = new Client(env('MONGO_URI'));
    $collection = $client->inventory->products;
    $product = $collection->insertOne(['name' => 'Pizza', 'ingredients' => ['masa', 'tomate', 'queso'] ]);
    return response()->json(['message' => 'Guardado', 'producto' => $product]);
} catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()], 500);
}
} 
    
);
