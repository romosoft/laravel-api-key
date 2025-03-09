<?php

namespace Leftsky\LaravelApiKey\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'key',
        'description',
        'permissions',
        'is_active',
        'expires_at',
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'json',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * 创建新实例时的引导方法
     */
    protected static function booted()
    {
        static::creating(function ($apiKey) {
            // 如果没有设置key，则自动生成
            if (empty($apiKey->key)) {
                $apiKey->key = static::generateKey();
            }
        });
    }

    /**
     * 获取拥有此API密钥的用户
     */
    public function user(): BelongsTo
    {
        $userModel = config('api_key.user_model', 'App\\Models\\User');
        return $this->belongsTo($userModel);
    }

    /**
     * 生成一个新的随机API密钥
     */
    public static function generateKey(): string
    {
        $length = config('api_key.key_length', 64);
        return bin2hex(random_bytes($length / 2)); // 每个字节产生2个十六进制字符
    }

    /**
     * 确定此密钥是否已激活并未过期
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * 设置表名
     */
    public function getTable()
    {
        return config('api_key.table_name', parent::getTable() ?: 'api_keys');
    }
} 