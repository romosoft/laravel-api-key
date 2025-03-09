# Laravel API Key

Laravel API Key 是一个简单易用的 Laravel 包，用于管理和验证 API 密钥。通过这个包，您可以轻松地为您的 API 添加基于密钥的认证。

## 特性

- 轻松生成和管理 API 密钥
- 支持密钥过期和状态管理
- 基于中间件的 API 请求验证
- 可选的 Filament 界面集成
- 支持权限控制
- 支持缓存以提高性能

## 安装

使用 Composer 安装：

```bash
composer require leftsky/laravel-api-key
```

发布配置文件：

```bash
php artisan vendor:publish --tag=api-key-config
```

运行迁移：

```bash
php artisan migrate
```

## 基本用法

### 生成 API 密钥

使用命令行：

```bash
php artisan api-key:generate {user_id} --name="示例密钥" --description="这是一个示例API密钥" --expires=365
```

或者使用 Facade：

```php
use Leftsky\LaravelApiKey\Facades\ApiKey;

$apiKey = ApiKey::generate(1, [
    'name' => '我的API密钥',
    'description' => '这是一个测试密钥',
    'expires_in_days' => 90
]);

// 获取生成的密钥
$keyString = $apiKey->key;
```

### 保护 API 路由

在路由中使用中间件保护您的 API：

```php
Route::middleware('api.key')->group(function () {
    Route::get('/user', function () {
        // 只有拥有有效API密钥的请求才能访问这里
        return auth()->user();
    });
});
```

### 在请求中使用 API 密钥

在 API 请求中，在头信息中包含您的 API 密钥：

```
X-API-KEY: your-api-key-here
```

## 配置

您可以在 `config/api_key.php` 中配置：

- 表名
- API 密钥长度
- 头信息名称
- 默认过期时间
- 缓存设置
- 等等

## Filament 集成

如果您的项目中使用了 Filament，本包提供了开箱即用的 API 密钥管理界面。在配置文件中启用 Filament 集成：

```php
'enable_filament_integration' => true,
```

## 缓存策略

为了提高性能，您可以使用缓存策略：

```php
'auth_strategy' => 'cache',
```

这将减少数据库查询，提高 API 响应速度。

## 许可证

本包基于 [MIT 许可证](LICENSE.md) 授权。 