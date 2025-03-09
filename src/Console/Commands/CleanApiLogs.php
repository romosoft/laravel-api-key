<?php

namespace Leftsky\LaravelApiKey\Console\Commands;

use Illuminate\Console\Command;
use Leftsky\LaravelApiKey\Services\ApiLogService;

class CleanApiLogs extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'api-key:clean-logs {--days= : 保留最近几天的日志}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '清理过期的API调用日志';

    /**
     * API日志服务
     * 
     * @var ApiLogService
     */
    protected $apiLogService;

    /**
     * 创建命令实例
     *
     * @param ApiLogService $apiLogService
     * @return void
     */
    public function __construct(ApiLogService $apiLogService)
    {
        parent::__construct();
        $this->apiLogService = $apiLogService;
    }

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        
        if ($days !== null) {
            $days = (int) $days;
            if ($days <= 0) {
                $this->error('保留天数必须大于0');
                return 1;
            }
        }
        
        $this->info('开始清理过期的API调用日志...');
        
        $count = $this->apiLogService->cleanOldLogs($days);
        
        $this->info("已清理 {$count} 条过期日志记录");
        
        return 0;
    }
} 