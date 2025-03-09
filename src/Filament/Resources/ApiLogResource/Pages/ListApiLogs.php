<?php

namespace Leftsky\LaravelApiKey\Filament\Resources\ApiLogResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Leftsky\LaravelApiKey\Filament\Resources\ApiLogResource;
use Leftsky\LaravelApiKey\Services\ApiLogService;

class ListApiLogs extends ListRecords
{
    protected static string $resource = ApiLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clean_logs')
                ->label('清理过期日志')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->action(function (ApiLogService $apiLogService) {
                    $count = $apiLogService->cleanOldLogs();
                    $this->notification()->success("已清理 {$count} 条过期日志记录");
                })
                ->requiresConfirmation(),
        ];
    }
} 