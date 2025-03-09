<?php

namespace Leftsky\LaravelApiKey\Services;

use Illuminate\Support\Facades\Cache;
use Leftsky\LaravelApiKey\Models\ApiKey;

class ApiKeyService
{
    /**
     * 生成一个新的API密钥
     * 
     * @param  int  $userId 用户ID
     * @param  array  $options API密钥选项
     * @return \Leftsky\LaravelApiKey\Models\ApiKey
     */
    public function generate(int $userId, array $options = [])
    {
        $expiresInDays = $options['expires_in_days'] ?? config('api_key.expires_in_days');
        
        $apiKey = new ApiKey([
            'user_id' => $userId,
            'name' => $options['name'] ?? 'API密钥 ' . now()->format('Y-m-d H:i'),
            'description' => $options['description'] ?? null,
            'is_active' => $options['is_active'] ?? true,
            'permissions' => $options['permissions'] ?? null,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);
        
        $apiKey->save();
        
        return $apiKey;
    }
    
    /**
     * 验证API密钥
     * 
     * @param  string  $keyString API密钥字符串
     * @return array 包含验证结果和消息的数组
     */
    public function validateKey(string $keyString): array
    {
        $authStrategy = config('api_key.auth_strategy', 'database');
        
        // 根据配置的策略验证密钥
        if ($authStrategy === 'cache') {
            return $this->validateKeyWithCache($keyString);
        }
        
        return $this->validateKeyWithDatabase($keyString);
    }
    
    /**
     * 使用数据库验证API密钥
     * 
     * @param  string  $keyString API密钥字符串
     * @return array 包含验证结果和消息的数组
     */
    protected function validateKeyWithDatabase(string $keyString): array
    {
        $apiKey = ApiKey::where('key', $keyString)->first();
        
        if (!$apiKey) {
            return [
                'valid' => false, 
                'message' => 'API密钥无效', 
                'error' => 'invalid_api_key',
                'api_key' => null
            ];
        }
        
        if (!$apiKey->isValid()) {
            return [
                'valid' => false, 
                'message' => 'API密钥已禁用或已过期', 
                'error' => 'inactive_api_key',
                'api_key' => $apiKey
            ];
        }
        
        // 更新最后使用时间
        $apiKey->update(['last_used_at' => now()]);
        
        return [
            'valid' => true, 
            'message' => 'API密钥有效', 
            'error' => null,
            'api_key' => $apiKey
        ];
    }
    
    /**
     * 使用缓存验证API密钥
     * 
     * @param  string  $keyString API密钥字符串
     * @return array 包含验证结果和消息的数组
     */
    protected function validateKeyWithCache(string $keyString): array
    {
        $cachePrefix = config('api_key.cache.prefix', 'api_key_');
        $cacheTtl = config('api_key.cache.ttl', 60);
        
        $cacheKey = $cachePrefix . md5($keyString);
        
        // 尝试从缓存获取API密钥
        $apiKey = Cache::remember($cacheKey, $cacheTtl, function () use ($keyString) {
            return ApiKey::where('key', $keyString)->first();
        });
        
        if (!$apiKey) {
            return [
                'valid' => false, 
                'message' => 'API密钥无效', 
                'error' => 'invalid_api_key',
                'api_key' => null
            ];
        }
        
        if (!$apiKey->isValid()) {
            return [
                'valid' => false, 
                'message' => 'API密钥已禁用或已过期', 
                'error' => 'inactive_api_key',
                'api_key' => $apiKey
            ];
        }
        
        // 更新最后使用时间，但不频繁更新数据库
        // 只在一定间隔后更新，以避免频繁的数据库写入
        if (!$apiKey->last_used_at || now()->diffInMinutes($apiKey->last_used_at) > 10) {
            $apiKey->update(['last_used_at' => now()]);
        }
        
        return [
            'valid' => true, 
            'message' => 'API密钥有效', 
            'error' => null,
            'api_key' => $apiKey
        ];
    }
    
    /**
     * 根据ID获取API密钥
     * 
     * @param  int  $id API密钥ID
     * @return \Leftsky\LaravelApiKey\Models\ApiKey|null
     */
    public function getById(int $id)
    {
        return ApiKey::find($id);
    }
    
    /**
     * 撤销API密钥
     * 
     * @param  int  $id API密钥ID
     * @return bool
     */
    public function revoke(int $id): bool
    {
        $apiKey = $this->getById($id);
        
        if (!$apiKey) {
            return false;
        }
        
        $apiKey->is_active = false;
        return $apiKey->save();
    }
    
    /**
     * 获取用户的所有API密钥
     * 
     * @param  int  $userId 用户ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserKeys(int $userId)
    {
        return ApiKey::where('user_id', $userId)->get();
    }
} 