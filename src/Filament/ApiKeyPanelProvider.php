<?php

namespace Leftsky\LaravelApiKey\Filament;

use Illuminate\Support\ServiceProvider;

class ApiKeyPanelProvider extends ServiceProvider
{
    public function register(): void
    {
        // 不在这里注册任何东西，因为直接在AdminPanelProvider中注册了资源
    }

    public function boot(): void
    {
        // 不需要任何引导逻辑，因为直接在AdminPanelProvider中注册了资源
    }
} 