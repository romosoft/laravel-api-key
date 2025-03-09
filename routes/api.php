<?php

use Illuminate\Support\Facades\Route;
use Leftsky\LaravelApiKey\Http\Controllers\Api\ApiKeyController;

// API密钥路由
Route::prefix(config('api_key.routes.prefix', 'api'))
    ->middleware(config('api_key.routes.middleware', ['api']))
    ->group(function () {
        // 公共路由，不需要API密钥
        Route::get('/status', function () {
            return response()->json([
                'success' => true,
                'message' => 'API运行正常',
                'version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
            ]);
        });

        // 需要API密钥的路由
        Route::middleware('api.key')->group(function () {
            // 用户信息
            Route::get('/user', function () {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => auth()->user(),
                    ],
                ]);
            });
            
            // API密钥信息
            Route::get('/key-info', function () {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'api_key' => request()->apiKey,
                    ],
                ]);
            });
        });
    }); 