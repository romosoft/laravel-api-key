<?php

namespace Leftsky\LaravelApiKey\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Leftsky\LaravelApiKey\Models\ApiKey;
use Leftsky\LaravelApiKey\Models\ApiLog;

class ApiLogService
{
    /**
     * 记录API调用日志
     *
     * @param Request $request 请求对象
     * @param Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response $response 响应对象
     * @param ApiKey $apiKey API密钥
     * @param int $duration 请求处理时间（毫秒）
     * @return ApiLog 创建的日志记录
     */
    public function logApiCall(Request $request, $response, ApiKey $apiKey, int $duration)
    {
        // 检查是否启用日志记录
        if (!config('api_key.logging.enabled', true)) {
            return null;
        }

        // 检查是否排除当前端点
        $path = $request->path();
        $excludedEndpoints = config('api_key.logging.excluded_endpoints', []);
        if (in_array($path, $excludedEndpoints)) {
            return null;
        }

        // 获取请求数据（根据配置决定是否记录）
        $requestData = null;
        if (config('api_key.logging.log_request_data', true)) {
            $requestData = $this->sanitizeRequestData($request);
        }

        // 获取响应数据（根据配置决定是否记录）
        $responseData = null;
        if (config('api_key.logging.log_response_data', true)) {
            $responseData = $this->getResponseContent($response);
        }

        // 创建日志记录
        return ApiLog::create([
            'api_key_id' => $apiKey->id,
            'endpoint' => $path,
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'request_data' => $requestData,
            'response_code' => $response->getStatusCode(),
            'response_data' => $responseData,
            'duration' => $duration,
        ]);
    }

    /**
     * 清理过期的日志记录
     * 
     * @param int|null $days 保留的天数，null则使用配置的默认值
     * @return int 删除的记录数量
     */
    public function cleanOldLogs(?int $days = null)
    {
        $days = $days ?? config('api_key.logging.retention_days', 30);
        $cutoff = now()->subDays($days);

        return ApiLog::where('created_at', '<', $cutoff)->delete();
    }

    /**
     * 处理和净化请求数据
     *
     * @param Request $request
     * @return array
     */
    protected function sanitizeRequestData(Request $request)
    {
        // 获取请求数据
        $data = $request->except(['password', 'password_confirmation', 'token']);

        // 如果请求有文件，我们只记录文件名
        if ($request->hasFile()) {
            foreach ($request->files->all() as $key => $file) {
                if (is_array($file)) {
                    $fileNames = [];
                    foreach ($file as $f) {
                        $fileNames[] = $f->getClientOriginalName();
                    }
                    $data[$key] = $fileNames;
                } else {
                    $data[$key] = $file->getClientOriginalName();
                }
            }
        }

        return $data;
    }

    /**
     * 获取响应内容
     *
     * @param Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response $response
     * @return array|null
     */
    protected function getResponseContent($response)
    {
        $content = $response->getContent();

        // 尝试将内容解析为JSON
        try {
            return json_decode($content, true) ?? ['raw' => mb_substr($content, 0, 1000)];
        } catch (\Exception $e) {
            return ['raw' => mb_substr($content, 0, 1000)];
        }
    }
} 