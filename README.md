# Laravel API Signature

Laravel 多系统之间的 API 验证处理，每个系统既可以作为客户端也可以作为服务端, 只需要在配置文件中添加对应配置就可以灵活扮演两种身份。

消息认证码(MAC)使用`sha256`哈希函数, sha256目前足够安全(相对于`sha1` `md5`) 快速(相对于`sha3`)。

使用`nonce`+`timestamp`来防止重放攻击, `nonce`依赖于`key-value`类型的内存型缓存,  需要`Laravel`设置默认缓存驱动为`redis` 或者 `memcache`。


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
    'ApiClient' => Mitoop\ApiSignature\Facades\Client::class,
    'ApiSignature' => Mitoop\ApiSignature\Facades\Signature::class,
]
```

3 . `api-signature.php` 配置说明

```php
'default' => 'client-one', // 默认的客户端

'clients' => [ // 客户端数组
   'client-one' => [ // 一个客户端
       'app_id'         => '10001', // app id [必填]
       'app_secret'     => 'phpisthebestlanguageintheworld', // 密匙 [必填]
       'scheme'         => '', // scheme [非必填] http 或者 https 默认http
       'host'           => 'client-one.test', // 基础host [必填] 如 : www.baidu.com 
       'ip'             => '127.0.0.1', // host对应ip [非必填] 指定ip可以减少一次DNS查询还会降低域名劫持的风险
       'port'           => '', // 端口 [非必填] http 默认 80 https 默认 443
       'https_cert_pem' => '', // SSL证书文件路径 [非必填] 当shceme为https 即要发起https请求时 要配置该证书 可选项 1. true 启用验证并使用系统的证书 2. false 不进行验证 3. 配置自己的证书路径 默认 false
   ],
   'another-client' => [ // 另外一个客户端
       'app_id'         => '10002',
       'app_secret'     => 'phpisthebestlanguageintheworld',
       'scheme'         => '',
       'host'           => 'another-client.test',
       'ip'             => '',
       'port'           => '',
       'https_cert_pem' => ''
   ],
   // ... 更多的客户端。
   // 作为服务端时 配置一个自己的client 也可以利用这种机制,在一个系统中模拟系统间的调用
   'self-client' => [ // 系统自己的配置
          'app_id'         => '10000',
          'app_secret'     => 'phpisthebestlanguageintheworld',
          'scheme'         => '',
          'host'           => 'self-client.test',
          'ip'             => '',
          'port'           => '',
          'https_cert_pem' => ''
      ],
],
// 当前系统的身份标识 [必填] 当向其他系统发起请求时，会使用这个身份标识作为前缀，生成的唯一的标识码，各个系统请使用不同的标识
'identity'       => '',
// 日志处理回调类 [必填] 日志会记录请求的数据，参数为 string $message, array $data
// 自定义处理类的时候必需实现 \Mitoop\ApiSignature\SignatureLoggerInterface 接口
'logger_handler' => \Mitoop\ApiSignature\DefaultSignatureLogger::class, 
 ``` 


## 请求 Request

假如设置了`alias` 为ApiClient

```php
ApiClient::connect('another-client')->get('/api/demo', ['foo' => 'bar']);
ApiClient::connect('another-client')->post('/api/demo', ['foo' => 'bar']);
```

如果设置了 `default`值 并向默认的客户端发起请求 这个时候可以不指定具体的connect的客户端

```php
ApiClient::connect()->get('/api/demo', ['foo' => 'bar']);
ApiClient::connect()->post('/api/demo', ['foo' => 'bar']);

or

ApiClient::get('/api/demo', ['foo' => 'bar']);
ApiClient::post('/api/demo', ['foo' => 'bar']);

```

如有没有设置`alias` 那么可以用`Facade` 或者 容器调用 

```php
// Facade模式
Mitoop\ApiSignature\Facades\Client::connect('client-one')->get('/api/demo', ['foo' => 'bar']);
Mitoop\ApiSignature\Facades\Client::connect('client-one')->post('/api/demo', ['foo' => 'bar']);

// 容器模式
app(\Mitoop\ApiSignature\Client::class)->connect('another-client')->get('/api/demo', ['foo' => 'bar']);
app()->make(\Mitoop\ApiSignature\Client::class)->connect('another-client')->post('/api/demo', ['foo' => 'bar']);
```

如果作为服务端，肯定要验证签名, 可以在中间件中进行验证

```php
// Mitoop\ApiSignature\SignatureMiddleware::class

public function handle($request, \Closure $next)
{
   // 如果验证失败会抛出`InvalidSignatureException`异常 
   // 如有需要 请在laravel的异常处理Handler里或者在此处catch后处理
   Signature::validSign($request);

   return $next($request);
}

// 加入到路由中间件
 protected $routeMiddleware = [
     // ...
     'api-signature' => \Mitoop\ApiSignature\SignatureMiddleware::class
 ];
```

目前可用的请求方法 : `get`, `post`, `put`, `delete`

## 响应 Response

HTTP 请求依赖了 `guzzle/guzzle` 包, 并设置了 `http_errors` 为 `false`, 请求不再抛出异常, 即使发生了错误, 每次请求都会返回 `\Mitoop\ApiSignature\SignatureResponse` 对象(参考自[zttp](https://github.com/kitetail/zttp)) `SignatureResponse` 提供了一些简洁有力的方法。

```php
$signatureResponse->isSuccess(); // 请求是否成功 
$signatureResponse->isOk(); //  isSuccess别名方法
$signatureResponse->body(); // 获取原始输出信息
$signatureResponse->json(); // 获取json格式的数据 

典型用法如 :
 
if($signatureResponse->isOk()) {
   if($json = $signatureResponse->json()) {
      // cook yourself business logic code
   }
}

告别发起请求时 try catch 冗长的处理

```

更多使用方法参考 [这里](https://github.com/mitoop/laravel-api-signature/blob/master/tests/SignatureResponseTest.php)

## Contributor

[zhuchichao](https://github.com/zhuzhichao)
