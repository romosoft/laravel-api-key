<?php

namespace Leftsky\LaravelApiKey\Filament;

use Illuminate\Support\ServiceProvider;
use Leftsky\LaravelApiKey\Filament\Resources\ApiLogResource;

class ApiKeyPanelProvider extends ServiceProvider
{
    public function register(): void
    {
        // 不在这里注册任何东西，因为直接在AdminPanelProvider中注册了资源
    }

    public function boot(): void
    {
        // 将API日志资源添加到第三方配置中，用于Filament注册
        $this->app->config->set('filament.api-key.resources', array_merge(
            $this->app->config->get('filament.api-key.resources', []),
            [ApiLogResource::class]
        ));
    }
} 