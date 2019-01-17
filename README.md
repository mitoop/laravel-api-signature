# Laravel API Signature

Laravel系统间的API验证处理. 每个系统既是服务端也是客户端.

## 安装
`composer require mitoop/laravel-api-signature`

## 要求
- Laravel5.5+ 
- PHP 7.0.0+ 

## 使用
- 安装过后运行 `php artisan vendor:publish` 然后选择 `Mitoop\ApiSignature\ClientServiceProvider` 生成 `api-signature.php` 配置文件

- 如果需要使用`Facade` 将 `\Mitoop\ApiSignature\Facades\Client` 和 `\Mitoop\ApiSignature\Facades\Signature`放入`config`下`app.php`中`aliases`下

- `api-signature.php`配置说明
   ```
   'default' => 'client1', // 默认的客户端 非必填
   
       'clients' => [ // 客户端数组
           'client1' => [ // 客户端1
               'app_id'     => '10001', // app id 必填
               'app_secret' => '111111', // 密匙 必填
               'scheme'     => '', // scheme 非必填 如 : http 或者 https
               'host'       => 'laravel.test', // 基础host 如 : www.baidu.com 必填
               'ip'         => '127.0.0.1', // host对应ip 非必填 用来减少域名解析时的dns查询
               'port'       => '', // 端口 非必填
               'https_cert_pem => '', // 当shceme为https 要配置该证书 可选项 1. true 启用验证并使用系统的证书 2. false 不进行验证 3. 配置自己的证书路径
           ],
           'client2' => [ // 客户端2 参见客户端1
               'app_id'     => '10002',
               'app_secret' => '111111',
               'scheme'     => '',
               'host'       => 'oav.test',
               'ip'         => '',
               'port'       => '',
               'https_cert_pem => ''
           ],
           ... 更多的客户端 作为服务端时 添加自己的配置为一个客户端就可以了 也可以用这种方法模拟系统间调用调试
       ],
   
       'identity'       => '', // 当前系统的身份标识 必填 当向其他系统发起请求时 生成的唯一标识码会用这个身份标识当前缀 各个系统最好不一样
       'logger_handler' => function ($message, array $data) { // 日志处理手柄 必填 日志手柄会记录远程调用的数据日志 参数固定为 string $message, array $data
           \Log::info($message, $data);
       },
     ``` 
     
## Demo
```
Mitoop\ApiSignature\Facades\Client::connection($client)->get('/api/demo', ['参数数组']);
Mitoop\ApiSignature\Facades\Client::connection($client)->post('/api/demo', ['参数数组']);

如果设置了 `default`值 并要调取对应的客户端
Mitoop\ApiSignature\Facades\Client::connection()->get('/api/demo', ['参数数组']);
Mitoop\ApiSignature\Facades\Client::connection()->post('/api/demo', ['参数数组']);

假如设置了`alias` 为ApiClient
ApiClient::connection()->get('/api/demo', ['参数数组']);
ApiClient::connection()->post('/api/demo', ['参数数组']);

对外就这两种方法 `get`, `post`, 其他在配置文件里配置就行了

如果作为服务端 可能要用到中间件校验 可以参考
Mitoop\ApiSignature\SignatureMiddleware::class

	public function handle($request, \Closure $next)
	{
	   // 如果验证失败会抛出`InvalidSignatureException`异常 
	   // 请在laravel的异常处理Handler里处理该异常 或者在此处catch后处理
	   Signature::validSign($request);
	
	   return $next($request);
	}
``` 

## 返回结果
如果出现异常 将返回`false` 日志里有记录 如果成功 返回 服务端定义的结构 请使用`json`格式

