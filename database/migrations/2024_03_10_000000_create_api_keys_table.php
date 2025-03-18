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
        $tableName = 'api_keys';
        
        Schema::create($tableName, function (Blueprint $table) {
            $table->id()->comment('主键');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('name')->comment('名称');
            $table->string('key', 64)->unique()->comment('密钥');
            $table->text('description')->nullable()->comment('描述');
            $table->json('permissions')->nullable()->comment('权限');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->timestamp('last_used_at')->nullable()->comment('最后使用时间');
            $table->timestamp('expires_at')->nullable()->comment('过期时间');
            $table->timestamps();
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        $tableName = 'api_keys';
        
        Schema::dropIfExists($tableName);
    }
}; 