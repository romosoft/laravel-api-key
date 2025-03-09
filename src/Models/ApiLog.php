<?php

namespace Leftsky\LaravelApiKey\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'api_key_id',
        'endpoint',
        'method',
        'ip_address',
        'request_data',
        'response_code',
        'response_data',
        'duration',
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'duration' => 'integer',
    ];

    /**
     * 与此日志关联的API密钥
     */
    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * 设置表名
     */
    public function getTable()
    {
        return config('api_key.log_table_name', parent::getTable() ?: 'api_logs');
    }
} 