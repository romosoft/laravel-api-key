# Laravel API Key

Laravel API Key 是一个简单易用的 Laravel 包，用于管理和验证 API 密钥。通过这个包，您可以轻松地为您的 API 添加基于密钥的认证，并提供开箱即用的Filament管理界面。

## 版本兼容性

| 包版本 | Laravel版本 | PHP版本 |
|-------|------------|---------|
| 1.0.0 | 12.x       | 8.2+    |

## 特性

- 🔑 轻松生成和管理 API 密钥
- 🛡️ 安全存储和验证机制
  - 密钥在前端始终部分隐藏，仅显示前8位
  - 仅在创建和重新生成时显示完整密钥一次
- ⏰ 支持密钥过期和状态管理
  - 可设置无限期密钥
  - 密钥过期状态清晰显示
- 🔒 基于中间件的 API 请求验证
- 🧩 完整的Filament界面集成
  - 开箱即用的管理界面
  - 支持创建、编辑、删除和重新生成密钥
- 🚀 支持缓存加速密钥验证
- 🛠️ 全面可配置
  - 表名
  - 密钥长度
  - 请求头名称
  - 缓存策略等

## 屏幕截图

管理界面（已内置实现）：
![API密钥管理界面](https://placeholder-for-screenshot.com/api-keys-list.png)

## 安装

使用 Composer 安装：

```bash
composer require leftsky/laravel-api-key:^1.0
```

安装后发布配置文件：

```bash
php artisan vendor:publish --tag=api-key-config
```

运行迁移创建API密钥表：

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
// routes/api.php
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

### Filament管理界面

本包自动集成到Filament管理面板中，提供完整的API密钥管理界面。

如果你还没有安装Filament，可以参考[Filament文档](https://filamentphp.com/docs/installation)进行安装。

## 配置

您可以在 `config/api_key.php` 中配置：

```php
return [
    // API密钥表名
    'table_name' => 'api_keys',
    
    // 密钥长度
    'key_length' => 64,
    
    // 请求头名称
    'header_name' => 'X-API-KEY',
    
    // 默认过期时间（天）
    'expires_in_days' => 365,
    
    // 是否启用Filament集成
    'enable_filament_integration' => true,
    
    // API路由配置
    'routes' => [
        'enabled' => true,
        'prefix' => 'api',
        'middleware' => ['api'],
    ],
    
    // 密钥验证策略（database或cache）
    'auth_strategy' => 'database',
];
```

## 缓存策略

为了提高性能，您可以使用缓存策略：

```php
'auth_strategy' => 'cache',
'cache' => [
    'prefix' => 'api_key_',
    'ttl' => 60,  // 缓存时间（分钟）
],
```

这将减少数据库查询，提高 API 响应速度。

## 自定义

### 修改API密钥的验证逻辑

可以通过扩展`VerifyApiKey`中间件来自定义验证逻辑：

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Leftsky\LaravelApiKey\Http\Middleware\VerifyApiKey as BaseVerifyApiKey;

class CustomApiKeyMiddleware extends BaseVerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // 自定义验证逻辑
        
        return parent::handle($request, $next);
    }
}
```

然后在`app/Http/Kernel.php`中替换原中间件：

```php
protected $middlewareAliases = [
    // ... 其他中间件
    'api.key' => \App\Http\Middleware\CustomApiKeyMiddleware::class,
];
```

## 贡献

欢迎贡献代码、报告问题或提出改进建议！

## 许可证

本包基于 [MIT 许可证](LICENSE.md) 授权。 