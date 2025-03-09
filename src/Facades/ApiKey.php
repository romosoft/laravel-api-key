<?php

namespace Leftsky\LaravelApiKey\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Leftsky\LaravelApiKey\Models\ApiKey generate(int $userId, array $options = [])
 * @method static array validateKey(string $keyString)
 * @method static \Leftsky\LaravelApiKey\Models\ApiKey|null getById(int $id)
 * @method static bool revoke(int $id)
 * @method static \Illuminate\Database\Eloquent\Collection getUserKeys(int $userId)
 * 
 * @see \Leftsky\LaravelApiKey\Services\ApiKeyService
 */
class ApiKey extends Facade
{
    /**
     * 获取注册的组件的名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'api-key';
    }
} 