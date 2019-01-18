# Laravel API Signature

Laravel 多系统之间的 API 验证处理，每个系统既是服务端也是客户端。

消息认证码(MAC)使用`sha256`哈希函数, sha256目前足够安全(相对于`sha1` `md5`) 又快速(相对于`sha3`)

使用`nonce`+`timestamp`来防止重放攻击 `nonce`依赖于`key-value`类型的内存性缓存,  需要`Laravel`设置默认缓存驱动为`redis` 或者 `memcache`


## 安装 Install

`composer require mitoop/laravel-api-signature`

## 要求 Require

- Laravel 5.5+ 
- PHP 7.0.0+

## 配置 Config

1 . 安装过后运行 `php artisan vendor:publish --provider="Mitoop\ApiSignature\ClientServiceProvider"`，会生成 `config/api-signature.php` 配置文件。

2 . 如果需要使用 `Client` Facade 和 `Signature` Facade，把他们添加到你的 `config/app.php`。

```php
'aliases' => [
    // ...
    'Client' => Mitoop\ApiSignature\Facades\Client::class,
    'Signature' => Mitoop\ApiSignature\Facades\Signature::class,
]
```

3 . `api-signature.php` 配置说明

```php
'default' => 'client1', // 默认的客户端 非必填

'clients' => [ // 客户端数组
   'client1' => [ // 客户端1
       'app_id'        => '10001', // app id 必填
       'app_secret'    => '111111', // 密匙 必填
       'scheme'        => '', // scheme 非必填 如 : http 或者 https
       'host'          => 'laravel.test', // 基础host 如 : www.baidu.com 必填
       'ip'            => '127.0.0.1', // host对应ip 非必填 用来减少域名解析时的dns查询
       'port'          => '', // 端口 非必填
       'https_cert_pem => '', // 当shceme为https 要配置该证书 可选项 1. true 启用验证并使用系统的证书 2. false 不进行验证 3. 配置自己的证书路径
   ],
   'client2' => [ // 客户端2 参见客户端1
       'app_id'        => '10002',
       'app_secret'    => '111111',
       'scheme'        => '',
       'host'          => 'oav.test',
       'ip'            => '',
       'port'          => '',
       'https_cert_pem => ''
   ],
   // ... 更多的客户端。作为服务端时，添加自己的配置为一个客户端就可以了，也可以用这种方法模拟系统间调用调试。
],

'identity'       => '', // [必填]当前系统的身份标识。当向其他系统发起请求时，会使用这个身份标识作为前缀，生成的唯一的标识码，各个系统建议使用不同的值。
'logger_handler' => function ($message, array $data) { // [必填]日志处理回调方法。日志回调方法会记录 HTTP 请求的数据日志，参数为 string $message, array $data。
   \Log::info($message, $data);
},
 ``` 


## 请求 Request

```php
Mitoop\ApiSignature\Facades\Client::connect($client)->get('/api/demo', ['参数数组']);
Mitoop\ApiSignature\Facades\Client::connect($client)->post('/api/demo', ['参数数组']);
```

如果设置了 `default`值 并要调取对应的客户端

```php
Mitoop\ApiSignature\Facades\Client::connect()->get('/api/demo', ['参数数组']);
Mitoop\ApiSignature\Facades\Client::connect()->post('/api/demo', ['参数数组']);
```

假如设置了`alias` 为ApiClient

```php
ApiClient::connect()->get('/api/demo', ['参数数组']);
ApiClient::connect()->post('/api/demo', ['参数数组']);
```

对外只有两种方法： `get`, `post`。

如果作为服务端，可能要用到中间件进行校验，例如：

```php
// Mitoop\ApiSignature\SignatureMiddleware::class

public function handle($request, \Closure $next)
{
   // 如果验证失败会抛出`InvalidSignatureException`异常 
   // 请在laravel的异常处理Handler里处理该异常 或者在此处catch后处理
   Signature::validSign($request);

   return $next($request);
}
```

## 响应 Response

HTTP 请求依赖了 `guzzle/guzzle` 包, 并设置了 `http_errors` 为 `false` 禁止抛出异常(原始 Response 为 `GuzzleHttp\Psr7\Response`)，

最终返回的对象为 `\Mitoop\ApiSignature\SignatureResponse` (参考自[zttp](https://github.com/kitetail/zttp))，`SignatureResponse` 提供了一些方便的方法。

`SignatureResponse` 常用方法:

```php
$signatureResponse->isOk(); // 请求是否成功 
$signatureResponse->isSuccess(); // 请求是否成功 
$signatureResponse->body(); // 获取原始输出信息
$signatureResponse->json(); // 获取json格式的数据 取决于服务端返回数据格式
```

更多使用方法参考 [这里](https://github.com/mitoop/laravel-api-signature/blob/master/tests/SignatureResponseTest.php)

## Contributor

[zhuchichao](https://github.com/zhuzhichao)
