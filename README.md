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
Mitoop\ApiSignature\Facades\Client::connect($client)->get('/api/demo', ['参数数组']);
Mitoop\ApiSignature\Facades\Client::connect($client)->post('/api/demo', ['参数数组']);

如果设置了 `default`值 并要调取对应的客户端
Mitoop\ApiSignature\Facades\Client::connect()->get('/api/demo', ['参数数组']);
Mitoop\ApiSignature\Facades\Client::connect()->post('/api/demo', ['参数数组']);

假如设置了`alias` 为ApiClient
ApiClient::connect()->get('/api/demo', ['参数数组']);
ApiClient::connect()->post('/api/demo', ['参数数组']);

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
远程调用使用了`guzzle/guzzle`包, 设置了`http_errors` 为 `false` 禁止抛出异常, 原始Response为`GuzzleHttp\Psr7\Response`
在上面封装了一层Response `\Mitoop\ApiSignature\SignatureResponse` 

`\Mitoop\ApiSignature\SignatureResponse` 参考自[zttp](https://github.com/kitetail/zttp)

`SignatureResponse`常用方法
```
$signatureResponse->isOk(); // 请求是否成功 
$signatureResponse->isSuccess(); // 请求是否成功 
$signatureResponse->body(); // 获取原始输出信息
$signatureResponse->json(); // 获取json格式的数据 取决于服务端返回数据格式
```

## 
感谢大神 [zhuchichao](https://github.com/zhuzhichao) 提供技术指导并写了测试代码


