# 基础使用：

## 1. 声明基础信息

```php
$auth = [
    'refresh_token' => '', // Aztr|...
    'client_id' => '', // App ID from Seller Central, amzn1.sellerapps.app.cfbfac4a-......
    'client_secret' => '', // The corresponding Client Secret
    'region' => \AmazonSellingPartnerAPI\Client::REGION_NORTH_AMERICA, 
    'access_key' => '', // Access Key of AWS IAM User, for example AKIAABCDJKEHFJDS
    'secret_key' => '', // Secret Key of AWS IAM User
    'role_arn' => '', // AWS IAM Role ARN for example: arn:aws:iam::123456789:role/Your-Role-Name
];
```



## 2. 使用 refresh_token 换取 access_token

```php
//使用refresh_token 换取 access_token
$oAuth = new \AmazonSellingPartnerAPI\OAuth($auth['client_id'], $auth['client_secret']);
$auth['access_token'] = $oAuth->getAccessToken($auth['refresh_token'])->access_token;
```



## 3. 实例化一个签名版本

```php
$sign = new \AmazonSellingPartnerAPI\Signature\V4Signature();
```



## 4. 获取用户权限信息

```php
$assumedRole = new \AmazonSellingPartnerAPI\AssumeRole($auth['region'], $auth['access_key'], $auth['secret_key'], $sign);
$credentials = $assumedRole->assume($auth['role_arn']);
$auth['access_key'] = $credentials['AccessKeyId'];
$auth['secret_key'] = $credentials['SecretAccessKey'];
$auth['session_token'] = $credentials['SessionToken'];
```



## 5. 发起请求

```php
$client = new \AmazonSellingPartnerAPI\Client($sign);
$client->setAuth($auth);
var_dump($client->get('/orders/v0/orders/XXX-XXXXXX-XXXXXXX'));
```



# 使用模块来请求



## 1. 声明信息

同 **基础使用** 的步骤 1



## 2. 实例化一个签名版本并使用响应的模块

```php
$sign = new \AmazonSellingPartnerAPI\Signature\V4Signature();
$cache = new Cache(); //缓存驱动必须实现 Psr\SimpleCache\CacheInterface 接口
$order = new \AmazonSellingPartnerAPI\Module\Orders($auth, $cache, $sign);
var_dump($order->getOrder([
        'path_params' => [
            "orderId" => "XXX-XXXXXX-XXXXXXX",
        ],
    ]));
```



# hyper 框架内使用使用示例

## 1. 声明基础信息

同  **基础使用** 步骤 1



## 2. 添加依赖

```php
// config/autoload 下添加依赖：
return [
  .
  .
  .
  \AmazonSellingPartnerAPI\Contract\SignInterface::class => \AmazonSellingPartnerAPI\Signature\V4Signature::class,
];
```



### 3. 使用相应模块

```php
$orders = make(\AmazonSellingPartnerAPI\Module\Orders::class, [
    'auth' => $auth
])
  
$order->getOrder([
        'path_params' => [
            "orderId" => "XXX-XXXXXX-XXXXXXX",
        ],
    ])
```



