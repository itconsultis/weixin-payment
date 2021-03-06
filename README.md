# weixin-payment

WeChat payment client library for PHP 5.5+

[![Build Status](https://travis-ci.org/itconsultis/weixin-payment.svg?branch=master)](https://travis-ci.org/itconsultis/weixin-payment)

## Features

- Simple, intuitive programming interface
- Composer-friendly; just install the package and go!
- [PSR-7](http://www.php-fig.org/psr/psr-7/) compatible
- [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compatible
- Integrates with [Laravel 5](http://laravel.com)
- Fully tested

## What it does (and doesn't)

The client exposes a simple programming interface to WeChat's payment-related
web service calls. It transparently handles boilerplate stuff like request
signing and XML serialization so you can focus on things that matter.

This package does not perform authentication; it will *not* help you get a user's
OpenID. Fortunately, there are plenty of other packages that already do this.
[overtrue/wechat](https://packagist.org/packages/overtrue/wechat) is a pretty good one.


## Usage

#### Create a Client instance
```php
$client = \ITC\Weixin\Payment\Client::instance([
    'app_id' => 'your appid',
    'secret' => 'your signing secret',
    'mch_id' => 'your merchant id',
    'public_key_path' => '/path/to/public_key',
    'private_key_path' => '/path/to/private_key',
]);
```

#### Start a payment
```php
// execute the "pay/unifiedorder" command; the result is a Message instance
$result = $client->command('pay/unifiedorder')->execute([
    'openid' => 'wx_9f8a98g9a8geag0',
    'trade_type' => 'JSAPI',
    'out_trade_no' => 'your-order-id',
    'total_fee' => 1000,
]);

// authenticate the result
$authentic = $result->authenticate();

// if a prepay_id is in the Message, the payment is ready to execute
if ($authentic && $prepay_id = $result->get('prepay_id'))
{
    // jsapize() returns a JsonSerializable object
    $jsbridge_params = $client->jsapize(['prepay_id'=>$prepay_id]);
}
```

The `$jsbridge_params` object can then be JSON-serialized and supplied directly
to the `WeixinJSBridge` global in the Javascript space. Here's an example:

```javascript
var jsbridge_params = <?php echo json_encode($jsbridge_params) ?>;

WeixinJSBridge.invoke('getBrandWCPayRequest', jsbridge_params, function(result) {
    // do something with the result
});
```

## Messages

This library represents XML payloads transported between the client and the
WeChat web service as *messages*. A [Message](https://github.com/itconsultis/weixin-payment/blob/master/src/ITC/Weixin/Payment/Contracts/Message.php)
is an object that:

- can be serialized to XML
- supports hash-based signing and authentication
- provides key/value access to its attributes

#### How to create a Message instance
```php
// create a Message instance with the "return_code" attribute
$message = $client->message(['return_code'=>'FAIL']);
```

#### How to add message attributes
```php
$message->set('foo', 1);
$message->set('bar', 'two');
```

#### How to convert a message to XML
```
$message->serialize();
```

#### How to sign a message
```php
// this adds a "sign" attribute to the Mesage instance
$message->sign();

$message->get('sign');
>>> "2C2B2A1D626E750FCFD0ED661E80E3AA"
```

#### How to authenticate a signed message

Generally, whenever you execute a `Command` (see below), you will want to
authenticate the result:
```php
$result = $client->command('pay/unifiedorder')->execute([/* ... */]);
$kosher = $result->authenticate();
```

## Commands

The various web service calls to the payment API are *commands*. A
[Command](https://github.com/itconsultis/weixin-payment/blob/master/src/ITC/Weixin/Payment/Contracts/Command.php) is an
object that has an `execute` method which returns a `Message`.



- `pay/unifiedorder` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_1)

    ```php
    $result = $client->command('pay/unifiedorder')->execute([
        'openid' => 'wx_9f8a98g9a8geag0',
        'trade_type' => 'JSAPI',
        'out_trade_no' => 'domain-order-id',
        'total_fee' => 1000,
    ]);
    ```

- `pay/orderquery` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_2&index=4)

    ```php
    // query a payment by wechat transaction id
    $result = $client->command('pay/orderquery')->execute([
        'transaction_id' => '1008450740201411110005820873'
    ]);

    // or by domain order id ("out_trade_no")
    $result = $client->command('pay/orderquery')->execute([
        'out_trade_no' => 'domain-order-id'
    ]);
    ```

- `pay/closeorder` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_3&index=5) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/closeorder')->execute([/* ... */]);
    ```

- `secapi/refund` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_4&index=6) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('secapi/refund')->execute([/* ... */]);
    ```

- `pay/refundquery` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_5&index=7) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/refundquery')->execute([/* ... */]);
    ```

- `pay/downloadbill` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_6&index=8) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/downloadbill')->execute([/* ... */]);
    ```

- `payitil/report` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_8&index=9) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('payitil/report')->execute([/* ... */]);
    ```

- `tools/shorturl` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_9&index=10) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('tools/shorturl')->execute([/* ... */]);
    ```

- `mmpaymkttransfers/sendredpack` [spec](https://pay.weixin.qq.com/wiki/doc/api/cash_coupon.php?chapter=13_5)

    ```php
    $result = $client->command('mmpaymkttransfers/sendredpack')->execute([
        'mch_billno' => '10000098201411111234567890',
        'send_name' => '天虹百货',
        're_openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
        'total_amount' => 1000,
        'total_num' => 1,
        'client_ip' => '192.168.0.1',
        'wishing' => '感谢您参加猜灯谜活动，祝您元宵节快乐！',
        'act_name' => '猜灯谜抢红包活动',
        'remark' => '猜越多得越多，快来抢！',
    ]);
   ```

- `mmpaymkttransfers/gethbinfo` [spec](https://pay.weixin.qq.com/wiki/doc/api/cash_coupon.php?chapter=13_7)

    ```php
    $result = $client->command('mmpaymkttransfers/gethbinfo')->execute([
        'mch_billno' => '10000098201411111234567890',
        'bill_type' => 'MCHT',
    ]);
    ```

## Installation

### Composer

    composer require itc/weixin-payment:1.3.0

### Laravel

The package ships with a [Laravel 5](http://laravel.com) service provider that
registers the Client on the application service container. Install the service
provider by adding the following line to the `providers`
array in `config/app.php`:

    ITC\Weixin\Payment\ServiceProvider::class

Then publish the package configuration via:

    php artisan vendor:publish

Now you can access the client instance via dependency injection or through the
service container:

```php
$client = App::make('ITC\Weixin\Payment\Contracts\Client');
```

## Contributing

Feel free to fork this project and create a pull request!

### How to implement a Command

1. Implement a concrete `ITC\Weixin\Payment\Contracts\Command`. Feel free to
   extend `ITC\Weixin\Payment\Command\Command`.

2. Register the command inside `Client::instance()`:

    ```
    public static function instance()
    {
        ...
        
        $client->register(Command\YourCommand::class);
        ...
    }
    ```

#### How to run tests

    ./phpunit

## License

[MIT](./LICENSE)

