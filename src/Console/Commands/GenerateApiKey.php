<?php

namespace Leftsky\LaravelApiKey\Console\Commands;

use Illuminate\Console\Command;
use Leftsky\LaravelApiKey\Services\ApiKeyService;

class GenerateApiKey extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'api-key:generate
                            {user : 用户ID}
                            {--name= : API密钥名称}
                            {--description= : API密钥描述}
                            {--expires= : 有效期（天数）}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '为指定用户生成一个新的API密钥 (Laravel API Key v1.0)';

    /**
     * 执行命令
     */
    public function handle(ApiKeyService $apiKeyService)
    {
        $this->info('Laravel API Key v1.0.0');
        $this->line('----------------------------');
        
        $userId = $this->argument('user');
        
        $options = [
            'name' => $this->option('name') ?: 'API密钥 ' . now()->format('Y-m-d H:i'),
            'description' => $this->option('description'),
        ];
        
        if ($this->option('expires')) {
            $options['expires_in_days'] = (int) $this->option('expires');
        }
        
        $apiKey = $apiKeyService->generate($userId, $options);
        
        $this->info('API密钥已成功创建！');
        $this->table(
            ['ID', '名称', 'API密钥', '过期时间'],
            [
                [
                    $apiKey->id,
                    $apiKey->name,
                    $apiKey->key,
                    $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : '永不过期',
                ],
            ]
        );
        
        $this->warn('请保存此API密钥，它不会再次显示！');
        
        return 0;
    }
} 