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
                Tables\Columns\TextColumn::make('key')
                    ->label('密钥')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 8) . '...')
                    ->copyable()
                    ->copyableState(fn (ApiKey $record): string => $record->key)
                    ->searchable()
                    ->tooltip(fn (ApiKey $record): string => $record->key),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(30),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('最后使用时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('过期时间')
                    ->dateTime()
                    ->sortable(),
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
                        $record->update([
                            'key' => ApiKey::generateKey()
                        ]);
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