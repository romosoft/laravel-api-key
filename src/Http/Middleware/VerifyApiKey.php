<?php

namespace Leftsky\LaravelApiKey\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Leftsky\LaravelApiKey\Services\ApiKeyService;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * 处理传入的请求
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取请求中的API密钥
        $headerName = config('api_key.header_name', 'X-API-KEY');
        $keyString = $request->header($headerName);
        
        if (!$keyString) {
            return response()->json([
                'message' => '缺少API密钥',
                'error' => 'missing_api_key'
            ], 401);
        }
        
        // 使用API密钥服务验证密钥
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->validateKey($keyString);
        
        if (!$result['valid']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error']
            ], 401);
        }
        
        // 将用户设置为API密钥所有者
        if ($result['api_key']->user) {
            Auth::setUser($result['api_key']->user);
        }
        
        // 将API密钥添加到请求中，以便控制器可以使用
        $request->apiKey = $result['api_key'];
        
        return $next($request);
    }
} 