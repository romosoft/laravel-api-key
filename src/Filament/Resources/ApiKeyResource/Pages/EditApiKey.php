<?php

namespace Leftsky\LaravelApiKey\Filament\Resources\ApiKeyResource\Pages;

use Leftsky\LaravelApiKey\Filament\Resources\ApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApiKey extends EditRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 