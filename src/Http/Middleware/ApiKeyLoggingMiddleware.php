<?php

namespace Leftsky\LaravelApiKey\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Leftsky\LaravelApiKey\Services\ApiLogService;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyLoggingMiddleware
{
    /**
     * API日志服务
     * 
     * @var ApiLogService
     */
    protected $apiLogService;

    /**
     * 创建一个中间件实例
     *
     * @param ApiLogService $apiLogService
     * @return void
     */
    public function __construct(ApiLogService $apiLogService)
    {
        $this->apiLogService = $apiLogService;
    }

    /**
     * 处理传入的请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取请求开始时间
        $startTime = microtime(true);
        
        // 处理请求
        $response = $next($request);
        
        // 计算请求处理时间（毫秒）
        $duration = round((microtime(true) - $startTime) * 1000);
        
        // 如果请求中包含有效的API密钥，则记录日志
        if ($request->apiKey) {
            $this->apiLogService->logApiCall($request, $response, $request->apiKey, $duration);
        }
        
        return $response;
    }
} 