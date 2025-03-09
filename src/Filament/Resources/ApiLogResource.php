<?php

namespace Leftsky\LaravelApiKey\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Leftsky\LaravelApiKey\Filament\Resources\ApiLogResource\Pages;
use Leftsky\LaravelApiKey\Models\ApiLog;

class ApiLogResource extends Resource
{
    protected static ?string $model = ApiLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'API 管理';
    
    protected static ?int $navigationSort = 10;

    public static function getLabel(): string
    {
        return 'API 调用日志';
    }

    public static function getPluralLabel(): string
    {
        return 'API 调用日志';
    }

    public static function getNavigationLabel(): string
    {
        return 'API 调用日志';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('日志详情')
                    ->schema([
                        Forms\Components\Select::make('api_key_id')
                            ->relationship('apiKey', 'name')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('endpoint')
                            ->label('端点')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('method')
                            ->label('HTTP方法')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP地址')
                            ->disabled(),
                        Forms\Components\TextInput::make('response_code')
                            ->label('响应状态码')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('duration')
                            ->label('处理时间(ms)')
                            ->disabled(),
                    ])->columns(2),
                
                Forms\Components\Section::make('请求数据')
                    ->schema([
                        Forms\Components\JsonEditor::make('request_data')
                            ->label('请求数据')
                            ->disabled(),
                    ]),
                
                Forms\Components\Section::make('响应数据')
                    ->schema([
                        Forms\Components\JsonEditor::make('response_data')
                            ->label('响应数据')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('apiKey.name')
                    ->label('API 密钥')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('endpoint')
                    ->label('端点')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('method')
                    ->label('方法')
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP地址')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('response_code')
                    ->label('状态码')
                    ->sortable()
                    ->color(function ($state) {
                        if ($state >= 200 && $state < 300) {
                            return 'success';
                        } elseif ($state >= 400 && $state < 500) {
                            return 'warning';
                        } elseif ($state >= 500) {
                            return 'danger';
                        }
                        return 'gray';
                    }),
                TextColumn::make('duration')
                    ->label('耗时(ms)')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('api_key_id')
                    ->relationship('apiKey', 'name')
                    ->label('API 密钥'),
                SelectFilter::make('method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'PATCH' => 'PATCH',
                        'DELETE' => 'DELETE',
                    ])
                    ->label('HTTP 方法'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('开始日期'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('结束日期'),
                    ])
                    ->query(function (Tables\Filters\Filter $filter, $query) {
                        return $query
                            ->when(
                                $filter->getState()['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $filter->getState()['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiLogs::route('/'),
            'view' => Pages\ViewApiLog::route('/{record}'),
        ];
    }
} 