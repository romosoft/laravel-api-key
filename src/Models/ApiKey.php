<?php

namespace Leftsky\LaravelApiKey\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * 序列化时隐藏的属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'key',
    ];
    
    /**
     * 获取掩码的密钥（仅显示前8位）
     *
     * @return string
     */
    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 8) . '...';
    }
    
    /**
     * 检查密钥是否过期
     * 
     * @return bool
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return now()->greaterThan($this->expires_at);
    }
    
    /**
     * 获取有效期状态（无限或日期）
     * 
     * @return string
     */
    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expires_at) {
            return '无限期';
        }
        
        return $this->is_expired ? '已过期' : $this->expires_at->format('Y-m-d H:i:s');
    }

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
     * 获取此API密钥的使用日志
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
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

        if ($this->is_expired) {
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