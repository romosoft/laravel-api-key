<?php

namespace Leftsky\LaravelApiKey\Filament\Resources\ApiKeyResource\Pages;

use Leftsky\LaravelApiKey\Filament\Resources\ApiKeyResource;
use Leftsky\LaravelApiKey\Models\ApiKey;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 设置用户ID
        $data['user_id'] = Auth::id();
        
        // 生成唯一密钥
        $data['key'] = ApiKey::generateKey();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // 显示通知，包含密钥信息
        Notification::make()
            ->title('API密钥已创建')
            ->body("您的新API密钥: {$this->record->key}\n请保存此密钥，它不会再次显示。")
            ->warning()
            ->persistent()
            ->send();
    }
} 