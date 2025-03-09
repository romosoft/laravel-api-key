<?php

namespace Leftsky\LaravelApiKey\Filament\Resources;

use Leftsky\LaravelApiKey\Filament\Resources\ApiKeyResource\Pages;
use Leftsky\LaravelApiKey\Models\ApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'API密钥';

    protected static ?string $modelLabel = 'API密钥';

    protected static ?string $pluralModelLabel = 'API密钥';

    protected static ?int $navigationSort = 90;
    
    // 指定面板ID
    protected static ?string $panel = 'admin';
    
    // 确保在导航中显示
    protected static bool $shouldRegisterNavigation = true;
    
    // 直接设置路由
    protected static ?string $slug = 'api-keys';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('API密钥信息')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('名称')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('描述')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('过期时间')
                            ->helperText('留空表示永不过期')
                            ->nullable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('是否激活')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('masked_key')
                    ->label('密钥')
                    ->tooltip('出于安全考虑，密钥仅显示前8位')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('key', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(30),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('最后使用时间')
                    ->dateTime()
                    ->placeholder('从未使用')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_status')
                    ->label('过期时间')
                    ->badge()
                    ->color(fn (string $state): string => 
                        $state === '无限期' ? 'primary' :
                        (str_contains($state, '已过期') ? 'danger' : 'success')
                    )
                    ->sortable(query: fn (Builder $query, string $direction): Builder => 
                        $query->orderBy('expires_at', $direction)
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('regenerate')
                    ->label('重新生成')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('重新生成API密钥')
                    ->modalDescription('确定要重新生成此API密钥吗？旧密钥将立即失效，使用旧密钥的应用程序需要更新。')
                    ->modalSubmitActionLabel('重新生成')
                    ->action(function (ApiKey $record) {
                        $newKey = ApiKey::generateKey();
                        $record->update([
                            'key' => $newKey
                        ]);
                        
                        // 显示通知，包含新密钥
                        \Filament\Notifications\Notification::make()
                            ->title('API密钥已重新生成')
                            ->body("新API密钥: {$newKey}\n请保存此密钥，它不会再次显示。")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
    
    // 确保导航项可见
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
} 