# What is Amazon Selling Partner API
SP-API is the next-generation API functionality suite for sellers and their agents to sell their products on the Amazon marketplace efficiently. Amazon Marketplace Web Services (Amazon MWS) APIs preceded SP-APIs and have been utilized extensively for over ten years. Amazon states in their documentation that the SP-API is the future and that SP APIs will receive any new updates and enhancements. However, one can expect a transition period from MWS to SP-API while the new system is stabilized and offers parity with the existing MWS APIs.

For more information visit: [https://github.com/amzn/selling-partner-api-docs](https://github.com/amzn/selling-partner-api-docs)
## Installation

```sh
composer require andrewyj/amazon-sp-api
```

## Basic Usage

### 1. Define auth info

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

### 2. Use refresh_token to exchange access_token

```php
$oAuth = new \AmazonSellingPartnerAPI\OAuth($auth['client_id'], $auth['client_secret']);
$auth['access_token'] = $oAuth->getAccessToken($auth['refresh_token'])->access_token;
```

### 3. Select a signature version(v4)
```php
$sign = new \AmazonSellingPartnerAPI\Signature\V4Signature();
```

### 4. Fetch role info
```php
$assumedRole = new \AmazonSellingPartnerAPI\AssumeRole($auth['region'], $auth['access_key'], $auth['secret_key'], $sign);
$credentials = $assumedRole->assume($auth['role_arn']);
$auth['access_key'] = $credentials['AccessKeyId'];
$auth['secret_key'] = $credentials['SecretAccessKey'];
$auth['session_token'] = $credentials['SessionToken'];
```



## 5. Make a request
```php
$client = new \AmazonSellingPartnerAPI\Client($sign);
$client->setAuth($auth);
var_dump($client->get('/orders/v0/orders/XXX-XXXXXX-XXXXXXX'));
```

## Module usage

### 1. Define auth info

[Define auth info](#basic-usage)

### 2. Select a module

```php
$sign = new \AmazonSellingPartnerAPI\Signature\V4Signature();
$cache = new Cache(); //缓存驱动必须实现 Psr\SimpleCache\CacheInterface 接口
$order = new \AmazonSellingPartnerAPI\Module\Order($auth, $cache, $sign);
```
> `Cache()` are used to cache role credentials and access_token.It must have `get()` and `set()` functions. if you don't have one. create an adapter.

### 3. Make a request

```php
# route param
var_dump($order->getOrder('XXX-XXXXXX-XXXXXXX')->send());

# query param
var_dump($order->getOrders()->withQuery([
  "CreatedAfter" => "2020-04-13T06:28:08Z",
  "MarketplaceIds" => [
      "XXXXXX",
  ]
])->send());
```