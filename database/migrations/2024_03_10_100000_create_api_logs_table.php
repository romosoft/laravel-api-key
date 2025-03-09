<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint');  // 请求的API端点
            $table->string('method');    // HTTP方法 (GET, POST, etc.)
            $table->string('ip_address')->nullable();  // 客户端IP
            $table->json('request_data')->nullable();  // 请求数据
            $table->integer('response_code');          // 响应状态码
            $table->json('response_data')->nullable(); // 响应数据
            $table->integer('duration')->nullable();   // 请求处理时间(ms)
            $table->timestamps();
            $table->index('created_at');  // 便于按时间查询
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
}; 