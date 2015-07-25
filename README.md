# weixin-payment

A sane PHP client library for WeChat in-app payments, brought to you by the
nerds at [IT Consultis Shanghai](http://it-consultis.com).

## What it does

This package wraps WeChat's payment API calls with a a pleasant interface.
Boilerplate stuff like request signing, nonce generation, and XML serialization
happens transparently behind the scenes so you can focus on what matters.

## What it does NOT do

This is for conducting payment transactions *inside WeChat*. Additionally, this
package does NOT provide JSAPI functionality in any way. There are plenty of
other packages that already do this. [overtrue/wechat](https://packagist.org/packages/overtrue/wechat)
is a pretty good one.

## How to use it

```php
// create the Client instance
$client = \ITC\Weixin\Payment\Client::instance([
    'app_id' => 'your appid',
    'secret' => 'your signing secret',
    'mch_id' => 'your merchant id',
    'public_key_path' => '/path/to/public_key',
    'private_key_path' => '/path/to/private_key',
]);

// execute the "unified order" command; the result is an associative array
$result = $client->command('create-unified-order')->execute([
    'openid' => 'wx_9f8a98g9a8geag0',
    'trade_type' => 'JSAPI',
    'out_trade_no' => 'domain-order-id',
    'total_fee' => 1000,
]);
```

## Commands

`create-unified-order` [reference](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_1)

This command prepares a payment to be executed in the native Wechat application.

```php
$result = $client->command('create-unified-order')->execute([
    'openid' => 'wx_9f8a98g9a8geag0',
    'trade_type' => 'JSAPI',
    'out_trade_no' => 'domain-order-id',
    'total_fee' => 1000,
]);
```

`create-jsbridge-params`

This command returns an array structure that can be JSON-serialized and supplied
to the Javascript context that executes the payment.

```php
$jsbridge_params = $client->command('create-jsbridge-params')->execute([
    'prepay_id' => 12389412928312,
]);
```

```javascript
WeixinJSBridge.invoke('getBrandWCPayRequest', jsbridge_params, function() {
    console.log(arguments);
});
```

## How to install the package

### Composer

    composer install itc/weixin-payment

### Laravel

The package ships with a [Laravel 5](http://laravel.com) service provider that
registers commands on the client and then registers the Client on the
application service container.

Install the service provider by adding the following line to the `providers`
array in `config/app.php`:

    ITC\Weixin\Payment\ServiceProvider::class

Then publish the package configuration via:

    php artisan vendor:publish

This publishes a configuration file in `config/weixin-payment.php`.

Afterwards you can obtain the client instance using the service container:

```php
$client = App::make('ITC\Weixin\Payment\Contracts\Client');
```

As usual, you can also take advantage of dependency injection.

## How to run tests

    ./phpunit

