<?php

namespace Leftsky\LaravelApiKey;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Leftsky\LaravelApiKey\Console\Commands\GenerateApiKey;
use Leftsky\LaravelApiKey\Filament\ApiKeyPanelProvider;
use Leftsky\LaravelApiKey\Http\Middleware\VerifyApiKey;
use Leftsky\LaravelApiKey\Services\ApiKeyService;

class ApiKeyServiceProvider extends ServiceProvider
{
    /**
     * 注册服务提供者
     */
    public function register(): void
    {
        // 合并配置文件
        $this->mergeConfigFrom(
            __DIR__.'/../config/api_key.php', 'api_key'
        );

        // 注册API密钥服务
        $this->app->singleton('api-key', function ($app) {
            return new ApiKeyService();
        });
        
        // 注册面板提供者
        if (config('api_key.enable_filament_integration', true)) {
            $this->app->register(ApiKeyPanelProvider::class);
        }
    }

    /**
     * 引导应用程序服务
     */
    public function boot(): void
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/../config/api_key.php' => config_path('api_key.php'),
        ], 'api-key-config');

        // 发布迁移文件
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'api-key-migrations');

        // 加载迁移文件
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // 注册中间件
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api.key', VerifyApiKey::class);

        // 有条件地加载路由
        if (config('api_key.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
        
        // 注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateApiKey::class,
            ]);
        }
    }
} 